<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Services\NowPaymentsService;
use App\Services\StartCashPixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    /**
     * Exibe a página de depósito
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallets()->where('type', 'deposit')->first();
        
        // Busca depósitos do usuário
        $deposits = Deposit::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Configurações
        $minDepositUsd = config('payment.deposit_limits.min_usd');
        $maxDepositUsd = config('payment.deposit_limits.max_usd');
        $usdToBrlRate = config('payment.usd_to_brl_rate');
        
        return view('payments.deposit.index', compact(
            'deposits',
            'minDepositUsd',
            'maxDepositUsd',
            'usdToBrlRate',
            'wallet'
        ));
    }

    /**
     * Processa a criação de um novo depósito
     */
    public function create(Request $request)
    {
        $minUsd = config('payment.deposit_limits.min_usd');
        
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:' . config('payment.deposit_limits.min_usd'),
                function ($attribute, $value, $fail) {
                    $max = config('payment.deposit_limits.max_usd');
                    if ($max && $value > $max) {
                        $fail("O valor máximo de depósito é $" . number_format($max, 2, '.', ','));
                    }
                },
            ],
            'payment_method' => 'required|in:pix,crypto',
        ], [
            'amount.required' => 'The deposit amount is mandatory.',
            'amount.numeric' => 'The value must be a valid number.',
            'amount.min' => "The minimum deposit amount is $" . number_format($minUsd, 2, '.', ','),
            'payment_method.required' => 'Select a payment method..',
            'payment_method.in' => 'Invalid payment method.',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $amountUsd = floatval($request->amount);
            $paymentMethod = $request->payment_method;

            // Cria o registro de depósito
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'gateway' => $paymentMethod === 'pix' ? 'startcash' : 'nowpayments',
                'amount_usd' => $amountUsd,
                'status' => 'pending',
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Processa de acordo com o método escolhido
            if ($paymentMethod === 'pix') {
                $result = $this->processPix($deposit);
            } else {
                $result = $this->processCrypto($deposit);
            }

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            DB::commit();

            return redirect()->route('deposit.show', $deposit->transaction_id)
                ->with('success', 'Deposit created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar depósito', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'An error occurred while processing your deposit. Please try again.');
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
     * Exibe detalhes de um depósito
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

        return view('payments.deposit.show', compact('deposit'));
    }

    /**
     * Verifica o status de um depósito
     */
    public function checkStatus($transactionId)
    {
        $deposit = Deposit::where('transaction_id', $transactionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Atualiza status se necessário
        if ($deposit->isPending() && !$deposit->isExpired()) {
            if ($deposit->payment_method === 'pix') {
                $pixService = new StartCashPixService();
                $status = $pixService->checkPaymentStatus($deposit->gateway_transaction_id);
                
                if ($status && isset($status['status']) && $status['status'] === 'paid') {
                    $deposit->markAsPaid();
                    $deposit->markAsConfirmed();
                }
            } else {
                $cryptoService = new NowPaymentsService();
                $status = $cryptoService->checkPaymentStatus($deposit->gateway_transaction_id);
                
                if ($status && in_array($status['payment_status'], ['confirmed', 'finished'])) {
                    $deposit->markAsConfirmed();
                }
            }
        }

        return response()->json([
            'status' => $deposit->status,
            'status_label' => $deposit->status_label,
            'is_completed' => $deposit->isCompleted(),
            'is_expired' => $deposit->isExpired(),
        ]);
    }

    /**
     * Cancela um depósito pendente
     */
    public function cancel($transactionId)
    {
        $deposit = Deposit::where('transaction_id', $transactionId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $deposit->update(['status' => 'cancelled']);

        return redirect()->route('deposit.index')
            ->with('success', 'Deposit successfully cancelled.');
    }

    /**
     * Webhook para PIX (StartCash)
     */
    public function webhookPix(Request $request)
    {
        try {
            $payload = $request->all();
            
            $pixService = new StartCashPixService();
            $result = $pixService->processWebhook($payload);

            return response()->json(['success' => $result], $result ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook PIX', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Webhook para NOWPayments (IPN)
     */
    public function webhookNowPayments(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('IPN NOWPayments recebido', ['payload' => $payload]);

            $cryptoService = new NowPaymentsService();
            $result = $cryptoService->processIPN($payload);

            return response()->json(['success' => $result], $result ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Erro ao processar IPN NOWPayments', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }
}