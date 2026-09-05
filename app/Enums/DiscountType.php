<?php

namespace App\Enums;

enum DiscountType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case PromotionalPrice = 'promotional_price';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Montant fixe',
            self::Percentage => 'Pourcentage',
            self::PromotionalPrice => 'Prix promotionnel',
        };
    }
}
