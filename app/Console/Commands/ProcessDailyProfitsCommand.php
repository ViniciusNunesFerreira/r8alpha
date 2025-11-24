<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvestmentService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessDailyProfitsCommand extends Command
{
    protected $signature = 'profits:process';
    protected $description = 'Processa lucros diários e distribui residuais';

    protected $investmentService;

    public function __construct(InvestmentService $investmentService)
    {
        parent::__construct();
        $this->investmentService = $investmentService;
    }

    public function handle()
    {
        $this->info('🔄 Iniciando processamento de lucros...');
        $startTime = microtime(true);

        try {
            // Processa Lucros e Comissões
            $stats = $this->investmentService->processDailyProfits();
            
            // Finaliza Expirados
            $finalized = $this->investmentService->finalizeExpiredInvestments();

            $executionTime = round(microtime(true) - $startTime, 2);

            $this->info('✅ Processamento concluído!');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Investimentos Pagos', $stats['total_processed']],
                    ['Lucro Bruto Distribuído', '$' . number_format($stats['total_profit_distributed'], 2)],
                    ['Erros', $stats['errors']],
                    ['Finalizados/Expirados', $finalized],
                    ['Tempo', $executionTime . 's'],
                ]
            );

        } catch (\Exception $e) {
            $this->error('❌ Erro crítico: ' . $e->getMessage());
            Log::error('Erro no comando profits:process', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}