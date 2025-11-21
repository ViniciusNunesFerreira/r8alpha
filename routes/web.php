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

    Route::prefix('investments/plans')->name('investments.plans.')->group(function () {
        Route::get('/', [InvestmentPlansController::class, 'index'])
            ->name('index');
        Route::get('/{plan}', [InvestmentPlansController::class, 'show'])
            ->name('show');
        Route::post('/{plan}/subscribe', [InvestmentPlansController::class, 'subscribe'])
            ->name('subscribe');
    });

    // Payment
    Route::get('/investments/{investment}/payment', [InvestmentPlansController::class, 'payment'])
        ->name('investments.payment');
    
    // Check payment status (AJAX)
    Route::get('/investments/{investment}/check-payment', function($investment) {
        $investment = Investment::findOrFail($investment);
        return response()->json([
            'status' => $investment->payment_status
        ]);
    })->name('investments.check-payment');


    //Deposits
    Route::prefix('deposit')->name('deposit.')->group(function () {

        // Página principal de depósitos
        Route::get('/', [DepositController::class, 'index'])->name('index');
        // Criar novo depósito
        Route::post('/', [DepositController::class, 'create'])->name('create');
        // Ver detalhes de um depósito
        Route::get('/{transactionId}', [DepositController::class, 'show'])->name('show');
        
        
        // Cancelar depósito
        Route::post('/{transactionId}/cancel', [DepositController::class, 'cancel'])
            ->name('cancel');

        /**
         * Endpoint Long Polling
         * Aguarda até 50 segundos por uma mudança de status
         * Reduz significativamente o número de requisições comparado ao polling tradicional
         * Recomendado para produção com muitos usuários
         */
        Route::get('/{transaction_id}/check-status-long', [DepositStatusController::class, 'checkStatusLongPolling'])
            ->name('check-status-long');

    });


    Route::get('referral-network', [ReferralNetworkController::class, 'index'])->name('referral.network');


});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Rota de indicação pública: /ref/username
Route::get('/ref/{username}', [ReferralRegisterController::class, 'show'])->name('register.ref');



require __DIR__.'/auth.php';
