<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'referred_by', 
        'referral_code', 
        'first_investment_at',
        'status',
        'type',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_investment_at' => 'datetime'
        ];
    }

     // Relacionamentos
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }
    public function botInstances()
    {
        return $this->hasMany(BotInstance::class);
    }
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function depositWallet()
    {
        return $this->hasOne(Wallet::class)->where('type', 'deposit');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Carteira específica para receber bônus de indicação (type='referral').
     */
    public function referralWallet()
    {
        return $this->hasOne(Wallet::class)->where('type', 'referral');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'sponsor_id');
    }


    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }


    public function referralCommissions()
    {
        // Usa 'user_id' na ReferralCommission como a chave local padrão
        return $this->hasMany(ReferralCommission::class, 'user_id'); 
    }
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower(trim($value));
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function getActiveBotsCountAttribute(): int
    {
        // Se a relação já foi carregada, usa ela (evita query extra)
        if ($this->relationLoaded('botInstances')) {
            return $this->botInstances->where('is_active', true)->count();
        }
        
        // Caso contrário, faz a query
        return $this->botInstances()->where('is_active', true)->count();
    }

    
}
