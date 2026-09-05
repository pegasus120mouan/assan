<?php

namespace App\Enums;

enum CartStatus: string
{
    case Active = 'active';
    case Converted = 'converted';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Actif',
            self::Converted => 'Converti',
            self::Abandoned => 'Abandonné',
        };
    }
}
