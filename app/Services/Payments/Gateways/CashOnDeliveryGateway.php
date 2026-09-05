<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Support\Payments\PaymentResult;

class CashOnDeliveryGateway implements PaymentGatewayInterface
{
    public function key(): PaymentGateway
    {
        return PaymentGateway::CashOnDelivery;
    }

    public function isEnabled(): bool
    {
        return (bool) config('payment.gateways.cash_on_delivery.enabled', true);
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function charge(Order $order, int $amount): PaymentResult
    {
        return new PaymentResult(
            status: PaymentStatus::CashOnDelivery,
            metadata: [
                'note' => 'Paiement à la livraison',
                'amount' => $amount,
            ],
            customerMessage: 'Payez '.$order->order_number.' à la réception du colis.',
        );
    }
}
