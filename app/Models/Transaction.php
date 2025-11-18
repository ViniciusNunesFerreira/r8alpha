<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
     protected $fillable = [
    'user_id', 'wallet_id', 'type', 'amount',
    'balance_before', 'balance_after',
    'description', 'status',
    ];
    protected $casts = [
    'amount' => 'decimal:8',
    'balance_before' => 'decimal:8',
    'balance_after' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
