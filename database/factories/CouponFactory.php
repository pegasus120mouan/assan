<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Enums\Status;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('OVL-####')),
            'type' => CouponType::Percentage,
            'value' => fake()->randomElement([5, 10, 15, 20]),
            'minimum_order_amount' => 10000,
            'maximum_discount' => 20000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'usage_limit' => 100,
            'usage_per_customer' => 1,
            'used_count' => 0,
            'status' => Status::Active,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);
    }
}
