<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Enums\PromotionType;
use App\Enums\Status;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Promo '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'type' => PromotionType::Product,
            'discount_type' => DiscountType::Percentage,
            'value' => 10,
            'product_id' => Product::factory(),
            'category_id' => null,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addWeek(),
            'status' => Status::Active,
            'priority' => 0,
        ];
    }
}
