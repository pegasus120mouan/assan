<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'type' => StockMovementType::Purchase,
            'quantity' => fake()->numberBetween(1, 20),
            'reference_type' => null,
            'reference_id' => null,
            'reason' => 'Réapprovisionnement',
            'user_id' => null,
        ];
    }
}
