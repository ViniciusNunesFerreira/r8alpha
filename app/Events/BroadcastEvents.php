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

class OpportunityDetected implements ShouldBroadcast
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

class TradeExecuted implements ShouldBroadcast
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

class ProfitGenerated implements ShouldBroadcast
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

class InvestmentUpdated implements ShouldBroadcast
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