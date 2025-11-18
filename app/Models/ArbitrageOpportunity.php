<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArbitrageOpportunity extends Model
{
     protected $fillable = [
        'bot_instance_id', 'base_currency',
        'intermediate_currency', 'quote_currency',
        'profit_percentage', 'estimated_profit',
        'prices', 'status', 'detected_at', 'executed_at',
    ];
    protected $casts = [
        'profit_percentage' => 'decimal:6',
        'estimated_profit' => 'decimal:8',
        'prices' => 'array',
        'detected_at' => 'datetime',
        'executed_at' => 'datetime',
    ];
    public function botInstance()
    {
        return $this->belongsTo(BotInstance::class);
    }
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
}
