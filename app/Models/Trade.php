<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = [
        'bot_instance_id',
        'arbitrage_opportunity_id',
        'pair',
        'side',
        'amount',
        'price',
        'total',
        'profit',
        'status',
        'exchange_order_id',
        'fees',
        'executed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'price' => 'decimal:8',
        'total' => 'decimal:8',
        'profit' => 'decimal:8',
        'fees' => 'decimal:8',
        'executed_at' => 'datetime',
    ];

    /**
     * Relacionamento com BotInstance
     */
    public function botInstance(): BelongsTo
    {
        return $this->belongsTo(BotInstance::class);
    }

    /**
     * Relacionamento com ArbitrageOpportunity
     */
    public function arbitrageOpportunity(): BelongsTo
    {
        return $this->belongsTo(ArbitrageOpportunity::class);
    }

    /**
     * Scope para trades completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para trades de compra
     */
    public function scopeBuy($query)
    {
        return $query->where('side', 'buy');
    }

    /**
     * Scope para trades de venda
     */
    public function scopeSell($query)
    {
        return $query->where('side', 'sell');
    }

    /**
     * Scope para trades lucrativos
     */
    public function scopeProfitable($query)
    {
        return $query->where('profit', '>', 0);
    }

    /**
     * Accessor para verificar se o trade foi lucrativo
     */
    public function getIsProfitableAttribute(): bool
    {
        return $this->profit > 0;
    }

    /**
     * Accessor para formatar o profit como percentual
     */
    public function getProfitPercentageAttribute(): float
    {
        if ($this->total == 0) return 0;
        return ($this->profit / $this->total) * 100;
    }

    /**
     * Accessor para o valor total incluindo taxas
     */
    public function getTotalWithFeesAttribute(): float
    {
        return $this->total + ($this->fees ?? 0);
    }
}