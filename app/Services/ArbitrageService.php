<?php

namespace App\Services;

use App\Models\BotInstance;
use App\Models\ArbitrageOpportunity;
use App\Models\Trade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Events\OpportunityDetected;
use App\Events\TradeExecuted;

 class ArbitrageService
 {

    protected $binanceService;
    protected $minProfitPercentage = 0.1; // Mínimo 0.5% de lucro
    protected $tradingFee = 0.001; // 0.1% por trade

    public function __construct(BinanceService $binanceService)
    {
        $this->binanceService = $binanceService;
    }


    /**
     * Busca oportunidades de arbitragem triangular
     * 
     * @param BotInstance $botInstance Instância do robô
     * @param array $baseCurrencies Moedas base para análise
     * @return array Array de oportunidades encontradas
     */
    public function findOpportunities(BotInstance $botInstance,  array $baseCurrencies = ['BTC', 'ETH', 'USDT', 'BNB']) 
    {
        $opportunities = [];
        foreach ($baseCurrencies as $baseCurrency) {
            // Encontra todos os triângulos possíveis
            $triangles = $this->findTriangularPaths($baseCurrency);
            
            foreach ($triangles as $triangle) {
                // Calcula se há oportunidade de lucro
                $opportunity = $this->calculateArbitrage($triangle);
                
                // Salva se for lucrativo
                if ($opportunity && 
                    $opportunity['profit_percentage'] > $this->minProfitPercentage) {
                    $opportunities[] = $this->saveOpportunity(
                        $botInstance, 
                        $opportunity
                    );
                }
            }
        }
        return $opportunities;
    }


     /**
     * Encontra todos os caminhos triangulares possíveis
     * 
     * Exemplo de triângulo:
     * USDT → BTC → ETH → USDT
     * 
     * @param string $baseCurrency Moeda inicial e final
     * @return array Array de triângulos possíveis
     */
    protected function findTriangularPaths(string $baseCurrency)
    {
        $pairs = $this->binanceService->getTradingPairs($baseCurrency);
        $triangles = [];
        // Par 1: BASE → INTERMEDIÁRIA
        foreach ($pairs as $pair1) {
            if ($pair1['quoteAsset'] !== $baseCurrency) continue;
            
            $intermediate = $pair1['baseAsset'];
            
            // Par 2: INTERMEDIÁRIA → COTAÇÃO
            foreach ($pairs as $pair2) {
                if ($pair2['quoteAsset'] !== $intermediate) continue;
                
                $quote = $pair2['baseAsset'];
                
                // Par 3: COTAÇÃO → BASE (fecha o triângulo)
                $pair3Symbol = $quote . $baseCurrency;
                
                $pair3 = $pairs->firstWhere('symbol', $pair3Symbol);
                
                if ($pair3 && $pair3['status'] === 'TRADING') {
                    $triangles[] = [
                        'base' => $baseCurrency,
                        'intermediate' => $intermediate,
                        'quote' => $quote,
                        'pair1' => $pair1['symbol'], // BASE/INTERMEDIATE
                        'pair2' => $pair2['symbol'], // INTERMEDIATE/QUOTE
                        'pair3' => $pair3['symbol'], // QUOTE/BASE
                    ];
                }
            }
        }
        return $triangles;
    }

    /**
     * Calcula se há oportunidade de arbitragem
     * 
     * Simula as 3 operações:
     * 1. Compra moeda intermediária
     * 2. Compra moeda de cotação
     * 3. Vende de volta para moeda base
     * 
     * @param array $triangle Dados do triângulo
     * @return array|null Oportunidade ou null se não for lucrativa
     */

    protected function calculateArbitrage(array $triangle)
    {
        // Obtém preços atuais dos 3 pares
        $price1 = $this->binanceService->getTickerPrice($triangle['pair1']);
        $price2 = $this->binanceService->getTickerPrice($triangle['pair2']);
        $price3 = $this->binanceService->getTickerPrice($triangle['pair3']);
        if (!$price1 || !$price2 || !$price3) {
            return null;
        }
        $p1 = (float) $price1['price'];
        $p2 = (float) $price2['price'];
        $p3 = (float) $price3['price'];
        // Simula com 1 unidade da moeda base
        $startAmount = 1;
        // OPERAÇÃO 1: Compra moeda intermediária
        // Quantidade = Valor / Preço - Taxa
        $amount1 = ($startAmount / $p1) * (1 - $this->tradingFee);
        // OPERAÇÃO 2: Compra moeda de cotação
        $amount2 = ($amount1 / $p2) * (1 - $this->tradingFee);
        // OPERAÇÃO 3: Vende de volta para moeda base
        $finalAmount = ($amount2 * $p3) * (1 - $this->tradingFee);
        // Calcula lucro
        $profit = $finalAmount - $startAmount;
        $profitPercentage = ($profit / $startAmount) * 100;
        // Retorna null se não for lucrativo
        if ($profitPercentage <= 0) {
            return null;
        }
        return [
            'base_currency' => $triangle['base'],
            'intermediate_currency' => $triangle['intermediate'],
            'quote_currency' => $triangle['quote'],
            'profit_percentage' => round($profitPercentage, 4),
            'prices' => [
                'pair1' => ['symbol' => $triangle['pair1'], 'price' => $p1],
                'pair2' => ['symbol' => $triangle['pair2'], 'price' => $p2],
                'pair3' => ['symbol' => $triangle['pair3'], 'price' => $p3],
            ],
            'path' => [
                $triangle['base'],
                $triangle['intermediate'],
                $triangle['quote'],
                $triangle['base']
            ]
        ];
    }

    /**
     * Salva oportunidade no banco de dados
     * 
     * @param BotInstance $botInstance
     * @param array $opportunity
     * @return ArbitrageOpportunity
     */
    protected function saveOpportunity( BotInstance $botInstance,   array $opportunity) 
    {
        $investment = $botInstance->investment;
        $estimatedProfit = ($investment->amount * $opportunity['profit_percentage']) / 100;
        $opp = ArbitrageOpportunity::create([
            'bot_instance_id' => $botInstance->id,
            'base_currency' => $opportunity['base_currency'],
            'intermediate_currency' => $opportunity['intermediate_currency'],
            'quote_currency' => $opportunity['quote_currency'],
            'profit_percentage' => $opportunity['profit_percentage'],
            'estimated_profit' => $estimatedProfit,
            'prices' => $opportunity['prices'],
            'status' => 'detected',
            'detected_at' => now(),
        ]);


        broadcast(new OpportunityDetected($opp))->toOthers();

        return $opp;
    }

    /**
     * Executa arbitragem (simulada para sinais apenas)
     * 
     * @param ArbitrageOpportunity $opportunity
     * @return array Resultado da execução
     */
    public function executeArbitrage(ArbitrageOpportunity $opportunity)
    {
        DB::beginTransaction();
        try {
            $botInstance = $opportunity->botInstance;
            $investment = $botInstance->investment;
            // Simula os três trades
            $trades = $this->simulateTrades($opportunity, $investment->amount);

            foreach($trades as $trade){
                broadcast(new TradeExecuted($trade, $opportunity->estimated_profit/3))->toOthers();
            }
            // Atualiza status da oportunidade
            $opportunity->update([
                'status' => 'executed',
                'executed_at' => now(),
            ]);
            // Atualiza estatísticas do robô
            $botInstance->increment('total_trades', 3);
            $botInstance->increment('successful_trades', 3);
            $botInstance->increment('total_profit', $opportunity->estimated_profit);
            $botInstance->update(['last_trade_at' => now()]);
            // Atualiza saldo do investimento
            $investment->increment('current_balance', $opportunity->estimated_profit);
            $investment->increment('total_profit', $opportunity->estimated_profit);
            DB::commit();
            return [
                'success' => true,
                'trades' => $trades,
                'profit' => $opportunity->estimated_profit,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Arbitrage Execution Error', [
                'opportunity_id' => $opportunity->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

     /**
     * Simula os três trades da arbitragem
     * 
     * @param ArbitrageOpportunity $opportunity
     * @param float $investmentAmount
     * @return array Array de trades criados
     */
    protected function simulateTrades(ArbitrageOpportunity $opportunity,  float $investmentAmount) 
    {
        $trades = [];
        $currentAmount = $investmentAmount;
        // TRADE 1: BASE → INTERMEDIÁRIA (COMPRA)
        $price1 = $opportunity->prices['pair1']['price'];
        $amount1 = $currentAmount / $price1;
        $fee1 = $this->binanceService->calculateFee($amount1);
        
        $trades[] = Trade::create([
            'bot_instance_id' => $opportunity->bot_instance_id,
            'arbitrage_opportunity_id' => $opportunity->id,
            'trade_sequence' => 'step_1',
            'pair' => $opportunity->prices['pair1']['symbol'],
            'side' => 'buy',
            'amount' => $amount1,
            'price' => $price1,
            'total' => $currentAmount,
            'fee' => $fee1,
            'status' => 'simulated',
        ]);
        $currentAmount = $amount1 - $fee1;
        // TRADE 2: INTERMEDIÁRIA → COTAÇÃO (COMPRA)
        $price2 = $opportunity->prices['pair2']['price'];
        $amount2 = $currentAmount / $price2;
        $fee2 = $this->binanceService->calculateFee($amount2);
        
        $trades[] = Trade::create([
            'bot_instance_id' => $opportunity->bot_instance_id,
            'arbitrage_opportunity_id' => $opportunity->id,
            'trade_sequence' => 'step_2',
            'pair' => $opportunity->prices['pair2']['symbol'],
            'side' => 'buy',
            'amount' => $amount2,
            'price' => $price2,
            'total' => $currentAmount,
            'fee' => $fee2,
            'status' => 'simulated',
        ]);
        $currentAmount = $amount2 - $fee2;
        // TRADE 3: COTAÇÃO → BASE (VENDA)
        $price3 = $opportunity->prices['pair3']['price'];
        $amount3 = $currentAmount * $price3;
        $fee3 = $this->binanceService->calculateFee($amount3);
        
        $trades[] = Trade::create([
            'bot_instance_id' => $opportunity->bot_instance_id,
            'arbitrage_opportunity_id' => $opportunity->id,
            'trade_sequence' => 'step_3',
            'pair' => $opportunity->prices['pair3']['symbol'],
            'side' => 'sell',
            'amount' => $currentAmount,
            'price' => $price3,
            'total' => $amount3,
            'fee' => $fee3,
            'status' => 'simulated',
        ]);
        return $trades;
    }


     /**
     * Obtém melhores oportunidades ativas
     * 
     * @param int $limit Quantidade de oportunidades
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBestOpportunities(int $limit = 10)
    {
        return ArbitrageOpportunity::where('status', 'detected')
            ->where('detected_at', '>', now()->subMinutes(5))
            ->orderByDesc('profit_percentage')
            ->limit($limit)
            ->get();
    }


 }