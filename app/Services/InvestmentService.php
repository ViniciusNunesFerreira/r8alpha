<?php
namespace App\Services;

use App\Models\Investment;
use App\Models\User;
use App\Models\InvestmentPlan;
use App\Models\BotInstance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Events\InvestmentUpdated;
use App\Events\ProfitGenerated;

 class InvestmentService
 {

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

            broadcast(new InvestmentUpdated($investment))->toOthers();

            DB::commit();
            return $investment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Calcula lucro diário de um investimento
     * 
     * O lucro é aleatório entre o mínimo e máximo do plano
     * para simular variação natural do mercado
     * 
     * @param Investment $investment
     * @return float Valor do lucro diário
     */
    public function calculateDailyProfit(Investment $investment)
    {
        $plan = $investment->investmentPlan;
        
        // Gera percentual aleatório entre min e max
        $profitPercentage = rand(
            $plan->daily_return_min * 100, 
            $plan->daily_return_max * 100
        ) / 100;
        // Calcula sobre o valor inicial do investimento
        return ($investment->amount * $profitPercentage) / 100;
    }


    /**
     * Processa lucros diários de todos os investimentos ativos
     * 
     * Executado diariamente via Cron às 00:00
     * 
     * @return array Estatísticas do processamento
     */
    public function processDailyProfits()
    {
        $investments = Investment::where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();
        $stats = [
            'total_processed' => 0,
            'total_profit_distributed' => 0,
            'errors' => 0,
        ];
        foreach ($investments as $investment) {
            try {
                $profit = $this->calculateDailyProfit($investment);
                
                DB::transaction(function () use ($investment, $profit) {
                    // Atualiza investimento
                    $investment->increment('current_balance', $profit);
                    $investment->increment('total_profit', $profit);

                    broadcast(new ProfitGenerated( $investment->user_id, $profit, $investment->id))->toOthers();

                    // Atualiza carteira do usuário
                    $wallet = $investment->user->wallet;
                    $wallet->increment('balance', $profit);
                    $wallet->increment('total_profit', $profit);
                    // Registra transação
                    Transaction::create([
                        'user_id' => $investment->user_id,
                        'wallet_id' => $wallet->id,
                        'type' => 'profit',
                        'amount' => $profit,
                        'balance_before' => $wallet->balance - $profit,
                        'balance_after' => $wallet->balance,
                        'description' => "Daily profit from investment #{$investment->id}",
                        'status' => 'completed',
                    ]);
                });
                $stats['total_processed']++;
                $stats['total_profit_distributed'] += $profit;
            } catch (\Exception $e) {
                Log::error('Error processing daily profit', [
                    'investment_id' => $investment->id,
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }
        return $stats;
    }

     /**
     * Finaliza investimentos expirados
     * 
     * @return int Quantidade de investimentos finalizados
     */
    public function finalizeExpiredInvestments()
    {
        $expired = Investment::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();
        $count = 0;
        foreach ($expired as $investment) {
            try {
                DB::transaction(function () use ($investment) {
                    // Atualiza status
                    $investment->update(['status' => 'completed']);
                    // Desativa robô associado
                    if ($investment->botInstance) {
                        $investment->botInstance->update(['is_active' => false]);
                    }
                });
                $count++;
            } catch (\Exception $e) {
                Log::error('Error finalizing investment', [
                    'investment_id' => $investment->id,
                    'error' => $e->getMessage()
                ]);
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