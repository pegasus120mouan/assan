<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Standard = 'standard';
    case Express = 'express';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Express => 'Express',
            self::Pickup => 'Retrait',
        };
    }
}
