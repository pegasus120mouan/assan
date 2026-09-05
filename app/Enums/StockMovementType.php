<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case Return = 'return';
    case Damaged = 'damaged';
    case Cancellation = 'cancellation';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Achat',
            self::Sale => 'Vente',
            self::Adjustment => 'Ajustement',
            self::Return => 'Retour',
            self::Damaged => 'Endommagé',
            self::Cancellation => 'Annulation',
        };
    }

    public function increasesStock(): bool
    {
        return in_array($this, [self::Purchase, self::Return, self::Cancellation], true);
    }

    public function decreasesStock(): bool
    {
        return in_array($this, [self::Sale, self::Damaged], true);
    }
}
