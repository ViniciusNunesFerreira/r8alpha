<?php

use App\Http\Controllers\Api\MarketDataController;
use App\Http\Controllers\Api\BotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    // Market Data
    Route::prefix('market')->group(function () {
        Route::get('/prices', [MarketDataController::class, 'prices']);
        Route::get('/ticker/{symbol}', [MarketDataController::class, 'ticker']);
        Route::get('/stats/{symbol}', [MarketDataController::class, 'stats']);
        Route::get('/opportunities', [MarketDataController::class, 'opportunities']);
    });
    // Bot Management API
    Route::prefix('bots')->group(function () {
        Route::get('/', [BotController::class, 'index']);
        Route::get('/{bot}', [BotController::class, 'show']);
        Route::post('/{bot}/start', [BotController::class, 'start']);
        Route::post('/{bot}/stop', [BotController::class, 'stop']);
        Route::get('/{bot}/stats', [BotController::class, 'stats']);
        Route::get('/{bot}/trades', [BotController::class, 'trades']);
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