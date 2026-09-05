<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Support\Payments\PaymentResult;

abstract class MobileMoneyGateway implements PaymentGatewayInterface
{
    public function isEnabled(): bool
    {
        return (bool) config('payment.gateways.'.$this->key()->value.'.enabled', false);
    }

    public function isConfigured(): bool
    {
        $config = config('payment.gateways.'.$this->key()->value, []);

        return filled($config['api_url'] ?? null) && filled($config['api_key'] ?? null);
    }

    public function charge(Order $order, int $amount): PaymentResult
    {
        return new PaymentResult(
            status: PaymentStatus::Pending,
            metadata: [
                'mode' => 'stub',
                'configured' => $this->isConfigured(),
                'amount' => $amount,
                'note' => $this->isConfigured()
                    ? 'Identifiants présents. L’appel API réel n’est pas encore branché.'
                    : 'En attente d’intégration '.$this->key()->label().'.',
            ],
            customerMessage: 'Commande enregistrée. Nous vous contacterons pour finaliser le paiement '.$this->key()->label().'.',
        );
    }
}
