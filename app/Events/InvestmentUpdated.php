<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvestmentUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $investment;
    public $userId;

    public function __construct($investment)
    {
        $this->investment = $investment;
        $this->userId = $investment->user_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'InvestmentUpdated';
    }

    public function broadcastWith()
    {
        return [
            'investment_id' => $this->investment->id,
            'current_balance' => $this->investment->current_balance,
            'total_profit' => $this->investment->total_profit,
        ];
    }
}
