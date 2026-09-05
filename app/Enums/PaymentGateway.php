<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case CashOnDelivery = 'cash_on_delivery';
    case OrangeMoney = 'orange_money';
    case MtnMoney = 'mtn_money';
    case MoovMoney = 'moov_money';
    case Wave = 'wave';

    public function label(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'Paiement à la livraison',
            self::OrangeMoney => 'Orange Money',
            self::MtnMoney => 'MTN Money',
            self::MoovMoney => 'Moov Money',
            self::Wave => 'Wave',
        };
    }
}
