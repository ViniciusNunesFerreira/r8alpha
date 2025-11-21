<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Deposit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'gateway_transaction_id',
        'payment_method',
        'gateway',
        'amount_usd',
        'amount_brl',
        'conversion_rate',
        'amount_crypto',
        'crypto_currency',
        'crypto_network',
        'crypto_address',
        'payment_data',
        'qr_code_image',
        'pix_code',
        'status',
        'expires_at',
        'paid_at',
        'confirmed_at',
        'webhook_data',
        'webhook_attempts',
        'last_webhook_at',
        'user_ip',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'amount_usd' => 'decimal:2',
        'amount_brl' => 'decimal:2',
        'conversion_rate' => 'decimal:4',
        'amount_crypto' => 'decimal:8',
        'payment_data' => 'array',
        'webhook_data' => 'array',
        'webhook_attempts' => 'integer',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'last_webhook_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($deposit) {
            if (empty($deposit->transaction_id)) {
                $deposit->transaction_id = 'DEP-' . strtoupper(Str::random(16));
            }
        });
    }

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o depósito expirou
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->isAfter($this->expires_at) && $this->status === 'pending';
    }

    /**
     * Verifica se o depósito está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se o depósito foi completado
     */
    public function isCompleted(): bool
    {
        return $this->status === 'paid';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Marca o depósito como pago
     */
    public function markAsPaid(string $paidAt = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Marca o depósito como confirmado e credita o saldo
     */
    public function markAsConfirmed(string $paidAt = null): void
    {

        DB::transaction(function () use ($paidAt) {
            // 1. Tenta atualizar o status do depósito
            $this->update([
                'status' => 'paid',
                'paid_at' => $paidAt ?? now(),
                'confirmed_at' => $paidAt ?? now(),
            ]);

            $user = $this->user;
            if (!$user) {
                throw new \Exception("O usuário associado ao depósito não foi encontrado.");
            }

            // 2. Creditar saldo do usuário

            $wallet = $user->wallets()->firstOrCreate(
                ['type' => 'deposit'],
                ['balance' => 0, 'total_deposited' => 0, 'total_withdrawn' => 0, 'total_profit' => 0] 
            );

            $wallet->increment('balance', $this->amount_usd);
            $wallet->increment('total_deposited', $this->amount_usd);

        });

    }

    /**
     * Marca o depósito como expirado
     */
    public function markAsExpired(): void
    {
        if ($this->isPending()) {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * Marca o depósito como falho
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }

    /**
     * Registra tentativa de webhook
     */
    public function recordWebhookAttempt(array $data = []): void
    {
        $this->increment('webhook_attempts');
        $this->update([
            'webhook_data' => $data,
            'last_webhook_at' => now(),
        ]);
    }

    /**
     * Obtém a taxa de conversão aplicada
     */
    public function getConversionRateAttribute($value): ?float
    {
        return $value ? (float) $value : null;
    }

    /**
     * Formata o valor em USD
     */
    public function getFormattedAmountUsdAttribute(): string
    {
        return '$' . number_format($this->amount_usd, 2, '.', ',');
    }

    /**
     * Formata o valor em BRL
     */
    public function getFormattedAmountBrlAttribute(): ?string
    {
        return $this->amount_brl ? 'BRL ' . number_format($this->amount_brl, 2, ',', '.') : null;
    }

    /**
     * Formata o valor em Crypto
     */
    public function getFormattedAmountCryptoAttribute(): ?string
    {
        return $this->amount_crypto ? number_format($this->amount_crypto, 8, '.', '') . ' ' . $this->crypto_currency : null;
    }

    /**
     * Obtém o nome amigável do método de pagamento
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            'pix' => 'PIX',
            'crypto' => 'CriptoCurrency (USDT BEP20)',
            default => 'Unknown',
        };
    }

    /**
     * Obtém a cor do status para exibição
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'paid'  => 'success',
            'failed', 'expired', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtém o label do status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Awaiting Payment',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'paid'  =>  'Paid',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Scope para depósitos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para depósitos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para depósitos completados
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para depósitos expirados
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                          ->where('expires_at', '<', now());
                    });
    }
}