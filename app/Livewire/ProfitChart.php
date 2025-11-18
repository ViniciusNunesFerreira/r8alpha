<?php

namespace App\Livewire;

use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Trade;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitChart extends Component
{
    public $chartData = [];
    public $period = 'week'; // week, month, year
    public $chartType = 'profit'; // profit, trades, performance
    public $hasData = false; // <-- Nova propriedade para controle da exibição

    protected $listeners = [
        'refreshStats' => '$refresh',
    ];

    public function mount()
    {
        // 1. Obter o ID do usuário
        $userId = auth()->user()->id;
        
        // 2. Definir os listeners dinamicamente, usando {$userId} para interpolação
        $this->listeners["echo-private:user.{$userId},ProfitGenerated"] = 'handleNewProfit';
        
        $this->loadChartData();
    }

    public function loadChartData()
    {
        $userId = auth()->user()->id;
        
        switch ($this->period) {
            case 'week':
                $this->chartData = $this->getWeeklyData($userId);
                break;
            case 'month':
                $this->chartData = $this->getMonthlyData($userId);
                break;
            case 'year':
                $this->chartData = $this->getYearlyData($userId);
                break;
        }

        // LÓGICA: Verifica se o primeiro dataset (Profit) tem dados significativos
        $data = collect($this->chartData['datasets'][0]['data'] ?? []);
        // Considera que há dados se houverem pontos e a soma total do lucro for maior que zero
        $this->hasData = $data->count() > 0 && $data->sum() > 0;
    }

    protected function getWeeklyData($userId)
    {
        $labels = [];
        $profitData = [];
        $tradesData = [];
        
        // Últimos 7 dias
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D');
            
            // Lucro diário
            $profit = Transaction::where('user_id', $userId)
                ->where('type', 'profit')
                ->whereDate('created_at', $date)
                ->sum('amount');
            $profitData[] = (float) $profit;
            
            // Trades diários
            $trades = Trade::whereHas('botInstance', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereDate('created_at', $date)
            ->count();
            $tradesData[] = $trades;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Daily Profit',
                    'data' => $profitData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Daily Trades',
                    'data' => $tradesData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'tension' => 0.4,
                    'fill' => false,
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    protected function getMonthlyData($userId)
    {
        $labels = [];
        $profitData = [];
        $tradesData = [];
        
        // Últimos 30 dias
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $profit = Transaction::where('user_id', $userId)
                ->where('type', 'profit')
                ->whereDate('created_at', $date)
                ->sum('amount');
            $profitData[] = (float) $profit;
            
            $trades = Trade::whereHas('botInstance', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereDate('created_at', $date)
            ->count();
            $tradesData[] = $trades;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Daily Profit',
                    'data' => $profitData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Daily Trades',
                    'data' => $tradesData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'tension' => 0.4,
                    'fill' => false,
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    protected function getYearlyData($userId)
    {
        $labels = [];
        $profitData = [];
        $tradesData = [];
        
        // Últimos 12 meses
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $profit = Transaction::where('user_id', $userId)
                ->where('type', 'profit')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            $profitData[] = (float) $profit;
            
            $trades = Trade::whereHas('botInstance', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->count();
            $tradesData[] = $trades;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Monthly Profit',
                    'data' => $profitData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Monthly Trades',
                    'data' => $tradesData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'tension' => 0.4,
                    'fill' => false,
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->loadChartData();
        $this->emit('chartUpdated');
    }

    public function handleNewProfit($data)
    {
        $this->loadChartData();
        $this->emit('chartUpdated');
    }

    public function render()
    {
        return view('livewire.profit-chart');
    }
}