<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Str;

class BotInstance extends Model
{
    protected $fillable = [
        'user_id', 'investment_id', 'instance_id',
        'is_active', 'config', 'total_trades',
        'successful_trades', 'total_profit', 'last_trade_at',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'total_profit' => 'decimal:8',
        'last_trade_at' => 'datetime',
    ];

     protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->instance_id)) {
                $model->instance_id = 'BOT-' . Str::uuid();
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
    public function arbitrageOpportunities()
    {
        return $this->hasMany(ArbitrageOpportunity::class);
    }
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
    public function getSuccessRateAttribute()
    {
        if ($this->total_trades == 0) return 0;
        return round(($this->successful_trades / $this->total_trades) * 100, 2);
    }
}
