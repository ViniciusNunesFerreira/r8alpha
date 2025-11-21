<?php

namespace App\Helpers;

class PaymentHelper
{
    /**
     * Converte USD para BRL usando taxa configurada
     */
    public static function usdToBrl(float $usd): float
    {
        $rate = config('payment.usd_to_brl_rate', 5.00);
        return round($usd * $rate, 2);
    }

    /**
     * Converte BRL para USD usando taxa configurada
     */
    public static function brlToUsd(float $brl): float
    {
        $rate = config('payment.usd_to_brl_rate', 5.00);
        return round($brl / $rate, 2);
    }

    /**
     * Formata valor em USD
     */
    public static function formatUsd(float $amount): string
    {
        return '$' . number_format($amount, 2, '.', ',');
    }

    /**
     * Formata valor em BRL
     */
    public static function formatBrl(float $amount): string
    {
        return 'BRL ' . number_format($amount, 2, ',', '.');
    }

    /**
     * Formata valor em crypto
     */
    public static function formatCrypto(float $amount, string $currency = 'USDT'): string
    {
        $decimals = $currency === 'BTC' ? 8 : 2;
        return number_format($amount, $decimals, '.', '') . ' ' . $currency;
    }

    /**
     * Valida se o valor está dentro dos limites
     */
    public static function isValidDepositAmount(float $amount): bool
    {
        $min = config('payment.deposit_limits.min_usd');
        $max = config('payment.deposit_limits.max_usd');

        if ($amount < $min) {
            return false;
        }

        if ($max && $amount > $max) {
            return false;
        }

        return true;
    }

    /**
     * Obtém a mensagem de limite de depósito
     */
    public static function getDepositLimitsMessage(): string
    {
        $min = config('payment.deposit_limits.min_usd');
        $max = config('payment.deposit_limits.max_usd');

        $message = "Min: " . self::formatUsd($min);

        if ($max) {
            $message .= " | Max: " . self::formatUsd($max);
        } else {
            $message .= " | No maximum limit";
        }

        return $message;
    }

    /**
     * Gera ID único para transação
     */
    public static function generateTransactionId(string $prefix = 'DEP'): string
    {
        return $prefix . '-' . strtoupper(substr(md5(uniqid() . time()), 0, 16));
    }

    /**
     * Valida CPF
     */
    public static function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

        if (intval($cpf[9]) !== $digit1) {
            return false;
        }

        // Validação do segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

        return intval($cpf[10]) === $digit2;
    }

    /**
     * Formata CPF
     */
    public static function formatCpf(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }

    /**
     * Valida endereço BEP20
     */
    public static function isBep20Address(string $address): bool
    {
        // Endereços BEP20 começam com 0x e têm 42 caracteres (0x + 40 hex)
        return preg_match('/^0x[a-fA-F0-9]{40}$/', $address) === 1;
    }

    /**
     * Obtém nome amigável do status
     */
    public static function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Awaiting Payment',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Obtém cor do status para badges
     */
    public static function getStatusColor(string $status): string
    {
        return match($status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'failed', 'expired', 'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Calcula taxa efetiva do gateway
     */
    public static function calculateGatewayFee(float $amount, string $gateway): float
    {
        $fees = [
            'startcash' => 0.0, 
            'nowpayments' => 0.5, 
        ];

        $feePercent = $fees[$gateway] ?? 0;
        return round($amount * ($feePercent / 100), 2);
    }

    /**
     * Verifica se é horário comercial brasileiro
     */
    public static function isBrazilianBusinessHours(): bool
    {
        $now = now()->timezone('America/Sao_Paulo');
        
        // Verifica se é fim de semana
        if ($now->isWeekend()) {
            return false;
        }

        // Verifica se está entre 9h e 18h
        $hour = $now->hour;
        return $hour >= 9 && $hour < 18;
    }

    /**
     * Gera hash para validação
     */
    public static function generateHash(array $data, string $secret): string
    {
        ksort($data);
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $json, $secret);
    }

    /**
     * Valida hash
     */
    public static function validateHash(array $data, string $hash, string $secret): bool
    {
        $calculatedHash = self::generateHash($data, $secret);
        return hash_equals($calculatedHash, $hash);
    }

    /**
     * Formata tempo restante até expiração
     */
    public static function formatTimeRemaining(\DateTime $expiresAt): string
    {
        $now = new \DateTime();
        $diff = $now->diff($expiresAt);

        if ($diff->invert) {
            return 'Expirado';
        }

        $parts = [];

        if ($diff->h > 0) {
            $parts[] = $diff->h . 'h';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . 'm';
        }
        if ($diff->s > 0 && empty($parts)) {
            $parts[] = $diff->s . 's';
        }

        return implode(' ', $parts);
    }

    /**
     * Sanitiza valor monetário de input
     */
    public static function sanitizeMoneyInput(string $input): float
    {
        // Remove tudo exceto números e ponto decimal
        $cleaned = preg_replace('/[^0-9.]/', '', $input);
        
        // Remove pontos extras, mantendo apenas o último
        $parts = explode('.', $cleaned);
        if (count($parts) > 2) {
            $cleaned = implode('', array_slice($parts, 0, -1)) . '.' . end($parts);
        }

        return floatval($cleaned);
    }

    /**
     * Verifica se gateway está disponível
     */
    public static function isGatewayAvailable(string $gateway): bool
    {
        return match($gateway) {
            'startcash' => config('payment.pix.enabled'),
            'nowpayments' => config('payment.nowpayments.enabled'),
            default => false,
        };
    }

    /**
     * Obtém lista de métodos de pagamento disponíveis
     */
    public static function getAvailablePaymentMethods(): array
    {
        $methods = [];

        if (config('payment.pix.enabled')) {
            $methods[] = [
                'key' => 'pix',
                'name' => 'PIX',
                'description' => 'Instant payment in (BRL)',
                'icon' => '🇧🇷',
            ];
        }

        if (config('payment.nowpayments.enabled')) {
            $methods[] = [
                'key' => 'crypto',
                'name' => 'USDT (BEP20)',
                'description' => 'USDT cryptocurrency on the BEP20 network',
                'icon' => '₮',
            ];
        }

        return $methods;
    }
}