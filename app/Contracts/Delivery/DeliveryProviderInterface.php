<?php

namespace App\Contracts\Delivery;

use App\Enums\DeliveryProvider;
use App\Models\Delivery;

interface DeliveryProviderInterface
{
    public function key(): DeliveryProvider;

    public function isConfigured(): bool;

    public function dispatch(Delivery $delivery): void;
}
