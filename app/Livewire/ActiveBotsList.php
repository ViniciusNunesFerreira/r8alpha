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
            ->where('user_id', auth()->user()->id);

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

        // Keep as collection of objects for route compatibility
        $this->bots = $query->get();
    }

    public function toggleBot($botId)
    {
        $bot = BotInstance::findOrFail($botId);
        
        if ($bot->user_id !== auth()->user()->id) {
            $this->dispatch('notification', 
                type: 'error',
                message: 'Unauthorized action.'
            );
            return;
        }

        $newStatus = !$bot->is_active;
        $bot->update(['is_active' => $newStatus]);
        
        $this->loadBots();
        
        $this->dispatch('notification',
            type: 'success',
            message: $newStatus ? 'Bot activated successfully!' : 'Bot deactivated.'
        );

        // Broadcast event
        broadcast(new \App\Events\BotStatusChanged($bot))->toOthers();
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