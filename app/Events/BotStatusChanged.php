<?php

namespace App\Events;

use App\Models\BotInstance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BotStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bot;

    public function __construct(BotInstance $bot)
    {
        $this->bot = $bot;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->bot->user_id),
            new PrivateChannel('bot.' . $this->bot->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'BotStatusChanged';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->bot->id,
            'instance_id' => $this->bot->instance_id,
            'is_active' => $this->bot->is_active,
            'total_trades' => $this->bot->total_trades,
            'successful_trades' => $this->bot->successful_trades,
            'total_profit' => (float) $this->bot->total_profit,
            'success_rate' => $this->bot->success_rate,
            'last_trade_at' => $this->bot->last_trade_at?->toIso8601String(),
        ];
    }
}