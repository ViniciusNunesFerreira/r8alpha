<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepositController;

 /*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 */
 Route::middleware('auth:sanctum')->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    // Wallet API
    Route::prefix('wallet')->group(function () {
        Route::get('/balance', function (Request $request) {
            return $request->user()->wallet;
        });
        Route::get('/transactions', function (Request $request) {
            return $request->user()->wallet->transactions()->latest()->paginate(20);
        });
    });

 });
 // Webhooks públicos (com autenticação personalizada)
 Route::post('/webhook/binance', function (Request $request) {
 // Processar webhook da Binance
 })->middleware('webhook.signature');


 Route::middleware(['throttle:60,1'])->group(function () {
    // Webhook StartCash PIX
    Route::post('/webhook/pix', [DepositController::class, 'webhookPix'])
        ->name('webhook.pix');
    // Webhook NOWPayments
    Route::post('/webhook/nowpayments', [DepositController::class, 'webhookNowPayments'])
        ->name('webhook.nowpayments');
});