<?php

namespace App\Http\Controllers;

use App\Models\InvestmentPlan;
use App\Models\Investment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InvestmentService;

class InvestmentPlansController extends Controller
{
    protected $investmentService;

    public function __construct(InvestmentService $investmentService)
    {
        
        $this->investmentService = $investmentService;
    }

    /**
     * Exibe todos os planos disponíveis
     */
    public function index()
    {

        $plans = InvestmentPlan::where('is_active', true)
            ->orderBy('min_amount')
            ->get();

        // Carteira do usuário
        $wallet = auth()->user()->wallets()
            ->where('type', 'deposit')
            ->first();

        return view('investments.plans.index', compact('plans', 'wallet'));
    }

    /**
     * Exibe detalhes de um plano específico
     */
    public function show(InvestmentPlan $plan)
    {
        if (!$plan->is_active) {
            return redirect()
                ->route('investments.plans.index')
                ->with('error', 'This plan is not available.');
        }

        $wallet = auth()->user()->wallets()
            ->where('type', 'deposit')
            ->first();

        // Investimentos anteriores do usuário neste plano
        $previousInvestments = auth()->user()->investments()
            ->where('investment_plan_id', $plan->id)
            ->latest()
            ->take(5)
            ->get();

        return view('investments.plans.show', compact('plan', 'wallet', 'previousInvestments'));
    }

    /**
     * Processa a contratação do plano
     */
    public function subscribe(Request $request, InvestmentPlan $plan)
    {
        $request->validate([
            'amount' => [
                'required',
                'min:' . $plan->min_amount,
                'max:' . $plan->max_amount,
            ],
            'payment_method' => 'required|in:wallet,pix,crypto',
        ]);

        DB::beginTransaction();
        
        try {
            $amount = $request->amount;
            $paymentMethod = $request->payment_method;

            // Verifica disponibilidade do plano
            if (!$plan->is_active) {
                throw new \Exception('This plan is not available.');
            }

            // Se pagamento via carteira, verifica saldo
            if ($paymentMethod === 'wallet') {
                $wallet = auth()->user()->wallets()
                    ->where('type', 'deposit')
                    ->first();

                    \Log::info('entrei na wallet');

                if (!$wallet || $wallet->balance < $amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }
            }

            // Cria o investimento com status pending
            $investment = Investment::create([
                'user_id' => auth()->user()->id,
                'investment_plan_id' => $plan->id,
                'amount' => $amount,
                'current_balance' => 0, // Será atualizado após confirmação
                'total_profit' => 0,
                'status' => 'pending', // pending, active, completed, cancelled
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending', // pending, paid, failed
                'started_at' => null, // Será definido após confirmação
                'expires_at' => null,
            ]);

            // Se pagamento via carteira, processa imediatamente
            if ($paymentMethod === 'wallet') {
                $this->processWalletPayment($investment, $wallet);
                
                DB::commit();
                
                return redirect()
                    ->route('dashboard')
                    ->with('success', 'Investment created successfully! Your bot is now active.');
            }

            // Se pagamento externo, gera informações de pagamento
            $paymentData = $this->generatePaymentData($investment, $paymentMethod);

            DB::commit();

            return redirect()
                ->route('investments.payment', $investment)
                ->with('success', 'Investment created! Complete the payment to activate your bot.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Processa pagamento via carteira
     */
    protected function processWalletPayment(Investment $investment, Wallet $wallet)
    {
        // Debita da carteira
        $wallet->decrement('balance', $investment->amount);

        // Registra transação
        $wallet->transactions()->create([
            'user_id' => $investment->user_id,
            'type' => 'investment',
            'amount' => $investment->amount,
            'balance_before' => $wallet->balance + $investment->amount,
            'balance_after' => $wallet->balance,
            'description' => "Investment in {$investment->investmentPlan->name}",
            'status' => 'completed',
        ]);

        // Atualiza investimento
        $investment->update([
            'payment_status' => 'paid',
            'status' => 'active',
            'current_balance' => $investment->amount,
            'started_at' => now(),
            'expires_at' => now()->addDays($investment->investmentPlan->duration_days),
        ]);

        // Cria bot instance
        $this->createBotInstance($investment);

        // Atualiza first_investment_at do usuário se for primeiro
        $user = $investment->user;
        if (!$user->first_investment_at) {
            $user->update(['first_investment_at' => now()]);
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

    /**
     * Gera dados de pagamento para métodos externos
     */
    protected function generatePaymentData(Investment $investment, string $method)
    {
        // Aqui você integraria com gateway de pagamento real
        // Por enquanto, retorna estrutura base
        
        $paymentData = [
            'investment_id' => $investment->id,
            'amount' => $investment->amount,
            'method' => $method,
            'expires_at' => now()->addMinutes(30),
        ];

        if ($method === 'pix') {
            // Integração PIX - exemplo
            $paymentData['pix_code'] = $this->generatePixCode($investment);
            $paymentData['qr_code'] = $this->generatePixQRCode($investment);
        }

        if ($method === 'crypto') {
            // Integração Crypto - exemplo
            $paymentData['wallet_address'] = config('payments.crypto.wallet_address');
            $paymentData['network'] = 'TRC20'; // ou ERC20, BEP20, etc
        }

        // Salva dados de pagamento
        $investment->update([
            'payment_data' => $paymentData,
        ]);

        return $paymentData;
    }

    /**
     * Página de pagamento
     */
    public function payment(Investment $investment)
    {
        // Verifica se é do usuário
        if ($investment->user_id !== auth()->user()->id) {
            abort(403);
        }

        // Se já foi pago, redireciona
        if ($investment->payment_status === 'paid') {
            return redirect()
                ->route('investments.show', $investment)
                ->with('info', 'This investment has already been paid.');
        }

        return view('investments.payment', compact('investment'));
    }

    /**
     * Webhook para confirmação de pagamento
     * Este método seria chamado pelo gateway de pagamento
     */
    public function confirmPayment(Request $request, Investment $investment)
    {
        // Validação do webhook (verificar assinatura, etc)
        // ...

        DB::beginTransaction();
        
        try {
            if ($investment->payment_status !== 'pending') {
                throw new \Exception('Payment already processed.');
            }

            // Atualiza status do pagamento
            $investment->update([
                'payment_status' => 'paid',
                'status' => 'active',
                'current_balance' => $investment->amount,
                'started_at' => now(),
                'expires_at' => now()->addDays($investment->investmentPlan->duration_days),
            ]);

            // Cria bot instance
            $this->createBotInstance($investment);

            // Atualiza first_investment_at do usuário
            $user = $investment->user;
            if (!$user->first_investment_at) {
                $user->update(['first_investment_at' => now()]);
            }

            DB::commit();

            // Notifica usuário
            // event(new PaymentConfirmed($investment));

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Helpers para PIX (exemplo - adaptar conforme gateway)
     */
    protected function generatePixCode(Investment $investment)
    {
        // Gerar código PIX real via gateway
        return 'PIX_' . strtoupper(substr(md5($investment->id . time()), 0, 32));
    }

    protected function generatePixQRCode(Investment $investment)
    {
        // Gerar QR Code real via gateway
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
    }
}