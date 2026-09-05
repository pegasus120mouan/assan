<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentGateway;

class OrangeMoneyGateway extends MobileMoneyGateway
{
    public function key(): PaymentGateway
    {
        return PaymentGateway::OrangeMoney;
    }
}
