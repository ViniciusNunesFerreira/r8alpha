<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id', 
        'balance', 
        'sponsored_balance',
        'total_deposited',
        'total_withdrawn', 
        'total_profit',
        'total_sponsored',
        'type'
    ];
    
    protected $casts = [
        'balance' => 'decimal:8',
        'sponsored_balance' => 'decimal:8',
        'total_deposited' => 'decimal:8',
        'total_withdrawn' => 'decimal:8',
        'total_profit' => 'decimal:8',
        'total_sponsored' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Verifica se esta é a carteira de bônus de indicação.
     */
    public function isReferral(): bool
    {
        return $this->type === 'referral';
    }

    /**
     * Adiciona saldo normal à carteira
     */
    public function addBalance(float $amount, string $description = null): void
    {
        $balanceBefore = $this->balance;
        
        $this->increment('balance', $amount);
        $this->increment('total_deposited', $amount);
        
        // Registrar transação se necessário
        $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
            'description' => $description ?? 'Depósito manual pelo admin',
        ]);
    }

    /**
     * Adiciona saldo patrocinado à carteira
     */
    public function addSponsoredBalance(float $amount, string $description = null): void
    {
        $balanceBefore = $this->sponsored_balance;
        
        $this->increment('sponsored_balance', $amount);
        $this->increment('total_sponsored', $amount);
        
        // Registrar transação se necessário
        $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'sponsored_deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->sponsored_balance,
            'description' => $description ?? 'Saldo patrocinado adicionado pelo admin',
        ]);
    }

    /**
     * Retorna o saldo total disponível (normal + patrocinado)
     */
    public function getTotalAvailableBalanceAttribute(): float
    {
        return $this->balance + $this->sponsored_balance;
    }

    /**
     * Debita do saldo patrocinado primeiro, depois do saldo normal
     */
    public function debitBalance(float $amount): bool
    {
        $totalAvailable = $this->total_available_balance;
        
        if ($totalAvailable < $amount) {
            return false;
        }

        $remaining = $amount;
        $usedSponsored = 0;

        // Usar saldo patrocinado primeiro
        if ($this->sponsored_balance > 0) {
            $usedSponsored = min($this->sponsored_balance, $remaining);
            $this->decrement('sponsored_balance', $usedSponsored);
            $remaining -= $usedSponsored;
        }

        // Se ainda restar, usar saldo normal
        if ($remaining > 0) {
            $this->decrement('balance', $remaining);
        }

        return true;
    }
}