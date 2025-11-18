<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
     protected $fillable = [
        'bot_instance_id', 'arbitrage_opportunity_id',
        'trade_sequence', 'pair', 'side', 'amount',
        'price', 'total', 'fee', 'status',
    ];
    protected $casts = [
        'amount' => 'decimal:8',
        'price' => 'decimal:8',
        'total' => 'decimal:8',
        'fee' => 'decimal:8',
    ];
    public function botInstance()
    {
        return $this->belongsTo(BotInstance::class);
    }
    public function arbitrageOpportunity()
    {
        return $this->belongsTo(ArbitrageOpportunity::class);
    }
}
