<?php

use App\Support\Money;

if (! function_exists('format_price')) {
    function format_price(int|float|string|null $amount): string
    {
        return Money::format($amount);
    }
}

if (! function_exists('shop_name')) {
    function shop_name(): string
    {
        return (string) config('shop.name', 'ASSAN');
    }
}

if (! function_exists('shop_tagline')) {
    function shop_tagline(): string
    {
        return (string) config('shop.tagline');
    }
}

if (! function_exists('shop_logo_url')) {
    function shop_logo_url(): string
    {
        return asset((string) config('shop.logo', 'images/logo/logo.png'));
    }
}

if (! function_exists('shop_whatsapp_number')) {
    function shop_whatsapp_number(): string
    {
        $raw = filled(config('shop.contact.whatsapp'))
            ? (string) config('shop.contact.whatsapp')
            : (string) (config('shop.contact.phone') ?: '0700000000');

        $digits = preg_replace('/\D+/', '', $raw) ?: '';

        if ($digits === '') {
            $digits = '0700000000';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '225'.substr($digits, 1);
        }

        return $digits;
    }
}

if (! function_exists('shop_whatsapp_url')) {
    function shop_whatsapp_url(?string $message = null): string
    {
        $text = $message ?: 'Bonjour, je souhaite passer une commande sur '.shop_name().'.';

        return 'https://wa.me/'.shop_whatsapp_number().'?text='.rawurlencode($text);
    }
}
