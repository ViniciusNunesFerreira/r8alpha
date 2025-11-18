<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestBinanceConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binance:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Binance API connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Binance API connection...');
        $this->newLine();

        $this->info('1. Testing Exchange Info...');
        $exchangeInfo = $binanceService->getExchangeInfo();
        if ($exchangeInfo) {
            $this->info("Connected! Exchange: {$exchangeInfo['timezone']}");
        } else {
            $this->error('Failed to get exchange info');
            return Command::FAILURE;
        }

        // Teste 2: Ticker Price
        $this->info('2. Testing Ticker Price (BTCUSDT)...');
        $price = $binanceService->getTickerPrice('BTCUSDT');
        
        if ($price) {
            $this->info("BTC Price: $" . number_format($price['price'], 2));
        } else {
            $this->error('Failed to get ticker price');
            return Command::FAILURE;
        }

         // Teste 3: 24hr Stats
        $this->info('3. Testing 24hr Statistics...');
        $stats = $binanceService->get24hrStats('ETHUSDT');
        if ($stats) {
            $this->info("ETH 24h Change: {$stats['priceChangePercent']}%");
        } else {
            $this->error('   Failed to get 24hr stats');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('All tests passed! Binance API is working correctly.');

         return Command::SUCCESS;
    }
}
