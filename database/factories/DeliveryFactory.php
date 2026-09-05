<?php

namespace Database\Factories;

use App\Enums\DeliveryProvider;
use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'tracking_number' => 'TRK-'.fake()->unique()->numerify('########'),
            'provider' => DeliveryProvider::Internal,
            'status' => DeliveryStatus::Pending,
            'assigned_to' => null,
            'delivery_fee' => 2000,
            'picked_up_at' => null,
            'delivered_at' => null,
            'failure_reason' => null,
            'metadata' => null,
        ];
    }
}
