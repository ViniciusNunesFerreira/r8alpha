<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Investment;
use App\Services\NowPaymentsService;
use App\Services\StartCashPixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Cria um pagamento (para depósito ou investimento)
     */
    public function create(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:deposit,investment',
            'amount' => 'required|numeric|min:' . config('payment.deposit_limits.min_usd'),
            'payment_method' => 'required|in:pix,crypto',
            'investment_id' => 'required_if:payment_type,investment|exists:investments,id',
        ]);

        try {
            $user = Auth::user();
            $amountUsd = floatval($request->amount);
            $paymentMethod = $request->payment_method;
            $paymentType = $request->payment_type;

            // ==========================================
            // PROTEÇÃO 1: Rate Limiting por Usuário
            // ==========================================
            $cacheKey = "payment_request:{$user->id}";
            $lastRequest = cache($cacheKey);
            
            if ($lastRequest) {
                $secondsSinceLastRequest = now()->diffInSeconds($lastRequest);
                
                if ($secondsSinceLastRequest < 30) { // 30 segundos entre requisições
                    $waitTime = 30 - $secondsSinceLastRequest;
                    
                    Log::warning('Rate limit atingido para criação de pagamento', [
                        'user_id' => $user->id,
                        'wait_time' => $waitTime,
                        'ip' => $request->ip(),
                    ]);
                    
                    return back()->with('error', "Please wait {$waitTime} seconds before creating another payment.");
                }
            }
            
            // Registra esta requisição
            cache([$cacheKey => now()], now()->addMinutes(1));

            // ==========================================
            // PROTEÇÃO 2: Verificar Pagamentos Pendentes
            // ==========================================
            if ($paymentType === 'investment' && $request->investment_id) {
                $investment = Investment::findOrFail($request->investment_id);
                
                // Verifica ownership
                if ($investment->user_id !== $user->id) {
                    abort(403, 'Unauthorized action.');
                }
                
                // Verifica se já existe pagamento pendente
                $existingPayment = Deposit::where('payment_type', 'investment')
                    ->where('reference_id', $investment->id)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->first();
                
                if ($existingPayment) {
                    Log::info('Redirecionando para pagamento pendente existente', [
                        'user_id' => $user->id,
                        'deposit_id' => $existingPayment->id,
                    ]);
                    
                    return redirect()->route('payment.show', $existingPayment->transaction_id)
                        ->with('info', 'You already have a pending payment for this investment.');
                }
                
                // Verifica se investimento já foi pago
                if ($investment->payment_status === 'paid') {
                    return back()->with('error', 'This investment has already been paid.');
                }
            }

            // ==========================================
            // PROTEÇÃO 3: Limitar Pagamentos Pendentes Totais
            // ==========================================
            $pendingPaymentsCount = Deposit::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->count();
            
            if ($pendingPaymentsCount >= 3) {
                Log::warning('Usuário atingiu limite de pagamentos pendentes', [
                    'user_id' => $user->id,
                    'pending_count' => $pendingPaymentsCount,
                ]);
                
                return back()->with('error', 'You have too many pending payments. Please complete or cancel them before creating new ones.');
            }

            DB::beginTransaction();

            // Cria o registro de depósito
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'gateway' => $paymentMethod === 'pix' ? 'startcash' : 'nowpayments',
                'amount_usd' => $amountUsd,
                'status' => 'pending',
                'payment_type' => $paymentType,
                'reference_id' => $request->investment_id,
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // ==========================================
            // PROTEÇÃO 4: Timeout nas Chamadas à API
            // ==========================================
            try {
                // Processa de acordo com o método escolhido
                if ($paymentMethod === 'pix') {
                    $result = $this->processPix($deposit);
                } else {
                    $result = $this->processCrypto($deposit);
                }

                if (!$result['success']) {
                    DB::rollBack();
                    
                    // Remove da cache para permitir nova tentativa
                    cache()->forget($cacheKey);
                    
                    return back()->with('error', $result['message']);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                
                Log::error('Erro ao processar pagamento via gateway', [
                    'user_id' => $user->id,
                    'deposit_id' => $deposit->id,
                    'error' => $e->getMessage(),
                ]);
                
                // Remove da cache para permitir nova tentativa
                cache()->forget($cacheKey);
                
                return back()->with('error', 'Payment gateway temporarily unavailable. Please try again in a few moments.');
            }

            // Se for investimento, atualiza o payment_data
            if ($paymentType === 'investment' && $request->investment_id) {
                $investment = Investment::findOrFail($request->investment_id);
                $investment->update([
                    'payment_transaction_id' => $deposit->transaction_id,
                    'payment_data' => $result,
                ]);
            }

            DB::commit();

            Log::info('Pagamento criado com sucesso', [
                'user_id' => $user->id,
                'deposit_id' => $deposit->id,
                'payment_type' => $paymentType,
                'method' => $paymentMethod,
            ]);

            return redirect()->route('payment.show', $deposit->transaction_id)
                ->with('success', $paymentType === 'deposit' 
                    ? 'Deposit created successfully!' 
                    : 'Investment payment created! Complete the payment to activate your bot.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar pagamento', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An error occurred while processing your payment. Please try again.');
        }
    }

    /**
     * Processa depósito via PIX
     */
    private function processPix(Deposit $deposit): array
    {
        $pixService = new StartCashPixService();
        return $pixService->createCharge($deposit);
    }

    /**
     * Processa depósito via Crypto (USDT BEP20)
     */
    private function processCrypto(Deposit $deposit): array
    {
        $cryptoService = new NowPaymentsService();
        return $cryptoService->createPayment($deposit);
    }

    /**
     * Exibe a página de pagamento unificada
     */
    public function show($transactionId)
    {
        $deposit = Deposit::where('transaction_id', $transactionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Verifica se expirou
        if ($deposit->isExpired()) {
            $deposit->markAsExpired();
        }

        // Se for investimento, carrega os dados
        $investment = null;
        if ($deposit->payment_type === 'investment' && $deposit->reference_id) {
            $investment = Investment::with('investmentPlan')->find($deposit->reference_id);
        }

        return view('payments.show', compact('deposit', 'investment'));
    }

    /**
     * Cancela um pagamento pendente
     */
    public function cancel($transactionId)
    {
        $deposit = Deposit::where('transaction_id', $transactionId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $deposit->update(['status' => 'cancelled']);

            // Se for investimento, cancela o investimento também
            if ($deposit->payment_type === 'investment' && $deposit->reference_id) {
                Investment::where('id', $deposit->reference_id)
                    ->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);
            }

            DB::commit();

            return redirect()->route($deposit->payment_type === 'investment' 
                ? 'investments.plans.index' 
                : 'deposit.index')
                ->with('success', 'Payment successfully cancelled.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error cancelling payment.');
        }
    }
}