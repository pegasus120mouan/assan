<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $sellingPrice = fake()->numberBetween(2500, 150000);
        $comparePrice = fake()->boolean(40) ? (int) round($sellingPrice * 1.25) : null;

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'purchase_price' => (int) round($sellingPrice * 0.65),
            'selling_price' => $sellingPrice,
            'compare_price' => $comparePrice,
            'cost_price' => (int) round($sellingPrice * 0.6),
            'stock_quantity' => fake()->numberBetween(0, 80),
            'reserved_quantity' => 0,
            'low_stock_threshold' => 5,
            'weight' => fake()->randomFloat(2, 0.05, 5),
            'status' => Status::Active,
            'featured' => fake()->boolean(20),
            'is_new' => fake()->boolean(30),
            'is_best_seller' => fake()->boolean(15),
            'meta_title' => Str::title($name),
            'meta_description' => fake()->sentence(),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }

    public function inStock(int $quantity = 20): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $quantity,
            'reserved_quantity' => 0,
        ]);
    }
}
