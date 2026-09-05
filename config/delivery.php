<?php

return [

    'default' => env('DELIVERY_PROVIDER', 'internal'),

    'ovl' => [
        'api_url' => env('OVL_DELIVERY_API_URL'),
        'api_key' => env('OVL_DELIVERY_API_KEY'),
    ],

    'standard_fee' => (int) env('DELIVERY_STANDARD_FEE', 2000),

];
