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

            // Obter taxa de câmbio estimada de USD para USDT
            $estimatedAmount = $this->getEstimatedPrice($amountUsd);

            if (!$estimatedAmount) {
                throw new Exception('Não foi possível obter a cotação de USDT.');
            }

            $payload = [
                'price_amount' => $amountUsd,
                'price_currency' => 'usd',
                'pay_currency' => config('payment.nowpayments.currency'), // usdtbep20
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
                Log::error('Erro ao criar pagamento NOWPayments', [
                    'deposit_id' => $deposit->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Falha ao criar pagamento: ' . $response->body());
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
                'network' => 'BEP20',
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
            Log::error('Erro ao criar pagamento NOWPayments', [
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage(),
            ]);

            $deposit->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'Não foi possível gerar o pagamento. Tente novamente.',
            ];
        }
    }

    /**
     * Obtém preço estimado em USDT
     */
    private function getEstimatedPrice(float $amountUsd): ?float
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->apiUrl}/estimate", [
                'amount' => $amountUsd,
                'currency_from' => 'usd',
                'currency_to' => config('payment.nowpayments.currency'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['estimated_amount'] ?? null;
            }

            return null;

        } catch (Exception $e) {
            Log::error('Erro ao obter estimativa de preço', [
                'error' => $e->getMessage(),
            ]);
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
            // Valida assinatura do IPN
            if (!$this->validateIPNSignature($payload)) {
                Log::warning('Assinatura de IPN inválida', ['payload' => $payload]);
                return false;
            }

            $paymentId = $payload['payment_id'] ?? null;
            $orderId = $payload['order_id'] ?? null;

            if (!$paymentId || !$orderId) {
                Log::warning('IPN sem payment_id ou order_id', ['payload' => $payload]);
                return false;
            }

            // Busca o depósito
            $deposit = Deposit::where('transaction_id', $orderId)
                ->where('gateway_transaction_id', $paymentId)
                ->first();

            if (!$deposit) {
                Log::warning('Depósito não encontrado para IPN', [
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                ]);
                return false;
            }

            // Registra a tentativa de IPN
            $deposit->recordWebhookAttempt($payload);

            $paymentStatus = $payload['payment_status'] ?? null;

            Log::info('IPN recebido do NOWPayments', [
                'deposit_id' => $deposit->id,
                'payment_id' => $paymentId,
                'status' => $paymentStatus,
            ]);

            // Processa de acordo com o status
            switch ($paymentStatus) {
                case 'waiting':
                    // Aguardando pagamento - não faz nada
                    break;

                case 'confirming':
                    // Pagamento detectado, aguardando confirmações
                    if ($deposit->isPending()) {
                        $deposit->markAsPaid();
                    }
                    break;

                case 'confirmed':
                case 'finished':
                    // Pagamento confirmado
                    if (!$deposit->isCompleted()) {
                        // Valida o valor recebido
                        $actuallyPaid = floatval($payload['price_amount'] ?? 0);
                        $expectedAmount = floatval($deposit->amount_usd);

                        if (abs($actuallyPaid - $expectedAmount) > 0.01) {
                            Log::warning('Valor recebido difere do esperado', [
                                'deposit_id' => $deposit->id,
                                'esperado' => $expectedAmount,
                                'recebido' => $actuallyPaid,
                            ]);
                        }

                        $deposit->markAsConfirmed();

                        Log::info('Depósito USDT confirmado via IPN', [
                            'deposit_id' => $deposit->id,
                            'amount_usd' => $deposit->amount_usd,
                            'amount_crypto' => $deposit->amount_crypto,
                        ]);

                        // Dispara evento ou notificação
                        // event(new DepositConfirmed($deposit));
                    }
                    break;

                case 'failed':
                case 'expired':
                    // Pagamento falhou ou expirou
                    $deposit->update(['status' => $paymentStatus]);
                    break;

                case 'refunded':
                case 'partially_paid':
                    // Situações especiais - registrar e analisar manualmente
                    Log::warning('Status especial recebido no IPN', [
                        'deposit_id' => $deposit->id,
                        'status' => $paymentStatus,
                        'payload' => $payload,
                    ]);
                    break;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Erro ao processar IPN NOWPayments', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Valida assinatura do IPN
     */
    private function validateIPNSignature(array $payload): bool
    {
        $receivedSignature = request()->header('x-nowpayments-sig');

        if (!$receivedSignature) {
            return false;
        }

        // Ordena o payload por chave
        ksort($payload);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $calculatedSignature = hash_hmac('sha512', $json, $this->ipnSecret);

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Gera URL para QR Code
     */
    private function generateQrCodeUrl(string $address, float $amount): string
    {
        // Formato padrão BEP20 para QR Code
        // binance://<address>?amount=<amount>
        $data = urlencode("binance://{$address}?amount={$amount}");
        
        // Usar serviço de QR Code (exemplo: api.qrserver.com)
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$data}";
    }

    /**
     * Verifica disponibilidade de USDT BEP20
     */
    public function checkCurrencyAvailability(): bool
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->apiUrl}/currencies");

            if ($response->successful()) {
                $currencies = $response->json()['currencies'] ?? [];
                return in_array('usdtbep20', $currencies);
            }

            return false;

        } catch (Exception $e) {
            Log::error('Erro ao verificar disponibilidade de moeda', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Obtém o status mínimo de pagamento
     */
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
                $data = $response->json();
                return $data['min_amount'] ?? null;
            }

            return null;

        } catch (Exception $e) {
            Log::error('Erro ao obter valor mínimo', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}