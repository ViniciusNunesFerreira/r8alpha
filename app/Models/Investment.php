<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Investment extends Model
{
    protected $fillable = [
        'user_id', 'investment_plan_id', 'amount',
        'current_balance', 'total_profit', 'status',
        'started_at', 'expires_at',
    ];
    protected $casts = [
        'amount' => 'decimal:8',
        'current_balance' => 'decimal:8',
        'total_profit' => 'decimal:8',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function investmentPlan()
    {
        return $this->belongsTo(InvestmentPlan::class);
    }
    public function botInstance()
    {
        return $this->hasOne(BotInstance::class);
    }

    
     public function referralCommissionSource(): MorphMany
    {
        return $this->morphMany(ReferralCommission::class, 'source');
    }
}
