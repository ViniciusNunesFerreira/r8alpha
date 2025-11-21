<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvestmentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Investment;
use Illuminate\Support\Facades\Cache;

class ProcessDailyProfitsCommand extends Command
{
    protected $signature = 'profits:process 
                            {--dry-run : Executa sem salvar alterações}
                            {--investment= : Processa investimento específico}
                            {--force : Força processamento mesmo se já processou hoje}';

    protected $description = 'Process daily profits for investments (runs every hour, pays after 24h)';

    protected $investmentService;

    public function __construct(InvestmentService $investmentService)
    {
        parent::__construct();
        $this->investmentService = $investmentService;
    }

    public function handle()
    {
        $this->info('🔄 Starting Daily Profits Processing...');
        $this->newLine();
        $startTime = microtime(true);

        if ($this->option('dry-run')) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be saved');
            $this->newLine();
        }

        try {
            if ($this->option('dry-run')) {
                DB::beginTransaction();
            }

            $now = Carbon::now();
            $this->info("⏰ Current time: {$now->format('Y-m-d H:i:s')}");
            $this->newLine();

            // Busca investimentos ativos que precisam processar lucro
            $investments = $this->getInvestmentsToProcess($now);

            $this->info("📊 Found {$investments->count()} investment(s) ready for profit distribution");
            $this->newLine();

            $stats = [
                'total_processed' => 0,
                'total_profit_distributed' => 0,
                'errors' => 0,
                'skipped' => 0,
            ];

            $bar = $this->output->createProgressBar($investments->count());
            $bar->start();

            foreach ($investments as $investment) {
                try {
                    $result = $this->processInvestmentProfit($investment, $now);
                    
                    if ($result['processed']) {
                        $stats['total_processed']++;
                        $stats['total_profit_distributed'] += $result['profit'];

                        Cache::forget("user_stats_{$investment->user_id}");
                        
                        $this->newLine();
                        $this->info("✅ Investment #{$investment->id}: +${$result['profit']}");
                    } else {
                        $stats['skipped']++;
                        $this->newLine();
                        $this->warn("⏭️  Investment #{$investment->id}: {$result['reason']}");
                    }
                    
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->newLine();
                    $this->error("❌ Investment #{$investment->id}: {$e->getMessage()}");
                    
                    Log::error('Error processing profit for investment', [
                        'investment_id' => $investment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Finaliza investimentos expirados
            $this->info('🔍 Checking for expired investments...');
            $finalized = $this->investmentService->finalizeExpiredInvestments();
            
            if ($finalized > 0) {
                $this->info("✅ Finalized {$finalized} expired investment(s)");
            }

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->newLine();
                $this->warn('🔄 Transaction rolled back (dry-run mode)');
            }

            $executionTime = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('✨ Daily profits processed successfully!');
            $this->newLine();

            // Tabela de estatísticas
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Investments Processed', $stats['total_processed']],
                    ['Total Profit Distributed', '$' . number_format($stats['total_profit_distributed'], 2)],
                    ['Skipped', $stats['skipped']],
                    ['Errors', $stats['errors']],
                    ['Investments Finalized', $finalized],
                    ['Execution Time', $executionTime . 's'],
                ]
            );

            // Log detalhado
            Log::info('Daily profits processed', [
                'stats' => $stats,
                'finalized' => $finalized,
                'execution_time' => $executionTime,
                'dry_run' => $this->option('dry-run')
            ]);

            // Alertas
            if ($stats['errors'] > 0) {
                $this->newLine();
                $this->warn("⚠️  {$stats['errors']} error(s) occurred during processing");
                $this->warn('Check logs for details');
            }

        

            return Command::SUCCESS;

        } catch (\Exception $e) {
            if ($this->option('dry-run')) {
                DB::rollBack();
            }
            
            $this->newLine();
            $this->error('❌ Critical error processing daily profits');
            $this->error($e->getMessage());
            
            Log::error('Daily profits processing critical error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Busca investimentos que precisam processar lucro
     * 
     * Lógica: Processa lucro 24h após started_at e depois a cada 24h
     */
    protected function getInvestmentsToProcess(Carbon $now)
    {
        $investmentId = $this->option('investment');
        $force = $this->option('force');

        $query = Investment::where('status', 'active')
            ->where('started_at', '<=', $now)
            ->where('expires_at', '>', $now);

        // Se ID específico fornecido
        if ($investmentId) {
            $query->where('id', $investmentId);
        }

        $investments = $query->get();

        // Filtra investimentos que precisam processar (24h desde último processamento)
        return $investments->filter(function ($investment) use ($now, $force) {
            // Se forçar, processa todos
            if ($force) {
                return true;
            }

            // Primeiro pagamento: 24h após started_at
            $firstPaymentTime = Carbon::parse($investment->started_at)->addDay();
            
            // Se nunca processou lucro e já passou 24h
            if (!$investment->last_profit_at && $now->greaterThanOrEqualTo($firstPaymentTime)) {
                return true;
            }

            // Próximos pagamentos: 24h após último pagamento
            if ($investment->last_profit_at) {
                $nextPaymentTime = Carbon::parse($investment->last_profit_at)->addDay();
                
                if ($now->greaterThanOrEqualTo($nextPaymentTime)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Processa lucro de um investimento específico
     */
    protected function processInvestmentProfit(Investment $investment, Carbon $now)
    {
        // Verifica se já processou hoje (proteção adicional)
        if ($investment->last_profit_at && !$this->option('force')) {
            $lastProfitDate = Carbon::parse($investment->last_profit_at);
            $hoursSinceLastProfit = $now->diffInHours($lastProfitDate);
            
            if ($hoursSinceLastProfit < 24) {
                return [
                    'processed' => false,
                    'reason' => "Processed {$hoursSinceLastProfit}h ago, needs 24h",
                    'profit' => 0
                ];
            }
        }

        DB::beginTransaction();
        
        try {
            $profit = $this->investmentService->calculateDailyProfit($investment);
            
            // Atualiza investimento
            $investment->increment('current_balance', $profit);
            $investment->increment('total_profit', $profit);
            $investment->update(['last_profit_at' => $now]);

            // Atualiza carteira do usuário
            $wallet = $investment->user->wallets()->where('type', 'investment')->first();
            
            if (!$wallet) {
                $wallet = $investment->user->wallets()->create([
                    'type' => 'investment',
                    'balance' => 0,
                ]);
            }
            
            $wallet->increment('balance', $profit);
            $wallet->increment('total_profit', $profit);

            // Registra transação
            $wallet->transactions()->create([
                'user_id' => $investment->user_id,
                'type' => 'profit',
                'amount' => $profit,
                'balance_before' => $wallet->balance - $profit,
                'balance_after' => $wallet->balance,
                'description' => "Daily profit from investment #{$investment->id}",
                'status' => 'completed',
            ]);

            // Dispara eventos
            event(new \App\Events\ProfitGenerated(
                $investment->user_id, 
                $profit, 
                $investment->id
            ));

            event(new \App\Events\InvestmentUpdated($investment));

            DB::commit();

            return [
                'processed' => true,
                'profit' => $profit,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}