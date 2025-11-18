<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReferralCommission extends Model
{
    protected $fillable = [
        'user_id', 'source_user_id', 'source_id', 'source_type', 'amount', 'type', 'level'
    ];

    public function referral() 
    { 
        return $this->belongsTo(Referral::class); 
    }

    /*public function investment() 
    { 
        return $this->belongsTo(Investment::class); 
    }*/

    // O patrocinador que recebeu o pagamento.
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // O usuário que gerou o evento (o indicado).
    public function sourceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    // Relação polimórfica para a fonte (Investment/Profit)
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
