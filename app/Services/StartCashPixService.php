<?php

namespace App\Services;

use App\Models\Deposit;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;


class StartCashPixService
{
    private string $apiUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->apiUrl = config('payment.pix.api_url');
        $this->clientId = config('payment.pix.client_id');
        $this->clientSecret = config('payment.pix.client_secret');
    }


    /**
     * Cria uma nova cobrança PIX
     */
    public function createCharge(Deposit $deposit): array
    {
        try {

            $url = "{$this->apiUrl}/transactions";
            // Autenticação (Basic Auth)
            $auth = base64_encode($this->clientSecret.':x');

           // Conversão de moeda e atualização do depósito
            $conversionRate = config('payment.usd_to_brl_rate');
            $amountBrl = $deposit->amount_usd * $conversionRate;
            
            $deposit->update([
                'amount_brl' => $amountBrl,
                'conversion_rate' => $conversionRate,
            ]);

            $expirationMinutes = config('payment.pix.expiration_minutes', 30);
            $expiresAt = now()->addMinutes($expirationMinutes);

            // Payload para criação da cobrança
            $amount = round($amountBrl * 100);
            $product = [ [ "title" => "Add Balance R8-Alpha", "unitPrice" => $amount, "quantity" => 1, "tangible" => false, "externalRef" => $deposit->transaction_id ] ];
            $customer = [ "document" => [ "type" => "cpf", "number" => $this->formatCpf($deposit->user->cpf ?? '109.567.543-50')], "name" => $deposit->user->name, "email" => $deposit->user->email ];

            $payload = [
                "paymentMethod" => "pix" , 
                "customer" => $customer,
                "amount" => $amount,
                "pix" => ["expiresInDays" => 1],
                "items" => $product
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => "Basic {$auth}",
            ])->timeout(10)->post($url, $payload)->throw() ->json();


            // O $response agora é um array PHP (já decodificado)
            $result = (object) $response;

            // Atualiza o depósito com os dados da cobrança
            $deposit->update([
                'gateway_transaction_id' => $result->id,
                'pix_code' => $result->pix['qrcode'] ?? null,
                'qr_code_image' => $result->pix['qrcode'] ?? null,
                'payment_data' => $result,
                'expires_at' => $expiresAt,
            ]);


            return [
                'success' => true,
                'txid' => $result->id,
                'qr_code' => $result->pix['qrcode'] ?? null,
                'pix_code' => $result->pix['qrcode'] ?? null,
                'expires_at' => $expiresAt,
                'amount_brl' => $amountBrl,
            ];

        } catch (RequestException $e) {
            // Captura exceções lançadas pelo ->throw() (status 4xx ou 5xx)
            $errorDetails = $e->response->body(); 
            
            Log::error('Erro ao criar cobrança PIX (HTTP Error)', [
                'deposit_id' => $deposit->id,
                'http_status' => $e->response->status(),
                'error_message' => $e->getMessage(),
                'response_body' => $errorDetails,
            ]);

            $deposit->markAsFailed("API Error: {$e->response->status()} - {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'We were unable to generate the PIX payment. Please try again. (API returned error)',
            ];

        } catch (Exception $e) {
            // Captura outros erros, como falha de conexão ou erros de lógica
            Log::error('Erro ao criar cobrança PIX (General Error)', [
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage(),
            ]);

            $deposit->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'We were unable to generate the PIX payment. Please try again.',
            ];
        }
    }

    /**
     * Consulta o status de uma cobrança PIX
     */
    public function checkPaymentStatus(string $txid): ?array
    {
        try {
            
            $url = "{$this->apiUrl}/transactions";
            // Autenticação (Basic Auth)
            $auth = base64_encode($this->clientSecret.':x');

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => "Basic {$auth}",
            ])->timeout(10)->get($url)->throw()->json();


            Log::debug('Resposta da API de Consulta de Transação', ['response_data' => $response]);

            return $response;

        } catch (Exception $e) {
            Log::error('Erro ao consultar status PIX', [
                'txid' => $txid,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Processa webhook recebido do StartCash
     */
    public function processWebhook(array $payload): bool
    {

        $type = data_get($payload, 'type');
        
        try {
           
            if($type == "transaction"){
                // Acesso seguro para o ID da transação
                $txid = data_get($payload, 'data.items.0.externalRef');

                if (data_get($payload, 'type') !== 'transaction' || data_get($payload, 'data.status') !== 'paid' || !$txid) {
                    Log::info('Webhook ignorado (tipo ou status não pago/incompleto).', ['payload' => $payload]);
                    return true; 
                }

                // Busca o depósito pelo txid
                $deposit = Deposit::where('transaction_id', $txid)->first();

                if (!$deposit) {
                    Log::warning('Depósito não encontrado para webhook', ['transaction_id' => $txid]);
                    return true; 
                }
  
                // Adiciona uma camada extra de idempotência.
                if ($deposit->status === 'paid') {
                    Log::info('Depósito já está pago, ignorando reprocessamento.', ['deposit_id' => $deposit->id]);
                    return true;
                }

                // Registra a tentativa de webhook
                $deposit->recordWebhookAttempt($payload);

                // 2. Consulta o status atualizado da cobrança (Dupla Verificação)
                $chargeStatus = $this->checkPaymentStatus(data_get($payload, 'id'));

                if (!$chargeStatus) {
                    \Log::info('Falhou na consulta atualizada do status do pagamento Start Cash');
                    return false;
                }

                if (data_get($chargeStatus, 'data.0.status') !== 'paid') {
                    
                    \Log::info('Consulta de status falhou ou retornou status diferente de paid.', [
                        'deposit_id' => $deposit->id,
                        'status_externo' => data_get($chargeStatus, 'data.0.status') ?? 'falha/invalido'
                    ]);

                    // O ideal aqui seria marcar o depósito como 'revisão'/'pendente' novamente, se for o caso.
                    return false; 
                }
                        
                // Marca como pago e confirma o depósito
                if ($deposit->isPending()) {
                    $paidAt = data_get($payload, 'data.paidAt');
                    $deposit->markAsConfirmed($paidAt);
                }

            }


            //Valida transferencias
            if($type == "transfer"){
                if($payload['status'] == 'COMPLETED'){
                  
                }
            }


            return true;

        } catch (Exception $e) {
            Log::error('Erro ao processar webhook PIX', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Valida a assinatura do webhook
     */
    private function validateWebhookSignature(array $payload): bool
    {
        // Implementar validação de assinatura conforme documentação StartCash
        // Geralmente envolve verificar um hash HMAC ou JWT
        
        $webhookSecret = config('payment.pix.webhook_secret');
        
        if (!$webhookSecret) {
            return true; // Se não tiver secret configurado, aceita (não recomendado em produção)
        }

        // Exemplo de validação (adaptar conforme API real)
        $signature = request()->header('X-Webhook-Signature');
        
        if (!$signature) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha256', json_encode($payload), $webhookSecret);

        return hash_equals($calculatedSignature, $signature);
    }


    private function formatCpf(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }
}