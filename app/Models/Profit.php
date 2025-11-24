<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profit extends Model
{
    protected $fillable = [
        'user_id', 
        'investment_id', 
        'amount', 
        'date'
    ];

    protected $casts = [
        'amount' => 'decimal:8', // Garante que venha como número, não string
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * O usuário que recebeu este lucro.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * O investimento que originou este lucro.
     */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }
}