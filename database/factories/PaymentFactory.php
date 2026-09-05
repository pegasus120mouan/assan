<?php

namespace Database\Factories;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'reference' => 'PAY-'.fake()->unique()->numerify('########'),
            'gateway' => PaymentGateway::CashOnDelivery,
            'amount' => fake()->numberBetween(5000, 100000),
            'status' => PaymentStatus::Pending,
            'transaction_id' => null,
            'metadata' => null,
            'paid_at' => null,
        ];
    }
}
