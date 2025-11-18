<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = ['user_id', 'sponsor_id', 'level', 'commission_earned'];

    public function user() 
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
    // O patrocinador (o 'pai' na árvore)
    public function sponsor() 
    { 
        return $this->belongsTo(User::class, 'sponsor_id'); 
    }
}
