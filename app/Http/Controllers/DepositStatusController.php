<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepositStatusController extends Controller
{
    /**
     * Endpoint para Server-Sent Events (SSE)
     * Mantém conexão aberta e envia atualizações em tempo real
     */
    public function stream(Request $request, string $transactionId): StreamedResponse
    {
        return response()->stream(function () use ($transactionId) {
            // Configurar headers SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no'); // Nginx
            
            // Prevenir timeout
            set_time_limit(0);
            
            // Intervalo de verificação (segundos)
            $checkInterval = 10;
            $maxDuration = 300; // 5 minutos máximo
            $startTime = time();
            
            $lastStatus = null;
            
            while (true) {
                // Verificar timeout
                if ((time() - $startTime) >= $maxDuration) {
                    echo "event: timeout\n";
                    echo "data: {\"message\": \"Connection timeout\"}\n\n";
                    ob_flush();
                    flush();
                    break;
                }
                
                // Verificar se cliente ainda está conectado
                if (connection_aborted()) {
                    break;
                }
                
                try {
                    // Buscar status do depósito
                    $deposit = Deposit::where('transaction_id', $transactionId)
                        ->where('user_id', auth()->user()->id)
                        ->first();
                    
                    if (!$deposit) {
                        echo "event: error\n";
                        echo "data: {\"message\": \"Deposit not found\"}\n\n";
                        ob_flush();
                        flush();
                        break;
                    }
                    
                    // Enviar dados se status mudou
                    if ($deposit->status !== $lastStatus) {
                        $data = [
                            'status' => $deposit->status,
                            'status_label' => $deposit->status_label,
                            'is_completed' => $deposit->isCompleted(),
                            'is_expired' => $deposit->isExpired(),
                            'updated_at' => $deposit->updated_at->toIso8601String(),
                        ];
                        
                        echo "event: status-update\n";
                        echo "data: " . json_encode($data) . "\n\n";
                        ob_flush();
                        flush();
                        
                        $lastStatus = $deposit->status;
                    }
                    
                    // Se completou ou expirou, encerrar stream
                    if ($deposit->isCompleted() || $deposit->isExpired()) {
                        echo "event: complete\n";
                        echo "data: {\"message\": \"Stream ended\"}\n\n";
                        ob_flush();
                        flush();
                        break;
                    }
                    
                    // Enviar heartbeat
                    echo "event: heartbeat\n";
                    echo "data: {\"timestamp\": " . time() . "}\n\n";
                    ob_flush();
                    flush();
                    
                } catch (\Exception $e) {
                    \Log::error('SSE Error: ' . $e->getMessage());
                    
                    echo "event: error\n";
                    echo "data: {\"message\": \"Internal error\"}\n\n";
                    ob_flush();
                    flush();
                    break;
                }
                
                // Aguardar antes da próxima verificação
                sleep($checkInterval);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
    
    /**
     * Endpoint para Long Polling
     * Aguarda até que haja uma mudança de status ou timeout
     */
    public function checkStatusLongPolling(Request $request, string $transactionId): JsonResponse
    {
        $maxWaitTime = 60; // 50 segundos
        $checkInterval = 5; // Verificar a cada 2 segundos
        $startTime = time();
        
        try {
            // Buscar depósito inicial
            $deposit = Deposit::where('transaction_id', $transactionId)
                ->where('user_id', auth()->user()->id)
                ->first();
            
            if (!$deposit) {
                return response()->json([
                    'error' => 'Deposit not found'
                ], 404);
            }
            
            $initialStatus = $deposit->status;
            
            // Loop de verificação
            while ((time() - $startTime) < $maxWaitTime) {
                // Re-buscar depósito
                $deposit->refresh();
                
                // Se status mudou, retornar imediatamente
                if ($deposit->status !== $initialStatus || 
                    $deposit->isCompleted() || 
                    $deposit->isExpired()) {
                    return $this->formatDepositResponse($deposit);
                }
                
                // Aguardar antes da próxima verificação
                sleep($checkInterval);
                
                // Verificar se cliente ainda está conectado
                if (connection_aborted()) {
                    break;
                }
            }
            
            // Timeout - retornar status atual
            return $this->formatDepositResponse($deposit);
            
        } catch (\Exception $e) {
            \Log::error('Long Polling Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }
    
    /**
     * Endpoint simples para verificação de status (backward compatibility)
     * Retorna imediatamente sem aguardar
     */
    public function checkStatus(Request $request, string $transactionId): JsonResponse
    {
        try {
            $deposit = Deposit::where('transaction_id', $transactionId)
                ->where('user_id', auth()->user()->id)
                ->first();
            
            if (!$deposit) {
                return response()->json([
                    'error' => 'Deposit not found'
                ], 404);
            }
            
            return $this->formatDepositResponse($deposit);
            
        } catch (\Exception $e) {
            \Log::error('Status Check Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Formata resposta com dados do depósito
     */
    private function formatDepositResponse(Deposit $deposit): JsonResponse
    {
        return response()->json([
            'status' => $deposit->status,
            'status_label' => $deposit->status_label,
            'is_completed' => $deposit->isCompleted(),
            'is_paid' => $deposit->isPaid(),
            'is_expired' => $deposit->isExpired(),
            'transaction_id' => $deposit->transaction_id,
            'amount_usd' => $deposit->amount_usd,
            'formatted_amount_usd' => $deposit->formatted_amount_usd,
            'created_at' => $deposit->created_at->toIso8601String(),
            'updated_at' => $deposit->updated_at->toIso8601String(),
            'confirmed_at' => $deposit->confirmed_at?->toIso8601String(),
            'expires_at' => $deposit->expires_at?->toIso8601String(),
        ]);
    }
}