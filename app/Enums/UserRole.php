<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Manager => 'Gestionnaire',
            self::Customer => 'Client',
        };
    }

    public function isStaff(): bool
    {
        return $this === self::Admin || $this === self::Manager;
    }

    /**
     * @return list<self>
     */
    public static function staffCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $role): bool => $role->isStaff(),
        ));
    }
}
