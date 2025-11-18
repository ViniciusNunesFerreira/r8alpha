<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ReferralRegisterController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ReferralNetworkController;



Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');



Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard do Investidor
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    // Investimentos
    Route::prefix('investments')->name('investments.')->group(function () {
        Route::get('/', [InvestmentController::class, 'index'])
            ->name('index');
        Route::post('/', [InvestmentController::class, 'store'])
            ->name('store');
        Route::get('/{investment}', [InvestmentController::class, 'show'])
            ->name('show');
        Route::delete('/{investment}', [InvestmentController::class, 'cancel'])
            ->name('cancel');
    });

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
