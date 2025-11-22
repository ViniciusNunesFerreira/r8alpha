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
                'numeric',
                'min:' . $plan->min_amount,
                'max:' . $plan->max_amount,
            ],
            'payment_method' => 'required|in:wallet,pix,crypto',
        ]);

        DB::beginTransaction();
        
        try {
            $amount = floatval($request->amount);
            $paymentMethod = $request->payment_method;

            // Verifica disponibilidade do plano
            if (!$plan->is_active) {
                throw new \Exception('This plan is not available.');
            }

            // Cria o investimento com status pending
            $investment = Investment::create([
                'user_id' => auth()->user()->id,
                'investment_plan_id' => $plan->id,
                'amount' => $amount,
                'current_balance' => 0,
                'total_profit' => 0,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'started_at' => null,
                'expires_at' => null,
            ]);

            // Se pagamento via carteira, processa imediatamente
            if ($paymentMethod === 'wallet') {
                $wallet = auth()->user()->wallets()
                    ->where('type', 'deposit')
                    ->first();

                if (!$wallet || $wallet->totalAvailableBalance < $amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }

                $this->processWalletPayment($investment, $wallet);
                
                DB::commit();
                
                return redirect()
                    ->route('dashboard')
                    ->with('success', 'Investment created successfully! Your bot is now active.');
            }

            DB::commit();

            // Se pagamento externo (PIX ou Crypto), redireciona para seleção de método
            return redirect()
                ->route('investments.payment', $investment)
                ->with('success', 'Investment created! Choose your payment method.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Página de escolha de método de pagamento
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
                ->route('dashboard')
                ->with('info', 'This investment has already been paid.');
        }

        // Se foi cancelado, redireciona
        if ($investment->status === 'cancelled') {
            return redirect()
                ->route('investments.plans.index')
                ->with('error', 'This investment was cancelled.');
        }

        return view('investments.payment', compact('investment'));
    }

    /**
     * Processa pagamento via carteira
     */
    protected function processWalletPayment(Investment $investment, Wallet $wallet)
    {
        $user = $investment->user;
        $investmentAmount = $investment->amount;

        $is_sponsor = 0;
        if($wallet->sponsored_balance > 0){
             $is_sponsor = 1;
        }

        // 1. Debita da carteira deposit
        $wallet->debitBalance($investmentAmount);

        // 2. Registra transação na carteira deposit
        $wallet->transactions()->create([
            'user_id' => $investment->user_id,
            'type' => 'investment',
            'amount' => $investmentAmount,
            'balance_before' => $wallet->totalAvailableBalance + $investmentAmount,
            'balance_after' => $wallet->totalAvailableBalance,
            'description' => "Investment in {$investment->investmentPlan->name}",
            'status' => 'completed',
        ]);

        // 3. Incrementa total_deposited na carteira investment
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

        // 4. Atualiza investimento
        $investment->update([
            'payment_status' => 'paid',
            'status' => 'active',
            'is_sponsored' => $is_sponsor,
            'current_balance' => $investment->amount,
            'started_at' => now(),
            'expires_at' => now()->addDays($investment->investmentPlan->duration_days),
        ]);

        // 5. Cria bot instance
        $this->createBotInstance($investment);

        // 6. Atualiza first_investment_at do usuário se for primeiro
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
}