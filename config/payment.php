<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moeda Padrão do Sistema
    |--------------------------------------------------------------------------
    |
    | Todos os valores no sistema são armazenados em USD (Dólar Americano)
    | A conversão para BRL (PIX) é feita usando taxa fixa configurada abaixo
    |
    */
    'default_currency' => 'USD',
    
    /*
    |--------------------------------------------------------------------------
    | Limites de Depósito
    |--------------------------------------------------------------------------
    |
    | Valores mínimo e máximo para depósitos em USD
    |
    */
    'deposit_limits' => [
        'min_usd' => 10,  // Mínimo de $10 USD
        'max_usd' => 100000,   // Sem limite máximo
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Taxa de Conversão USD para BRL (PIX)
    |--------------------------------------------------------------------------
    |
    | Taxa fixa utilizada para conversão de USD para BRL
    | 1 USD = 5.00 BRL
    |
    */
    'usd_to_brl_rate' => 5.00,
    
    /*
    |--------------------------------------------------------------------------
    | Configurações PIX - StartCash
    |--------------------------------------------------------------------------
    |
    */
    'pix' => [
        'enabled' => env('PIX_ENABLED', true),
        'api_url' => env('PIX_API_URL', 'https://api.startcash.io/v1'),
        'client_id' => env('PIX_CLIENT_ID', 'pk_EA3I576a6b_RgYvmxHMA4cneXlX2QD-74R4movXMFbVPnMOF'),
        'client_secret' => env('PIX_CLIENT_SECRET', 'sk_msl33IY__ypViDX5W6kjL_uhTFVsPeKITQ6F2l7Zo52H73VR'),
        'webhook_secret' => env('PIX_WEBHOOK_SECRET'),
        'expiration_minutes' => env('PIX_EXPIRATION_MINUTES', 30),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações USDT (BEP20) - NOWPayments
    |--------------------------------------------------------------------------
    |
    */
    'nowpayments' => [
        'enabled' => env('NOWPAYMENTS_ENABLED', true),
        'api_url' => env('NOWPAYMENTS_API_URL', 'https://api.nowpayments.io/v1'),
        'api_key' => env('NOWPAYMENTS_API_KEY'),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),
        
        // Apenas USDT na rede BEP20
        'currency' => 'usdtbep20',
        'network' => 'BEP20',
        'currency_display' => 'USDT (BEP20)',
        
        // Configurações de timeout
        'timeout' => env('NOWPAYMENTS_TIMEOUT', 3600), // 1 hora em segundos
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Status de Pagamento
    |--------------------------------------------------------------------------
    |
    */
    'status' => [
        'pending' => 'pending',
        'processing' => 'processing',
        'completed' => 'completed',
        'failed' => 'failed',
        'expired' => 'expired',
        'cancelled' => 'cancelled',
    ],
];