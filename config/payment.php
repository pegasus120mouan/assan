<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default payment gateway
    |--------------------------------------------------------------------------
    |
    | Gateways are registered later via PaymentGatewayInterface. No live
    | provider is called until credentials are supplied in the environment.
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'cash_on_delivery'),

    'gateways' => [
        'cash_on_delivery' => [
            'enabled' => (bool) env('PAYMENT_COD_ENABLED', true),
        ],
        'orange_money' => [
            'enabled' => (bool) env('PAYMENT_ORANGE_MONEY_ENABLED', false),
            'api_url' => env('PAYMENT_ORANGE_MONEY_API_URL'),
            'api_key' => env('PAYMENT_ORANGE_MONEY_API_KEY'),
        ],
        'mtn_money' => [
            'enabled' => (bool) env('PAYMENT_MTN_MONEY_ENABLED', false),
            'api_url' => env('PAYMENT_MTN_MONEY_API_URL'),
            'api_key' => env('PAYMENT_MTN_MONEY_API_KEY'),
        ],
        'moov_money' => [
            'enabled' => (bool) env('PAYMENT_MOOV_MONEY_ENABLED', false),
            'api_url' => env('PAYMENT_MOOV_MONEY_API_URL'),
            'api_key' => env('PAYMENT_MOOV_MONEY_API_KEY'),
        ],
        'wave' => [
            'enabled' => (bool) env('PAYMENT_WAVE_ENABLED', false),
            'api_url' => env('PAYMENT_WAVE_API_URL'),
            'api_key' => env('PAYMENT_WAVE_API_KEY'),
        ],
    ],

];
