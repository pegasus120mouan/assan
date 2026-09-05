<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentGateway;

class MtnMoneyGateway extends MobileMoneyGateway
{
    public function key(): PaymentGateway
    {
        return PaymentGateway::MtnMoney;
    }
}
