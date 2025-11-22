<?php

namespace App\Jobs;

use App\Models\BotInstance;
use App\Services\ArbitrageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanArbitrageOpportunities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * A instância do bot que será processada
     *
     * @var BotInstance
     */
    public $botInstance;

    // Tentativas em caso de falha
    public $tries = 3;
    
    // Timeout de execução (segundos)
    public $timeout = 120;

    /**
     * Construtor do Job
     * 
     * @param BotInstance $botInstance
     */
    public function __construct(BotInstance $botInstance)
    {
        $this->botInstance = $botInstance;
    }   

    /**
     * Execute the job.
     */
    public function handle(ArbitrageService $arbitrageService)
    {
        try {
            // Verifica se o robô está ativo
            if (!$this->botInstance->is_active) {
                Log::info('Bot is inactive, skipping scan', [
                    'bot_instance_id' => $this->botInstance->id
                ]);
                return;
            }

            // Obtém configurações do robô
            $config = $this->botInstance->config;
            $baseCurrencies = $config['base_currencies'] ?? ['BTC', 'ETH', 'USDT'];

            Log::info('Starting arbitrage scan', [
                'bot_instance_id' => $this->botInstance->id,
                'base_currencies' => $baseCurrencies
            ]);

            // Busca oportunidades
            $opportunities = $arbitrageService->findOpportunities(
                $this->botInstance, 
                $baseCurrencies
            );

            Log::info('Arbitrage scan completed', [
                'bot_instance_id' => $this->botInstance->id,
                'opportunities_found' => count($opportunities)
            ]);

            // Se encontrou oportunidades, executa a melhor
            if (!empty($opportunities)) {
                $bestOpportunity = collect($opportunities)
                    ->sortByDesc('profit_percentage')
                    ->first();

                Log::info('Executing best opportunity', [
                    'opportunity_id' => $bestOpportunity->id,
                    'profit_percentage' => $bestOpportunity->profit_percentage
                ]);

                $result = $arbitrageService->executeArbitrage($bestOpportunity);

                if ($result['success']) {
                    Log::info('Arbitrage executed successfully', [
                        'opportunity_id' => $bestOpportunity->id,
                        'profit' => $result['profit']
                    ]);
                } else {
                    Log::error('Arbitrage execution failed', [
                        'opportunity_id' => $bestOpportunity->id,
                        'error' => $result['error']
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Arbitrage scan error', [
                'bot_instance_id' => $this->botInstance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retentar o job
            $this->release(60); // Aguarda 60 segundos antes de retentar
        }
    }

    /**
     * Método chamado quando o job falha permanentemente
     */
    public function failed(\Throwable $exception)
    {
        Log::critical('Arbitrage scan job failed permanently', [
            'bot_instance_id' => $this->botInstance->id,
            'error' => $exception->getMessage()
        ]);

        // Pode enviar notificação ao usuário
        // notification($this->botInstance->user)->send(new ScanFailedNotification());
    }
}