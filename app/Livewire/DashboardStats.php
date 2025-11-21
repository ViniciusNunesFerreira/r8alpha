<?php

namespace App\Livewire;

use App\Models\Investment;
use App\Models\BotInstance;
use App\Models\Trade;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class DashboardStats extends Component
{
    public $stats = [];
    public $totalCommission = 0;
    
    // Poll a cada 10 segundos para atualizar
    protected $listeners = [
        'refreshStats' => '$refresh',
       // 'echo-private:user.{userId},InvestmentUpdated' => 'handleInvestmentUpdate',
       // 'echo-private:user.{userId},TradeExecuted' => 'handleTradeExecuted',
    ];

    public function mount()
    {
        $userId = auth()->user()->id;
        
        // 2. Definir os listeners dinamicamente
        $this->listeners["echo-private:user.{$userId},InvestmentUpdated"] = 'handleInvestmentUpdate';
        $this->listeners["echo-private:user.{$userId},TradeExecuted"] = 'handleTradeExecuted';

        $this->totalCommission = Auth::user()
                                    ->referralCommissions()
                                    ->sum('amount');

        $this->loadStats();
    }

    public function loadStats()
    {
        $userId = auth()->user()->id;
        
        // Cache por 5 segundos para evitar queries excessivas
        $this->stats = Cache::remember("dashboard_stats_{$userId}", 5, function () use ($userId) {
            $investments = Investment::where('user_id', $userId)->get();
            $activeInvestments = $investments->where('status', 'active');
            
            // Total investido (apenas investimentos ativos)
            $totalInvested = $activeInvestments->sum('amount');
            
            // Saldo atual (investimento + lucros)
            $currentBalance = $activeInvestments->sum('current_balance');
            
            // Total de lucros
            $totalProfit = $activeInvestments->sum('total_profit');
            
            // Percentual de lucro
            $profitPercentage = $totalInvested > 0 
                ? (($currentBalance - $totalInvested) / $totalInvested) * 100 
                : 0;
            
            // Robôs ativos
            $activeBots = BotInstance::where('user_id', $userId)
                ->where('is_active', true)
                ->count();
            
            // Total de robôs
            $totalBots = BotInstance::where('user_id', $userId)->count();
            
            // Trades hoje
            $tradesToday = Trade::whereHas('botInstance', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereDate('created_at', today())
            ->count();
            
            // Lucro hoje
            $profitToday = Trade::whereHas('botInstance', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereDate('created_at', today())
            ->sum('total');
            
            // Taxa de sucesso média
            $botInstances = BotInstance::where('user_id', $userId)->get();
            $averageSuccessRate = $botInstances->avg('success_rate') ?? 0;
            
            // Melhor performance
            $bestBot = $botInstances->sortByDesc('total_profit')->first();
            
            return [
                'total_invested' => $totalInvested,
                'current_balance' => $currentBalance,
                'total_profit' => $totalProfit,
                'profit_percentage' => $profitPercentage,
                'active_investments' => $activeInvestments->count(),
                'total_investments' => $investments->count(),
                'active_bots' => $activeBots,
                'total_bots' => $totalBots,
                'trades_today' => $tradesToday,
                'profit_today' => $profitToday,
                'average_success_rate' => $averageSuccessRate,
                'best_bot_profit' => $bestBot ? $bestBot->total_profit : 0,
                'wallet_balance' => auth()->user()->depositWallet->balance ?? 0,
            ];
        });
    }

    public function handleInvestmentUpdate($data)
    {
        $this->loadStats();
        $this->emit('notification', [
            'type' => 'success',
            'message' => 'Investment updated!'
        ]);
    }

    public function handleTradeExecuted($data)
    {
        $this->loadStats();
        $this->emit('notification', [
            'type' => 'success',
            'message' => 'New trade executed! Profit: $' . number_format($data['profit'] ?? 0, 2)
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
