<?php

namespace App\Services\Delivery\Providers;

use App\Contracts\Delivery\DeliveryProviderInterface;
use App\Enums\DeliveryProvider;
use App\Models\Delivery;

class OvlDeliveryProvider implements DeliveryProviderInterface
{
    public function key(): DeliveryProvider
    {
        return DeliveryProvider::OvlDelivery;
    }

    public function isConfigured(): bool
    {
        return filled(config('delivery.ovl.api_url')) && filled(config('delivery.ovl.api_key'));
    }

    public function dispatch(Delivery $delivery): void
    {
        $delivery->update([
            'metadata' => array_merge($delivery->metadata ?? [], [
                'ovl' => [
                    'status' => $this->isConfigured() ? 'ready_not_sent' : 'not_configured',
                    'note' => $this->isConfigured()
                        ? 'Identifiants OVL Delivery présents. L’appel API réel n’est pas encore branché.'
                        : 'OVL Delivery n’est pas configuré. La livraison reste gérée en interne.',
                ],
            ]),
        ]);
    }
}
