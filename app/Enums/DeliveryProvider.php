<?php

namespace App\Enums;

enum DeliveryProvider: string
{
    case Internal = 'internal';
    case OvlDelivery = 'ovl_delivery';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Livraison interne',
            self::OvlDelivery => 'OVL Delivery',
        };
    }
}
