<?php

namespace App\Livewire;

use App\Models\BotInstance;
use Livewire\Component;

class ActiveBotsList extends Component
{
    public $bots = [];
    public $filter = 'all'; // all, active, inactive
    public $sortBy = 'profit'; // profit, trades, success_rate

    protected $listeners = [];

    public function mount()
    {
        $userId = auth()->user()->id;
        $this->listeners["echo-private:user.{$userId},BotStatusChanged"] = 'handleBotStatusChange';
        $this->listeners["echo-private:user.{$userId},BotUpdated"] = 'refreshBots';

        $this->loadBots();
    }

    public function loadBots()
    {
        $query = BotInstance::with(['investment.investmentPlan', 'user'])
            ->where('user_id', auth()->id());

        // Apply filters
        if ($this->filter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filter === 'inactive') {
            $query->where('is_active', false);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'profit':
                $query->orderByDesc('total_profit');
                break;
            case 'trades':
                $query->orderByDesc('total_trades');
                break;
            case 'success_rate':
                $query->orderByRaw('CASE WHEN total_trades > 0 THEN (successful_trades / total_trades) ELSE 0 END DESC');
                break;
        }

        $this->bots = $query->get()->map(function ($bot) {
            return [
                'id' => $bot->id,
                'instance_id' => $bot->instance_id,
                'is_active' => $bot->is_active,
                'total_trades' => $bot->total_trades,
                'successful_trades' => $bot->successful_trades,
                'total_profit' => $bot->total_profit,
                'success_rate' => $bot->success_rate,
                'last_trade_at' => $bot->last_trade_at,
                'investment' => [
                    'amount' => $bot->investment->amount,
                    'current_balance' => $bot->investment->current_balance,
                    'plan_name' => $bot->investment->investmentPlan->name,
                ],
                'config' => $bot->config,
            ];
        })->toArray();
    }

    public function toggleBot($botId)
    {
        $bot = BotInstance::findOrFail($botId);
        
        if ($bot->user_id !== auth()->id()) {
            $this->emit('notification', [
                'type' => 'error',
                'message' => 'Unauthorized action.'
            ]);
            return;
        }

        $bot->update(['is_active' => !$bot->is_active]);
        
        $this->loadBots();
        
        $this->emit('notification', [
            'type' => 'success',
            'message' => $bot->is_active ? 'Bot activated successfully!' : 'Bot deactivated.'
        ]);

        // Ativar Broadcast event
       // broadcast(new \App\Events\BotStatusChanged($bot))->toOthers();
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->loadBots();
    }

    public function setSorting($sortBy)
    {
        $this->sortBy = $sortBy;
        $this->loadBots();
    }

    public function refreshBots()
    {
        $this->loadBots();
    }

    public function handleBotStatusChange($data)
    {
        $this->loadBots();
    }

    public function render()
    {
        return view('livewire.active-bots-list');
    }
}
