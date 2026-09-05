<?php

namespace App\Events;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Delivery $delivery,
        public DeliveryStatus $previous,
        public DeliveryStatus $current,
    ) {}
}
