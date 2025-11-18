<?php

namespace App\Livewire;

use App\Models\ArbitrageOpportunity;
use Livewire\Component;

class RecentOpportunitiesFeed extends Component
{
    public $opportunities = [];
    public $limit = 10;
    public $statusFilter = 'all'; // all, detected, executed

    protected $listeners = [];

    public function mount()
    {
        // 1. Obter o ID do usuário
        $userId = auth()->user()->id;
        $this->listeners["echo-private:user.{$userId},OpportunityDetected"] = 'handleNewOpportunity';

        $this->loadOpportunities();
    }

    public function loadOpportunities()
    {
        $userId = auth()->user()->id;
        
        $query = ArbitrageOpportunity::whereHas('botInstance', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with('botInstance')
        ->orderByDesc('detected_at');

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $this->opportunities = $query->take($this->limit)
            ->get()
            ->map(function($opp) {
                return [
                    'id' => $opp->id,
                    'base_currency' => $opp->base_currency,
                    'intermediate_currency' => $opp->intermediate_currency,
                    'quote_currency' => $opp->quote_currency,
                    'profit_percentage' => $opp->profit_percentage,
                    'estimated_profit' => $opp->estimated_profit,
                    'status' => $opp->status,
                    'detected_at' => $opp->detected_at,
                    'executed_at' => $opp->executed_at,
                    'prices' => $opp->prices,
                    'bot_instance_id' => $opp->botInstance->instance_id,
                ];
            })
            ->toArray();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->loadOpportunities();
    }

    public function handleNewOpportunity($data)
    {
        $this->loadOpportunities();
        
        $this->emit('notification', [
            'type' => 'success',
            'message' => 'New arbitrage opportunity detected: ' . number_format($data['profit_percentage'] ?? 0, 4) . '%'
        ]);
    }

    public function render()
    {
        return view('livewire.recent-opportunities-feed');
    }
}
