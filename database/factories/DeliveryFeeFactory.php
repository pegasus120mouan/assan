<?php

namespace Database\Factories;

use App\Enums\DeliveryMethod;
use App\Enums\Status;
use App\Models\Commune;
use App\Models\DeliveryFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryFee>
 */
class DeliveryFeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'city_id' => null,
            'commune_id' => Commune::factory(),
            'zone_id' => null,
            'delivery_method' => DeliveryMethod::Standard,
            'min_order_amount' => 0,
            'max_order_amount' => null,
            'min_weight' => null,
            'max_weight' => null,
            'fee' => fake()->randomElement([1500, 2000, 2500, 3000]),
            'free_above_amount' => 50000,
            'status' => Status::Active,
        ];
    }
}
