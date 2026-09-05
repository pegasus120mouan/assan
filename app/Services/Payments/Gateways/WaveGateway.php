<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentGateway;

class WaveGateway extends MobileMoneyGateway
{
    public function key(): PaymentGateway
    {
        return PaymentGateway::Wave;
    }
}
