<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ReferralRegisterController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\BotController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ReferralNetworkController;
use App\Http\Controllers\InvestmentPlansController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DepositStatusController;
use App\Http\Controllers\PaymentController;

use App\Models\Investment;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard do Investidor
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Robôs de Trading
    Route::prefix('bots')->name('bots.')->group(function () {
        Route::get('/', [BotController::class, 'index'])
            ->name('index');
        Route::get('/{bot}', [BotController::class, 'show'])
            ->name('show');
        Route::post('/{bot}/toggle', [BotController::class, 'toggle'])
            ->name('toggle');
        Route::put('/{bot}/config', [BotController::class, 'updateConfig'])
            ->name('update-config');
        Route::get('/{bot}/export', [BotController::class, 'exportData'])
            ->name('export');
    });

    // Investment Plans
    Route::prefix('investments/plans')->name('investments.plans.')->group(function () {
        Route::get('/', [InvestmentPlansController::class, 'index'])
            ->name('index');
        Route::get('/{plan}', [InvestmentPlansController::class, 'show'])
            ->name('show');
        Route::post('/{plan}/subscribe', [InvestmentPlansController::class, 'subscribe'])
            ->name('subscribe');
    });

    // Investment Payment (escolha de método)
    Route::get('/investments/{investment}/payment', [InvestmentPlansController::class, 'payment'])
        ->name('investments.payment');

    // Deposits (Página inicial de depósitos)
    Route::prefix('deposit')->name('deposit.')->group(function () {
        Route::get('/', [DepositController::class, 'index'])->name('index');
        
        // Endpoint Long Polling para verificação de status
        Route::get('/{transaction_id}/check-status-long', [DepositStatusController::class, 'checkStatusLongPolling'])
            ->name('check-status-long');
    });

    // ============================================
    // SISTEMA UNIFICADO DE PAGAMENTOS
    // ============================================
    Route::prefix('payment')->name('payment.')->group(function () {
        // Criar pagamento (PIX ou Crypto) - RATE LIMITED
        Route::post('/create', [PaymentController::class, 'create'])
            ->middleware('throttle:10,1') // Máximo 10 requisições por minuto
            ->name('create');
        
        // Visualizar pagamento - unificado para ambos
        Route::get('/{transactionId}', [PaymentController::class, 'show'])
            ->name('show');
        
        // Cancelar pagamento - RATE LIMITED
        Route::post('/{transactionId}/cancel', [PaymentController::class, 'cancel'])
            ->middleware('throttle:5,1') // Máximo 5 cancelamentos por minuto
            ->name('cancel');
    });

    // Referral Network
    Route::get('referral-network', [ReferralNetworkController::class, 'index'])
        ->name('referral.network');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rota de indicação pública: /ref/username
Route::get('/ref/{username}', [ReferralRegisterController::class, 'show'])->name('register.ref');

// ============================================
// WEBHOOKS (Sem autenticação)
// ============================================
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // Webhook PIX (StartCash)
    Route::post('/pix', [DepositController::class, 'webhookPix'])
        ->name('pix');
    
    // Webhook Crypto (NOWPayments)
    Route::post('/nowpayments', [DepositController::class, 'webhookNowPayments'])
        ->name('nowpayments');
});

require __DIR__.'/auth.php';