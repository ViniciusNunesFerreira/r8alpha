<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\BinanceService;

class CryptoTicker extends Component
{
    // Lista de pares que queremos exibir, no formato exigido pela Binance (ex: 'BTCUSDT')
    public $symbols = [
        'BTCUSDT', 
        'ETHUSDT', 
        'BNBUSDT',
        'AVAXUSDT', 
        'SOLUSDT', 
        'XRPUSDT',
        'DOGEUSDT',
        'PEPEUSDT'
    ];
    
    // Armazenará os dados de cotação reais
    public $tickers = [];

    protected BinanceService $binanceService;

    public function boot(BinanceService $binanceService)
    {
        $this->binanceService = $binanceService;
    }

    // O método mount é executado apenas na primeira carga
    public function mount()
    {
        $this->fetchTickers();
    }

    // O método render é chamado após o mount e em cada atualização do poll
    public function render()
    {
        return view('livewire.crypto-ticker');
    }

    /**
     * Busca os dados de estatísticas de 24h para todos os símbolos.
     * Esta função será executada em cada ciclo do Livewire poll (a cada 10s)
     */
    public function fetchTickers()
    {
        $fetchedTickers = [];

        foreach ($this->symbols as $symbol) {
            $stats = $this->binanceService->get24hrStats($symbol);

            if ($stats && isset($stats['lastPrice']) && isset($stats['priceChangePercent'])) {
                $price = (float) $stats['lastPrice'];
                $percentChange = (float) $stats['priceChangePercent'];

                // Formatação para exibição
                $formattedPrice = number_format($price, $this->getDecimalPlaces($symbol), '.', ',');
                $formattedPercent = number_format(abs($percentChange), 2) . '%';
                $isPositive = $percentChange >= 0;

                $fetchedTickers[] = [
                    'symbol' => str_replace('USDT', '/USDT', $symbol), // Ex: BTC/USDT
                    'price' => $formattedPrice,
                    'percent' => $formattedPercent,
                    'is_positive' => $isPositive,
                    'raw_price' => $price, // Mantém o preço para detecção de mudança
                ];
            } else {
                // Caso a API falhe, usa um valor padrão
                $fetchedTickers[] = [
                    'symbol' => str_replace('USDT', '/USDT', $symbol),
                    'price' => 'N/A',
                    'percent' => 'N/A',
                    'is_positive' => true,
                    'raw_price' => 0,
                ];
            }
        }
        
        $this->tickers = $fetchedTickers;
    }

    /**
     * Determina a quantidade de casas decimais com base no símbolo.
     * Isso é uma simplificação para fins de exibição.
     */
    protected function getDecimalPlaces(string $symbol): int
    {
        switch ($symbol) {
            case 'BTCUSDT':
            case 'ETHUSDT':
            case 'BNBUSDT':
            case 'AVAXUSDT':
            case 'SOLUSDT':
                return 2; 
            case 'XRPUSDT':
                return 4;
            case 'DOGEUSDT':
                return 5;
            case 'PEPEUSDT':
                return 8;
            default:
                return 4; 
        }
    }
}