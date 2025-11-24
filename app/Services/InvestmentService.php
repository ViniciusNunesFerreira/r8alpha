<?php
namespace App\Services;

use App\Models\Investment;
use App\Models\User;
use App\Models\InvestmentPlan;
use App\Models\BotInstance;
use App\Models\Transaction;
use App\Models\Profit;
use Illuminate\Support\Facades\DB;
use App\Events\InvestmentUpdated;
use App\Events\ProfitGenerated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;



 class InvestmentService
 {

     protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

     /**
     * Cria um novo investimento
     * 
     * Processo:
     * 1. Valida valor do investimento
     * 2. Verifica saldo da carteira
     * 3. Cria registro de investimento
     * 4. Cria instância do robô
     * 5. Atualiza carteira
     * 6. Registra transação
     * 
     * @param User $user Usuário investidor
     * @param InvestmentPlan $plan Plano escolhido
     * @param float $amount Valor do investimento
     * @return Investment
     * @throws \Exception
     */
    public function createInvestment(User $user,  InvestmentPlan $plan, float $amount) 
    {
        DB::beginTransaction();
        try {
            // 1. Validação de valor
            if ($amount < $plan->min_amount || $amount > $plan->max_amount) {
                throw new \Exception(
                    "Investment amount must be between " .
                    "$" . number_format($plan->min_amount, 2) . 
                    " and $" . number_format($plan->max_amount, 2)
                );
            }
            // 2. Verificação de saldo
            $wallet = $user->wallet;
            if (!$wallet || $wallet->balance < $amount) {
                throw new \Exception('Insufficient balance');
            }
            // 3. Criação do investimento
            $investment = Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => $plan->id,
                'amount' => $amount,
                'current_balance' => $amount,
                'total_profit' => 0,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
            ]);
            // 4. Criação da instância do robô
            $botInstance = BotInstance::create([
                'user_id' => $user->id,
                'investment_id' => $investment->id,
                'is_active' => false,
                'config' => [
                    'base_currencies' => ['BTC', 'ETH', 'USDT', 'BNB'],
                    'min_profit_percentage' => 0.5,
                    'max_trade_amount' => $amount,
                    'trading_fee' => 0.001,
                ],
            ]);
            // 5. Atualização da carteira
            $wallet->decrement('balance', $amount);
            // 6. Registro de transação
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'investment',
                'amount' => $amount,
                'balance_before' => $wallet->balance + $amount,
                'balance_after' => $wallet->balance,
                'description' => "Investment in {$plan->name} plan",
                'status' => 'completed',
            ]);

            Cache::forget("user_stats_{$investment->user_id}");

            broadcast(new InvestmentUpdated($investment))->toOthers();

            DB::commit();
            return $investment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



    /**
     * Calcula o valor exato do lucro para o dia.
     * Implementa a regra: "Obrigatoriamente 100% até o fim".
     */
    public function calculateDailyProfitAmount(Investment $investment): float
    {
        $plan = $investment->investmentPlan;
        $now = Carbon::now();
        $expiresAt = Carbon::parse($investment->expires_at);
        
        // Meta final: 100% do valor investido (ou seja, o lucro total deve ser igual ao amount)
        $targetTotalProfit = $investment->amount; 
        
        // Lucro já acumulado
        $currentTotalProfit = $investment->total_profit;

        // Se já atingiu o teto, retorna 0
        if ($currentTotalProfit >= $targetTotalProfit) {
            return 0.0;
        }

        // Verifica se é o último dia (ou se já passou da data mas ainda não pagou tudo)
        $isLastDay = $now->copy()->addDay()->greaterThanOrEqualTo($expiresAt);
        
        if ($isLastDay) {
            // No último dia, pagamos a diferença exata para completar 100%
            $remainingToPay = $targetTotalProfit - $currentTotalProfit;
            return max(0, $remainingToPay);
        }

        // Cálculo Randômico Padrão
        $profitPercentage = rand(
            $plan->daily_return_min * 100, 
            $plan->daily_return_max * 100
        ) / 100;

        $calculatedProfit = ($investment->amount * $profitPercentage) / 100;

        // Proteção: Nunca pagar mais que o restante para atingir 100%
        $remainingCap = $targetTotalProfit - $currentTotalProfit;
        
        return min($calculatedProfit, $remainingCap);
    }


     /**
     * Processa os lucros diários (Engine Principal)
     */
    public function processDailyProfits()
    {
        $investments = Investment::where('status', 'active')
            ->where('started_at', '<=', now()->subDay()) // Só começa 24h depois
            ->where('expires_at', '>', now()) // Ainda não expirou
            ->where(function ($query) {
                    $query->where('last_profit_at', '<=', now()->subDay()) 
                        ->orWhereNull('last_profit_at');           
                })
            ->get();

        \Log::info('Buscando os investimentos para rentabilizar, total:'.$investments->count());

        $stats = [
            'total_processed' => 0,
            'total_profit_distributed' => 0,
            'errors' => 0,
        ];

        foreach ($investments as $investment) {
            
            // Verificação de segurança para não rodar 2x no mesmo dia
            if ($investment->last_profit_at && Carbon::parse($investment->last_profit_at)->isToday()) {
                continue;
            }

            try {
                DB::beginTransaction();

                // 1. Calcula o Lucro Bruto do dia
                $grossProfit = $this->calculateDailyProfitAmount($investment);

                if ($grossProfit > 0) {
                    
                    // 2. Aplica a Regra dos 20% (Retenção para Nível 1)
                    $referralShare = $grossProfit * 0.20; // 20% vai para o sponsor direto
                    $userShare = $grossProfit * 0.80;     // 80% vai para o usuário

                    // 3. Atualiza o Investimento (Registra o lucro BRUTO no histórico do investimento para controle de teto)
                    $investment->increment('total_profit', $grossProfit);
                    $investment->increment('current_balance', $grossProfit); // Ou apenas userShare se o saldo for sacável
                    $investment->update(['last_profit_at' => now()]);

                    // 4. Paga a parte do Usuário (80%)
                    $userWallet = $investment->user->wallets()->firstOrCreate(['type' => 'investment']); // Sugiro separar em carteira de lucro
                    $balanceBefore = $userWallet->balance;
                    $userWallet->increment('balance', $userShare);
                    $userWallet->increment('total_profit', $userShare);

                    // Cria registro na tabela profits (importante para relatórios e triggers se houver)
                    $profitRecord = Profit::create([
                        'user_id' => $investment->user_id,
                        'investment_id' => $investment->id,
                        'amount' => $grossProfit, // Registra o bruto para fins contábeis
                        'date' => now(),
                    ]);

                    // Transação do usuário
                    $userWallet->transactions()->create([
                        'user_id' => $investment->user_id,
                        'type' => 'profit',
                        'amount' => $userShare,
                        'description' => "Yield (80%) Inv #{$investment->id} - 20% Residual Bonus",
                        'balance_after' => $userWallet->balance,
                        'balance_before' => $balanceBefore
                    ]);

                    // 5. Distribui Bônus Residual
                    // Passamos o $grossProfit porque as porcentagens dos niveis 2+ (5%, 3%, 1%) geralmente são baseadas no lucro total, não no líquido.
                    // O CommissionService vai lidar com o destino dos 20% ($referralShare) e calcular os extras.
                    $this->commissionService->processResiduals($investment->user, $grossProfit, $investment, $referralShare);

                    // Eventos
                    event(new ProfitGenerated($investment->user_id, $userShare, $investment->id));
                } else {
                    // Se lucro for 0 (já atingiu teto), marcamos o dia
                    $investment->update(['last_profit_at' => now()]);
                }

                DB::commit();
                
                $stats['total_processed']++;
                $stats['total_profit_distributed'] += $grossProfit;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Erro processando inv #{$investment->id}: " . $e->getMessage());
                $stats['errors']++;
            }
        }

        return $stats;
    }


     /**
     * Finaliza investimentos expirados ou que atingiram 100%
     */
    public function finalizeExpiredInvestments()
    {
        // Busca investimentos que venceram o prazo OU já atingiram 100% do lucro (se essa for a regra de encerramento antecipado)
        $investments = Investment::where('status', 'active')->get();
        $count = 0;

        foreach ($investments as $investment) {
            $expiredTime = Carbon::parse($investment->expires_at)->isPast();
            $reachedCap = $investment->total_profit >= $investment->amount;

            if ($expiredTime || $reachedCap) {
                $investment->update(['status' => 'completed']);
                if ($investment->botInstance) {
                    $investment->botInstance->update(['is_active' => false]);
                }
                $count++;
            }
        }
        return $count;
    }

    /**
     * Obtém estatísticas gerais de investimentos
     * 
     * @param User $user Usuário (opcional)
     * @return array Estatísticas
     */
    public function getInvestmentStats(?User $user = null)
    {
        $query = Investment::query();
        if ($user) {
            $query->where('user_id', $user->id);
        }
        return [
            'total_active' => $query->where('status', 'active')->count(),
            'total_completed' => $query->where('status', 'completed')->count(),
            'total_invested' => $query->sum('amount'),
            'total_profit' => $query->sum('total_profit'),
            'average_return' => $query->avg('total_profit'),
        ];
    }

    /**
     * Cancela um investimento
     * 
     * @param Investment $investment
     * @return bool
     * @throws \Exception
     */
    public function cancelInvestment(Investment $investment)
    {
        if ($investment->status !== 'active') {
            throw new \Exception('Only active investments can be cancelled');
        }
        DB::beginTransaction();
        try {
            // Atualiza status
            $investment->update(['status' => 'cancelled']);
            // Desativa robô
            if ($investment->botInstance) {
                $investment->botInstance->update(['is_active' => false]);
            }
            // Devolve saldo + lucros para carteira
            $wallet = $investment->user->wallet;
            $returnAmount = $investment->current_balance;
            
            $wallet->increment('balance', $returnAmount);
            // Registra transação
            Transaction::create([
                'user_id' => $investment->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $returnAmount,
                'balance_before' => $wallet->balance - $returnAmount,
                'balance_after' => $wallet->balance,
                'description' => "Investment #{$investment->id} cancelled",
                'status' => 'completed',
            ]);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }




 }