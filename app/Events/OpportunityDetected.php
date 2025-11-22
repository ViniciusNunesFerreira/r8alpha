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
            'id' => $this->opportunity->id,
            'bot_instance_id' => $this->opportunity->bot_instance_id,
            'base_currency' => $this->opportunity->base_currency,
            'intermediate_currency' => $this->opportunity->intermediate_currency,
            'quote_currency' => $this->opportunity->quote_currency,
            'profit_percentage' => (float) $this->opportunity->profit_percentage,
            'estimated_profit' => (float) $this->opportunity->estimated_profit,
            'prices' => $this->opportunity->prices,
            'status' => $this->opportunity->status,
            'detected_at' => $this->opportunity->detected_at->toIso8601String(),
        ];
    }
}
