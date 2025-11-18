<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
     protected $fillable = [
        'user_id', 'balance', 'total_deposited',
        'total_withdrawn', 'total_profit', 'type'
    ];
    protected $casts = [
    'balance' => 'decimal:8',
    'total_deposited' => 'decimal:8',
    'total_withdrawn' => 'decimal:8',
    'total_profit' => 'decimal:8',
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

}
