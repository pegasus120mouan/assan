<?php

namespace App\Contracts\Payments;

use App\Enums\PaymentGateway;
use App\Models\Order;
use App\Support\Payments\PaymentResult;

interface PaymentGatewayInterface
{
    public function key(): PaymentGateway;

    public function isEnabled(): bool;

    public function isConfigured(): bool;

    public function charge(Order $order, int $amount): PaymentResult;
}
