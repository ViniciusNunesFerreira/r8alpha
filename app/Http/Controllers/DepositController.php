<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Investment;
use App\Services\NowPaymentsService;
use App\Services\StartCashPixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    /**
     * Exibe a página de depósito
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallets()->where('type', 'deposit')->first();
        
        // Busca depósitos do usuário (apenas tipo 'deposit')
        $deposits = Deposit::where('user_id', $user->id)
            ->where('payment_type', 'deposit')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Configurações
        $minDepositUsd = config('payment.deposit_limits.min_usd');
        $maxDepositUsd = config('payment.deposit_limits.max_usd');
        $usdToBrlRate = config('payment.usd_to_brl_rate');
        
        return view('payments.deposit.index', compact(
            'deposits',
            'minDepositUsd',
            'maxDepositUsd',
            'usdToBrlRate',
            'wallet'
        ));
    }

    /**
     * Webhook para PIX (StartCash)
     */
    public function webhookPix(Request $request)
    {
        try {
            $payload = $request->all();
            
            $pixService = new StartCashPixService();
            $result = $pixService->processWebhook($payload);

            if ($result) {
                // Processa confirmação de pagamento se for investimento
                $this->processInvestmentPayment($payload);
            }

            return response()->json(['success' => $result], $result ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook PIX', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Webhook para NOWPayments (IPN)
     */
    public function webhookNowPayments(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('IPN NOWPayments recebido', ['payload' => $payload]);

            $cryptoService = new NowPaymentsService();
            $result = $cryptoService->processIPN($payload);

            if ($result) {
                // Processa confirmação de pagamento se for investimento
                $this->processInvestmentPayment($payload);
            }

            return response()->json(['success' => $result], $result ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Erro ao processar IPN NOWPayments', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Processa confirmação de pagamento para investimentos
     */
    private function processInvestmentPayment(array $payload): void
    {
        try {
            // Busca o depósito relacionado
            $txid = data_get($payload, 'data.items.0.externalRef') 
                ?? data_get($payload, 'order_id');
            
            if (!$txid) {
                return;
            }

            $deposit = Deposit::where('transaction_id', $txid)
                ->where('payment_type', 'investment')
                ->where('status', 'paid')
                ->first();

            if (!$deposit || !$deposit->reference_id) {
                return;
            }

            // Busca o investimento
            $investment = Investment::find($deposit->reference_id);
            
            if (!$investment || $investment->payment_status === 'paid') {
                return;
            }

            DB::beginTransaction();

            $user = $investment->user;
            $investmentAmount = $investment->amount;

            // 1. Decrementa o saldo da carteira deposit (que foi creditado pelo markAsConfirmed)
            $depositWallet = $user->wallets()->where('type', 'deposit')->first();
            if ($depositWallet) {
                $depositWallet->decrement('balance', $investmentAmount);
            }

            // 2. Incrementa o total_deposited da carteira investment
            $investmentWallet = $user->wallets()->firstOrCreate(
                ['type' => 'investment'],
                [
                    'balance' => 0,
                    'total_deposited' => 0,
                    'total_withdrawn' => 0,
                    'total_profit' => 0
                ]
            );
            
            $investmentWallet->increment('total_deposited', $investmentAmount);

            // 3. Atualiza investimento
            $investment->update([
                'payment_status' => 'paid',
                'status' => 'active',
                'current_balance' => $investment->amount,
                'started_at' => now(),
                'expires_at' => now()->addDays($investment->investmentPlan->duration_days),
            ]);

            // 4. Cria bot instance
            $this->createBotInstance($investment);

            // 5. Atualiza first_investment_at do usuário
            if (!$user->first_investment_at) {
                $user->update(['first_investment_at' => now()]);
            }

            DB::commit();

            // Dispara notificação
            // event(new InvestmentActivated($investment));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao processar pagamento de investimento', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    /**
     * Cria instância do bot de trading
     */
    protected function createBotInstance(Investment $investment)
    {
        $botInstance = $investment->botInstance()->create([
            'user_id' => $investment->user_id,
            'is_active' => true,
            'config' => [
                'base_currencies' => ['BTC', 'ETH', 'USDT', 'BNB'],
                'min_profit_percentage' => 0.5,
                'max_trade_amount' => $investment->amount,
                'trading_fee' => 0.001,
            ],
            'total_trades' => 0,
            'successful_trades' => 0,
            'total_profit' => 0,
        ]);

        // Dispara job para começar a escanear
        \App\Jobs\ScanArbitrageOpportunities::dispatch($botInstance);

        return $botInstance;
    }
}