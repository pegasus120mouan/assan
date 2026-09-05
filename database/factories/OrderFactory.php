<?php

namespace Database\Factories;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 120000);
        $deliveryFee = 2000;
        $discount = 0;

        return [
            'order_number' => 'OVL-2026-'.fake()->unique()->numerify('######'),
            'user_id' => User::factory(),
            'coupon_id' => null,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'delivery_status' => DeliveryStatus::Pending,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal - $discount + $deliveryFee,
            'payment_method' => PaymentGateway::CashOnDelivery,
            'delivery_method' => DeliveryMethod::Standard,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('2250########'),
            'customer_email' => fake()->safeEmail(),
            'delivery_address' => fake()->streetAddress(),
            'delivery_commune_id' => null,
            'delivery_commune' => 'Cocody',
            'delivery_city' => 'Abidjan',
            'customer_notes' => null,
            'confirmed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'confirmed_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
