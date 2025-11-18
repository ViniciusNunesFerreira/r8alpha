<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'binance' => [
        'api_key' => env('BINANCE_API_KEY'),
        'api_secret' => env('BINANCE_API_SECRET'),
        'base_url' => env('BINANCE_BASE_URL', 'https://api.binance.com'),
        'testnet' => env('BINANCE_TESTNET', false),
        'testnet_url' => 'https://testnet.binance.vision',
        
        // Rate Limiting
        'rate_limit' => [
            'requests_per_minute' => 1200,
            'orders_per_second' => 10,
            'orders_per_day' => 200000,
        ],
        // Timeout configurações
        'timeout' => 10, // segundos
        'connect_timeout' => 5,
        // Retry configurações
        'retry' => [
            'times' => 3,
            'sleep' => 100, // milliseconds
        ],
    ],

];
