<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Assigned => 'Assignée',
            self::PickedUp => 'Récupérée',
            self::InTransit => 'En transit',
            self::Delivered => 'Livrée',
            self::Failed => 'Échec',
            self::Returned => 'Retournée',
        };
    }
}
