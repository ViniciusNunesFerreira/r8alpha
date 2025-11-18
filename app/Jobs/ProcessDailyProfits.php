 <?php
 namespace App\Jobs;
 use App\Services\InvestmentService;
 use Illuminate\Bus\Queueable;
 use Illuminate\Contracts\Queue\ShouldQueue;
 use Illuminate\Foundation\Bus\Dispatchable;
 use Illuminate\Queue\InteractsWithQueue;
 use Illuminate\Queue\SerializesModels;
 use Illuminate\Support\Facades\Log;

 class ProcessDailyProfits implements ShouldQueue
 {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
     public $tries = 1; // Não retentar
    public $timeout = 600; // 10 minutos
    /**
     * Executa o processamento de lucros diários
     * 
     * @param InvestmentService $investmentService
     * @return void
     */
    public function handle(InvestmentService $investmentService)
    {
        Log::info('Starting daily profits processing');
        $startTime = microtime(true);
        try {
            // Processa lucros
            $stats = $investmentService->processDailyProfits();
            // Finaliza investimentos expirados
            $finalized = $investmentService->finalizeExpiredInvestments();
            $executionTime = microtime(true) - $startTime;
            Log::info('Daily profits processing completed', [
                'total_processed' => $stats['total_processed'],
                'total_profit_distributed' => $stats['total_profit_distributed'],
                'errors' => $stats['errors'],
                'investments_finalized' => $finalized,
                'execution_time' => round($executionTime, 2) . 's'
            ]);
            // Enviar relatório por email (opcional)
            // Mail::to(config('mail.admin'))->send(new DailyProfitsReport($stats));
        } catch (\Exception $e) {
            Log::error('Daily profits processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Notificar administradores
            // notification(User::admins())->send(new ProcessingFailedNotification($e));
        }
    }
 }