<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\BotInstance;

class BotStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $botInstance;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(BotInstance $botInstance)
    {
        $this->botInstance = $botInstance;
        $this->userId = $botInstance->user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

     public function broadcastAs()
    {
        return 'BotStatusChanged';
    }

    public function broadcastWith()
    {
        return [
            'bot_id' => $this->botInstance->id,
            'instance_id' => $this->botInstance->instance_id,
            'is_active' => $this->botInstance->is_active,
        ];
    }
}
