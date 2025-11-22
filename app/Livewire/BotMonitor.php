<?php

namespace App\Livewire;

use App\Models\BotInstance;
use App\Models\ArbitrageOpportunity;
use App\Models\Trade;
use App\Jobs\ScanArbitrageOpportunities;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BotMonitor extends Component
{
    public BotInstance $bot;
    public $recentOpportunities = [];
    public $recentTrades = [];
    public $performanceData = [];
    public $stats = [
        'total_profit' => 0,
        'success_rate' => 0,
        'total_trades' => 0,
        'opportunities_today' => 0,
        'avg_profit_per_trade' => 0,
    ];

    protected $listeners = ['botUpdated' => '$refresh'];

    public function mount($botId)
    {
        $this->bot = BotInstance::with(['investment', 'user'])
            ->where('id', $botId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->loadBotData();
    }

    public function loadBotData()
    {
        // Refresh bot instance
        $this->bot->refresh();

        // Load recent opportunities (last 10, from last 5 minutes)
        $this->recentOpportunities = ArbitrageOpportunity::where('bot_instance_id', $this->bot->id)
            ->where('detected_at', '>=', now()->subMinutes(5))
            ->orderBy('detected_at', 'desc')
            ->limit(10)
            ->get();

        // Load recent trades (last 20)
        $this->recentTrades = Trade::where('bot_instance_id', $this->bot->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Calculate stats
        $this->calculateStats();

        // Load performance data for chart (last 24 hours)
        $this->loadPerformanceData();

        // Dispatch event for chart update
        $this->dispatch('botUpdated', [
            'profit' => $this->bot->total_profit,
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    protected function calculateStats()
    {
        $this->stats['total_profit'] = $this->bot->total_profit ?? 0;
        $this->stats['success_rate'] = $this->bot->success_rate ?? 0;
        $this->stats['total_trades'] = $this->bot->total_trades ?? 0;

        // Opportunities detected today
        $this->stats['opportunities_today'] = ArbitrageOpportunity::where('bot_instance_id', $this->bot->id)
            ->whereDate('detected_at', today())
            ->count();

        // Average profit per trade
        if ($this->stats['total_trades'] > 0) {
            $this->stats['avg_profit_per_trade'] = $this->stats['total_profit'] / $this->stats['total_trades'];
        } else {
            $this->stats['avg_profit_per_trade'] = 0;
        }
    }

    protected function loadPerformanceData()
    {
        // Get hourly profit data for the last 24 hours
        $trades = Trade::where('bot_instance_id', $this->bot->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->where('status', 'completed')
            ->orderBy('created_at', 'asc')
            ->get();

        $cumulativeProfit = 0;
        $hourlyData = [];

        foreach ($trades as $trade) {
            $hour = $trade->created_at->format('H:00');
            $profit = $trade->profit ?? 0;
            $cumulativeProfit += $profit;

            $hourlyData[$hour] = $cumulativeProfit;
        }

        // Format for chart
        $this->performanceData = [
            'labels' => array_keys($hourlyData),
            'data' => array_values($hourlyData),
        ];
    }

    public function toggleBot()
    {
        try {
            $previousStatus = $this->bot->is_active;
            $this->bot->is_active = !$this->bot->is_active;
            $this->bot->save();

            if ($this->bot->is_active) {
                // Se ativou, dispara job para escanear oportunidades
                ScanArbitrageOpportunities::dispatch($this->bot);
                
                Log::info('Bot activated via Livewire', [
                    'bot_id' => $this->bot->id,
                    'user_id' => $this->bot->user_id,
                    'instance_id' => $this->bot->instance_id
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Bot ativado com sucesso! Começando a escanear oportunidades...'
                ]);
            } else {
                Log::info('Bot deactivated via Livewire', [
                    'bot_id' => $this->bot->id,
                    'user_id' => $this->bot->user_id,
                    'instance_id' => $this->bot->instance_id
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Bot pausado com sucesso!'
                ]);
            }

            // Reload data after toggle
            $this->loadBotData();

        } catch (\Exception $e) {
            Log::error('Error toggling bot status via Livewire', [
                'bot_id' => $this->bot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao alterar status do bot: ' . $e->getMessage()
            ]);
        }
    }

    public function refreshMonitor()
    {
        $this->loadBotData();
        
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Dados atualizados!'
        ]);
    }

    public function getActiveOpportunitiesProperty()
    {
        return $this->recentOpportunities->where('status', 'detected')->count();
    }

    public function getExecutedOpportunitiesProperty()
    {
        return $this->recentOpportunities->where('status', 'executed')->count();
    }

    public function render()
    {
        return view('livewire.bot-monitor');
    }
}