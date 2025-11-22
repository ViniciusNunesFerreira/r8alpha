<?php

namespace App\Events;

use App\Models\Trade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeExecuted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trade;
    public $profit;

    public function __construct(Trade $trade, float $profit = 0)
    {
        $this->trade = $trade;
        $this->profit = $profit;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->trade->botInstance->user_id),
            new PrivateChannel('bot.' . $this->trade->bot_instance_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TradeExecuted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->trade->id,
            'bot_instance_id' => $this->trade->bot_instance_id,
            'pair' => $this->trade->pair,
            'side' => $this->trade->side,
            'amount' => (float) $this->trade->amount,
            'price' => (float) $this->trade->price,
            'total' => (float) $this->trade->total,
            'profit' => $this->profit,
            'status' => $this->trade->status,
            'trade_sequence' => $this->trade->trade_sequence,
            'executed_at' => $this->trade->created_at->toIso8601String(),
        ];
    }
}