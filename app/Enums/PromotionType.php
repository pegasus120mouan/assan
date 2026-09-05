<?php

namespace App\Enums;

enum PromotionType: string
{
    case Product = 'product';
    case Category = 'category';
    case FlashSale = 'flash_sale';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Produit',
            self::Category => 'Catégorie',
            self::FlashSale => 'Vente flash',
        };
    }
}
