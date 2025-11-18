<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ArbitrageOpportunity;

class OpportunityDetected
{
   use Dispatchable, InteractsWithSockets, SerializesModels;

    public $opportunity;
    public $userId;

    public function __construct(ArbitrageOpportunity $opportunity)
    {
        $this->opportunity = $opportunity;
        $this->userId = $opportunity->botInstance->user_id;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->userId),
            new PrivateChannel('bot.' . $this->opportunity->bot_instance_id),
        ];
    }

    public function broadcastAs()
    {
        return 'OpportunityDetected';
    }

    public function broadcastWith()
    {
        return [
            'opportunity_id' => $this->opportunity->id,
            'bot_instance_id' => $this->opportunity->bot_instance_id,
            'profit_percentage' => $this->opportunity->profit_percentage,
            'estimated_profit' => $this->opportunity->estimated_profit,
            'base_currency' => $this->opportunity->base_currency,
            'intermediate_currency' => $this->opportunity->intermediate_currency,
            'quote_currency' => $this->opportunity->quote_currency,
        ];
    }
}
