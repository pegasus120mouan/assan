<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API
    |--------------------------------------------------------------------------
    |
    | Credentials are read from the environment. Leave empty until the
    | official WhatsApp Business integration is activated.
    |
    */

    'enabled' => (bool) env('WHATSAPP_ENABLED', false),

    'api_url' => env('WHATSAPP_API_URL'),

    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),

    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),

];
