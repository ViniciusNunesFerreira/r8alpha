<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvestmentService;
use Illuminate\Support\Facades\Log;
 use Illuminate\Support\Facades\DB;

class ProcessDailyProfitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'profits:process 
                            {--dry-run : Executa sem salvar alterações}
                            {--investment= : Processa investimento específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily profits for all active investments';

    protected $investmentService;


    public function __construct(InvestmentService $investmentService)
    {
        parent::__construct();
        $this->investmentService = $investmentService;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing Daily Profits...');
        $this->newLine();
        $startTime = microtime(true);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be saved');
            $this->newLine();
        }

        try {
            if ($this->option('dry-run')) {
                DB::beginTransaction();
            }
            // Processa lucros
            $stats = $this->investmentService->processDailyProfits();
            // Finaliza investimentos expirados
            $this->info('Checking for expired investments...');
            $finalized = $this->investmentService->finalizeExpiredInvestments();

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn('Transaction rolled back (dry-run)');
            }
            $executionTime = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('Daily profits processed successfully!');
            $this->newLine();

            // Tabela de estatísticas
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Investments Processed', $stats['total_processed']],
                    ['Total Profit Distributed', '$' . number_format($stats['total_profit_distributed'], 2)],
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
                $this->warn("{$stats['errors']} error(s) occurred during processing");
                $this->warn('Check logs for details');
            }
            return Command::SUCCESS;

        } catch (\Exception $e) {
            if ($this->option('dry-run')) {
                DB::rollBack();
            }
            $this->error('Error processing daily profits');
            $this->error($e->getMessage());
            
            Log::error('Daily profits processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
