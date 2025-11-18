<?php

namespace App\Livewire;

 use App\Services\BinanceService;
 use Livewire\Component;
 use Illuminate\Support\Facades\Cache;

class MarketTicker extends Component
{
    public $symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'ADAUSDT', 'DOGEUSDT',  'SOLUSDT', 'XRPUSDT'];
    public $prices = [];
    public $loading = false;
    /**
     * Montagem do componente
     */
    public function mount()
    {
        $this->loadPrices();
    }
    /**
     * Carrega preços da Binance
     */
    public function loadPrices()
    {
        $this->loading = true;
        
        $binanceService = app(BinanceService::class);
        
        foreach ($this->symbols as $symbol) {
            $cacheKey = "market_ticker_{$symbol}";
            
            $data = Cache::remember($cacheKey, 3, function () use ($binanceService, $symbol) {
                return $binanceService->get24hrStats($symbol);
            });
            
            if ($data) {
                $this->prices[$symbol] = [
                    'symbol' => str_replace('USDT', '', $symbol),
                    'price' => number_format($data['lastPrice'], 2),
                    'change' => number_format($data['priceChangePercent'], 2),
                    'volume' => $this->formatVolume($data['volume']),
                    'high' => number_format($data['highPrice'], 2),
                    'low' => number_format($data['lowPrice'], 2),
                ];
            }
        }
        
        $this->loading = false;
    }
    /**
     * Formata volume para exibição
     */
    protected function formatVolume($volume)
    {
        $volume = (float) $volume;
        
        if ($volume >= 1000000000) {
            return number_format($volume / 1000000000, 2) . 'B';
        } elseif ($volume >= 1000000) {
            return number_format($volume / 1000000, 2) . 'M';
        } elseif ($volume >= 1000) {
            return number_format($volume / 1000, 2) . 'K';
        }
        
        return number_format($volume, 2);
    }

    public function render()
    {
        return view('livewire.market-ticker');
    }
}
