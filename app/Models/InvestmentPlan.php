<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
    protected $fillable = [
        'name', 'description', 'min_amount', 
        'max_amount', 'daily_return_min', 
        'daily_return_max', 'duration_days', 'is_active', 'is_capital_back'
    ];

    protected $casts = [
        'min_amount' => 'decimal:8',
        'max_amount' => 'decimal:8',
        'daily_return_min' => 'decimal:2',
        'daily_return_max' => 'decimal:2',
        'is_active' => 'boolean',
        'is_capital_back' => 'boolean',
    ];

     public function investments()
    {
        return $this->hasMany(Investment::class);
    }
}
