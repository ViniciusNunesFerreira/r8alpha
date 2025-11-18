<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfitGenerated
{
     use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $amount;
    public $investmentId;

    public function __construct($userId, $amount, $investmentId = null)
    {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->investmentId = $investmentId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'ProfitGenerated';
    }

    public function broadcastWith()
    {
        return [
            'amount' => $this->amount,
            'investment_id' => $this->investmentId,
        ];
    }
}
