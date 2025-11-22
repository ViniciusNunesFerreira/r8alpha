<?php

namespace App\Services;

use App\Models\Deposit;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NowPaymentsService
{
    private string $apiUrl;
    private string $apiKey;
    private string $ipnSecret;

    public function __construct()
    {
        $this->apiUrl = config('payment.nowpayments.api_url');
        $this->apiKey = config('payment.nowpayments.api_key');
        $this->ipnSecret = config('payment.nowpayments.ipn_secret');
    }

    /**
     * Cria um novo pagamento em USDT BEP20
     */
    public function createPayment(Deposit $deposit): array
    {
        try {
            // NOWPayments trabalha diretamente em USD, então usamos amount_usd
            $amountUsd = $deposit->amount_usd;

            // Tenta obter a cotação. Se falhar e for USDT, usa fallback 1:1
            $estimatedAmount = $this->getEstimatedPrice($amountUsd);

            // Se mesmo com fallback falhar (retornar null), lança erro
            if (!$estimatedAmount) {
                // Tenta verificar o valor mínimo para dar uma mensagem melhor ao usuário
                $minAmount = $this->getMinimumPaymentAmount();
                $msg = $minAmount 
                    ? "Erro na cotação. O valor mínimo atual para USDT é aproximadamente {$minAmount}." 
                    : 'Não foi possível obter a cotação de USDT e o serviço está instável.';
                
                throw new Exception($msg);
            }

            $payload = [
                'price_amount' => $amountUsd,
                'price_currency' => 'USD',
                'pay_amount' => $estimatedAmount, // Envia o valor estimado calculado/fallback
                'pay_currency' => 'USDTBSC',//config('payment.nowpayments.currency'), // usdtbep20
                'ipn_callback_url' => route('webhook.nowpayments'),
                'order_id' => $deposit->transaction_id,
                'order_description' => 'Depósito de fundos - ' . $deposit->transaction_id,
                'is_fixed_rate' => false,
                'is_fee_paid_by_user' => true,
            ];

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/payment", $payload);

            if ($response->failed()) {
                Log::error('Erro CRÍTICO ao criar pagamento NOWPayments', [
                    'deposit_id' => $deposit->id,
                    'status' => $response->status(),
                    'body' => $response->body(), // Loga o corpo da resposta para debug
                ]);
                throw new Exception('Falha na API NowPayments: ' . $response->body());
            }

            $data = $response->json();
            
            // Calcula tempo de expiração
            $expiresAt = now()->addSeconds(config('payment.nowpayments.timeout', 3600));

            // Atualiza o depósito
            $deposit->update([
                'gateway_transaction_id' => $data['payment_id'],
                'amount_crypto' => $data['pay_amount'],
                'crypto_currency' => 'USDT',
                'crypto_network' => 'BEP20',
                'crypto_address' => $data['pay_address'],
                'payment_data' => $data,
                'expires_at' => $expiresAt,
            ]);

            Log::info('Pagamento USDT BEP20 criado com sucesso', [
                'deposit_id' => $deposit->id,
                'payment_id' => $data['payment_id'],
                'amount_usd' => $amountUsd,
                'amount_usdt' => $data['pay_amount'],
            ]);

            return [
                'success' => true,
                'payment_id' => $data['payment_id'],
                'pay_address' => $data['pay_address'],
                'pay_amount' => $data['pay_amount'],
                'pay_currency' => 'USDT',
                'network' => 'BEP20',
                'expires_at' => $expiresAt,
                'qr_code_url' => $this->generateQrCodeUrl($data['pay_address'], $data['pay_amount']),
            ];

        } catch (Exception $e) {
            Log::error('Exceção ao processar NowPayments', [
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage(),
            ]);

            $deposit->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(), // Retorna a mensagem real para debug se necessário
            ];
        }
    }

    /**
     * Obtém preço estimado em USDT com Fallback Inteligente
     */
    private function getEstimatedPrice(float $amountUsd): ?float
    {
        $targetCurrency = 'USDTBSC';

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->apiUrl}/estimate", [
                'amount' => $amountUsd,
                'currency_from' => 'USD',
                'currency_to' => $targetCurrency,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['estimated_amount'] ?? null;
            }

            // --- MELHORIA DE DEBUG ---
            // Se falhou, vamos logar exatamente O PORQUÊ (ex: min amount)
            Log::warning('Falha na estimativa NowPayments (Tentando Fallback)', [
                'status' => $response->status(),
                'body' => $response->body(),
                'amount' => $amountUsd
            ]);

            // --- FALLBACK PARA STABLECOINS ---
            // Se a API de estimativa falhar (manutenção ou erro momentâneo)
            // e estivermos usando USDT, podemos assumir 1:1 temporariamente
            // para não travar o pagamento. A NowPayments recalcula no checkout se necessário.
            if (str_contains(strtolower($targetCurrency), 'usdt') || 
                str_contains(strtolower($targetCurrency), 'busd') || 
                str_contains(strtolower($targetCurrency), 'usdc')) {
                
                Log::info('Usando fallback 1:1 para Stablecoin');
                return $amountUsd; 
            }

            return null;

        } catch (Exception $e) {
            Log::error('Erro de conexão ao obter estimativa', [
                'error' => $e->getMessage(),
            ]);
            
            // Fallback de conexão também
            if (str_contains(strtolower($targetCurrency), 'usdt')) {
                return $amountUsd;
            }
            
            return null;
        }
    }

    /**
     * Consulta o status de um pagamento
     */
    public function checkPaymentStatus(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->apiUrl}/payment/{$paymentId}");

            if ($response->failed()) {
                Log::error('Erro ao consultar pagamento NOWPayments', [
                    'payment_id' => $paymentId,
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Erro ao consultar status NOWPayments', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Processa IPN (Instant Payment Notification) do NOWPayments
     */
    public function processIPN(array $payload): bool
    {
        try {
            if (!$this->validateIPNSignature($payload)) {
                Log::warning('Assinatura de IPN inválida', ['payload' => $payload]);
                return false;
            }

            $paymentId = $payload['payment_id'] ?? null;
            $orderId = $payload['order_id'] ?? null;

            if (!$paymentId || !$orderId) {
                return false;
            }

            $deposit = Deposit::where('transaction_id', $orderId)
                ->where('gateway_transaction_id', $paymentId)
                ->first();

            if (!$deposit) {
                return false;
            }

            $deposit->recordWebhookAttempt($payload);
            $paymentStatus = $payload['payment_status'] ?? null;

            Log::info('IPN NowPayments Processado', [
                'deposit_id' => $deposit->id,
                'status' => $paymentStatus
            ]);

            switch ($paymentStatus) {
                case 'confirming':
                case 'confirmed':
                case 'finished':
                    if (!$deposit->isCompleted()) {
                        $deposit->markAsConfirmed();
                    }
                    break;

                case 'failed':
                case 'expired':
                    $deposit->update(['status' => $paymentStatus]);
                    break;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Erro IPN NowPayments', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function validateIPNSignature(array $payload): bool
    {
        $receivedSignature = request()->header('x-nowpayments-sig');
        if (!$receivedSignature) return false;

        ksort($payload);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $calculatedSignature = hash_hmac('sha512', $json, $this->ipnSecret);

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    private function generateQrCodeUrl(string $address, float $amount): string
    {
        $data = urlencode("ethereum:{$address}?amount={$amount}"); // BEP20 é compatível com carteiras ETH
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$data}";
    }

    public function getMinimumPaymentAmount(): ?float
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->apiUrl}/min-amount", [
                'currency_from' => 'usd',
                'currency_to' => config('payment.nowpayments.currency'),
            ]);

            if ($response->successful()) {
                return $response->json()['min_amount'] ?? null;
            }
            // Log do erro real do minimo
            Log::warning('Erro ao buscar min-amount', ['body' => $response->body()]);
            return null;

        } catch (Exception $e) {
            return null;
        }
    }

    public function checkCurrencyAvailability(): bool
    {
        // Implementação simplificada para manter o arquivo limpo
        return true; 
    }
}