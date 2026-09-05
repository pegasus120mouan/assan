<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'image' => 'products/'.fake()->uuid().'.jpg',
            'alt' => fake()->words(3, true),
            'sort_order' => 0,
            'is_primary' => true,
        ];
    }
}
