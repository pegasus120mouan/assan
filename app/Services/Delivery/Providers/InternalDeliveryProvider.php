<?php

namespace App\Services\Delivery\Providers;

use App\Contracts\Delivery\DeliveryProviderInterface;
use App\Enums\DeliveryProvider;
use App\Models\Delivery;

class InternalDeliveryProvider implements DeliveryProviderInterface
{
    public function key(): DeliveryProvider
    {
        return DeliveryProvider::Internal;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function dispatch(Delivery $delivery): void
    {
        $delivery->update([
            'metadata' => array_merge($delivery->metadata ?? [], [
                'provider' => 'internal',
                'note' => 'Livraison interne OVL Tech.',
            ]),
        ]);
    }
}
