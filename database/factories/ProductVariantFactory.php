<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $color = fake()->randomElement(['Noir', 'Blanc', 'Bleu']);

        return [
            'product_id' => Product::factory(),
            'name' => $color,
            'sku' => 'VAR-'.fake()->unique()->numerify('######'),
            'price' => null,
            'stock_quantity' => fake()->numberBetween(0, 40),
            'reserved_quantity' => 0,
            'options' => ['color' => $color],
            'status' => Status::Active,
        ];
    }
}
