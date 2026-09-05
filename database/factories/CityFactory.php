<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'region_id' => Region::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => Status::Active,
            'sort_order' => 0,
        ];
    }
}
