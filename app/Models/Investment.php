<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Investment extends Model
{
    protected $fillable = [
        'user_id', 
        'investment_plan_id', 
        'amount',
        'current_balance', 
        'total_profit', 
        'status',
        'is_sponsored',
        'admin_notes',
        'started_at', 
        'expires_at',
        'payment_status', 
        'payment_data', 
        'payment_method', 
        'last_profit_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:8',
        'current_balance' => 'decimal:8',
        'total_profit' => 'decimal:8',
        'is_sponsored' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_profit_at' => 'datetime'
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

    /**
     * Verifica se o investimento é patrocinado
     */
    public function isSponsored(): bool
    {
        return $this->is_sponsored === true;
    }

    /**
     * Calcula o rendimento diário considerando se é patrocinado
     * Você pode ajustar as regras específicas aqui
     */
    public function calculateDailyReturn(): float
    {
        $plan = $this->investmentPlan;
        
        if (!$plan) {
            return 0;
        }

        // Pega o retorno médio do plano
        $baseReturn = ($plan->daily_return_min + $plan->daily_return_max) / 2;

        // Se for patrocinado, você pode aplicar uma taxa diferente
        // Exemplo: patrocinados têm 50% do retorno normal
        if ($this->is_sponsored) {
            $baseReturn *= 0.5; // Ajuste conforme sua regra de negócio
        }

        return ($this->amount * $baseReturn) / 100;
    }

    /**
     * Scope para filtrar investimentos patrocinados
     */
    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    /**
     * Scope para filtrar investimentos normais
     */
    public function scopeNormal($query)
    {
        return $query->where('is_sponsored', false);
    }
}