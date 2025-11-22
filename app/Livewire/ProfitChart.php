<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitChart extends Component
{
    public $chartData = [];
    public $period = '7d'; // 24h, 7d, 30d


    public function mount()
    {      
        $userId = auth()->user()->id;
        $this->listeners["echo-private:user.{$userId},TradeExecuted"] = 'refreshChart';
        $this->loadChartData();

    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->loadChartData();
    }

    public function loadChartData()
    {
        $userId = auth()->user()->id;
        
        switch ($this->period) {
            case '24h':
                $data = $this->get24HoursData($userId);
                break;
            case '30d':
                $data = $this->get30DaysData($userId);
                break;
            case '7d':
            default:
                $data = $this->get7DaysData($userId);
                break;
        }

        $this->chartData = $data;
    }

    protected function get24HoursData($userId)
    {
        $trades = DB::table('trades')
            ->join('bot_instances', 'trades.bot_instance_id', '=', 'bot_instances.id')
            ->where('bot_instances.user_id', $userId)
            ->where('trades.created_at', '>=', now()->subHours(24))
            ->where('trades.status', 'completed')
            ->select(
                DB::raw('HOUR(trades.created_at) as hour'),
                DB::raw('SUM(trades.profit) as total_profit'),
                DB::raw('COUNT(*) as trade_count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $data = [];
        $cumulativeProfit = 0;

        for ($i = 0; $i < 24; $i++) {
            $hour = now()->subHours(23 - $i)->format('H:00');
            $labels[] = $hour;
            
            $hourData = $trades->firstWhere('hour', now()->subHours(23 - $i)->format('H'));
            $profit = $hourData ? (float) $hourData->total_profit : 0;
            $cumulativeProfit += $profit;
            
            $data[] = round($cumulativeProfit, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    protected function get7DaysData($userId)
    {
        $trades = DB::table('trades')
            ->join('bot_instances', 'trades.bot_instance_id', '=', 'bot_instances.id')
            ->where('bot_instances.user_id', $userId)
            ->where('trades.created_at', '>=', now()->subDays(7))
            ->where('trades.status', 'completed')
            ->select(
                DB::raw('DATE(trades.created_at) as date'),
                DB::raw('SUM(trades.profit) as total_profit')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        $cumulativeProfit = 0;

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            
            $dayData = $trades->firstWhere('date', $date->format('Y-m-d'));
            $profit = $dayData ? (float) $dayData->total_profit : 0;
            $cumulativeProfit += $profit;
            
            $data[] = round($cumulativeProfit, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    protected function get30DaysData($userId)
    {
        $trades = DB::table('trades')
            ->join('bot_instances', 'trades.bot_instance_id', '=', 'bot_instances.id')
            ->where('bot_instances.user_id', $userId)
            ->where('trades.created_at', '>=', now()->subDays(30))
            ->where('trades.status', 'completed')
            ->select(
                DB::raw('DATE(trades.created_at) as date'),
                DB::raw('SUM(trades.profit) as total_profit')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        $cumulativeProfit = 0;

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            
            $dayData = $trades->firstWhere('date', $date->format('Y-m-d'));
            $profit = $dayData ? (float) $dayData->total_profit : 0;
            $cumulativeProfit += $profit;
            
            $data[] = round($cumulativeProfit, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function refreshChart()
    {
        $this->loadChartData();
        $this->dispatch('refreshChart');
    }

    public function render()
    {
        return view('livewire.profit-chart');
    }
}