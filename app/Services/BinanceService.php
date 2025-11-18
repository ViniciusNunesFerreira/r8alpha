<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BinanceService
{

    protected $baseUrl = 'https://api.binance.com';
    protected $apiKey;
    protected $apiSecret;

    public function __construct(){
        $this->apiKey = config('services.binance.api_key');
        $this->apiSecret = config('services.binance.api_secret');
    }


     public function getTickerPrice(string $symbol)
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/v3/ticker/price", [
                'symbol' => $symbol
            ]);
            if ($response->successful()) {
                return $response->json();
            }
            Log::error('Binance API Error', [
                'response' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Binance API Exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


    /**
     * Obtém o livro de ofertas (order book)
     * 
     * @param string $symbol Par de trading
     * @param int $limit Quantidade de níveis (5, 10, 20, 50, 100, 500, 1000, 5000)
     * @return array|null Dados do order book ou null
     */
    public function getOrderBook(string $symbol, int $limit = 100)
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/v3/depth", [
                'symbol' => $symbol,
                'limit' => $limit
            ]);
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Binance Order Book Exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


     /**
     * Obtém preços de todos os pares disponíveis
     * Cache de 1 segundo para evitar rate limit
     * 
     * @return \Illuminate\Support\Collection Collection de preços
     */
    public function getAllTickers()
    {
        $cacheKey = 'binance_all_tickers';
        
        return Cache::remember($cacheKey, 1, function () {
            try {
                $response = Http::get("{$this->baseUrl}/api/v3/ticker/price");
                if ($response->successful()) {
                    return collect($response->json());
                }
                return collect();
            } catch (\Exception $e) {
                Log::error('Binance All Tickers Exception', [
                    'error' => $e->getMessage()
                ]);
                return collect();
            }
        });
    }


    /**
     * Obtém estatísticas de 24 horas de um símbolo
     * 
     * @param string $symbol Par de trading
     * @return array|null Estatísticas ou null
     */

    public function get24hrStats(string $symbol)
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/v3/ticker/24hr", [
                'symbol' => $symbol
            ]);
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Binance 24hr Stats Exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


     /**
     * Obtém informações completas da exchange
     * Cache de 1 hora (dados raramente mudam)
     * 
     * @return array|null Informações da exchange
     */
    public function getExchangeInfo()
    {
        $cacheKey = 'binance_exchange_info';
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = Http::get("{$this->baseUrl}/api/v3/exchangeInfo");
                if ($response->successful()) {
                    return $response->json();
                }
                return null;
            } catch (\Exception $e) {
                Log::error('Binance Exchange Info Exception', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }


     /**
     * Obtém pares de trading disponíveis para uma moeda base
     * 
     * @param string $baseCurrency Moeda base (ex: BTC, ETH, USDT)
     * @return \Illuminate\Support\Collection Collection de pares
     */
    public function getTradingPairs(string $baseCurrency)
    {
        $exchangeInfo = $this->getExchangeInfo();
        
        if (!$exchangeInfo) {
            return collect();
        }
        return collect($exchangeInfo['symbols'])
            ->filter(function ($symbol) use ($baseCurrency) {
                return $symbol['status'] === 'TRADING' && 
                       ($symbol['baseAsset'] === $baseCurrency || 
                        $symbol['quoteAsset'] === $baseCurrency);
            });
    }

       /**
     * Calcula taxa de trading
     * Taxa padrão da Binance: 0.1%
     * 
     * @param float $amount Valor da operação
     * @param float $feeRate Taxa (padrão 0.1%)
     * @return float Valor da taxa
     */
    public function calculateFee(float $amount, float $feeRate = 0.001)
    {
        return $amount * $feeRate;
    }

     /**
     * Obtém dados históricos de candlestick
     * 
     * @param string $symbol Par de trading
     * @param string $interval Intervalo (1m, 5m, 15m, 1h, 4h, 1d, etc)
     * @param int $limit Quantidade de candles (máx: 1000)
     * @return array|null Dados históricos ou null
     */
    public function getKlines(string $symbol, string $interval = '1h', int $limit = 100)
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/v3/klines", [
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit
            ]);
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Binance Klines Exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }



}