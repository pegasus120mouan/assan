<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_id' => null,
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake()->sentence(4),
            'comment' => fake()->paragraph(),
            'is_verified' => false,
            'status' => ReviewStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Approved,
        ]);
    }
}
