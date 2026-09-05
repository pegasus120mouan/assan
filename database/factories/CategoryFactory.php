<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'image' => null,
            'icon' => 'grid',
            'status' => Status::Active,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
