<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeExecuted
{
   use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trade;
    public $userId;
    public $profit;

    public function __construct($trade, $profit = 0)
    {
        $this->trade = $trade;
        $this->profit = $profit;
        $this->userId = $trade->botInstance->user_id;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->userId),
            new PrivateChannel('bot.' . $this->trade->bot_instance_id),
        ];
    }

    public function broadcastAs()
    {
        return 'TradeExecuted';
    }

    public function broadcastWith()
    {
        return [
            'trade_id' => $this->trade->id,
            'bot_instance_id' => $this->trade->bot_instance_id,
            'pair' => $this->trade->pair,
            'side' => $this->trade->side,
            'amount' => $this->trade->amount,
            'price' => $this->trade->price,
            'total' => $this->trade->total,
            'profit' => $this->profit,
        ];
    }
}
