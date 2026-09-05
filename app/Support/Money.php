<?php

namespace App\Support;

class Money
{
    public static function format(int|float|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return number_format($value, 0, ',', ' ').' '.config('shop.currency_label', 'FCFA');
    }
}
