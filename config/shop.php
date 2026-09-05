<?php

return [

    'name' => env('APP_NAME', 'ASSAN'),

    'tagline' => env('SHOP_TAGLINE', 'Votre site d\'achat en ligne.'),

    'logo' => env('SHOP_LOGO', 'images/logo/logo.png'),

    'currency' => env('SHOP_CURRENCY', 'XOF'),

    'currency_label' => env('SHOP_CURRENCY_LABEL', 'FCFA'),

    'country' => 'CI',

    'country_name' => "Côte d'Ivoire",

    'timezone' => env('APP_TIMEZONE', 'Africa/Abidjan'),

    'locale' => env('APP_LOCALE', 'fr'),

    'contact' => [
        'email' => env('SHOP_EMAIL', 'contact@example.com'),
        'phone' => env('SHOP_PHONE'),
        'sav' => env('SHOP_SAV_PHONE'),
        'whatsapp' => env('SHOP_WHATSAPP_NUMBER'),
        'city' => 'Abidjan',
        'address' => env('SHOP_ADDRESS'),
    ],

    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrateur ASSAN'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'abandoned_cart_hours' => (int) env('CART_ABANDONED_AFTER_HOURS', 48),

];
