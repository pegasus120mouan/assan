<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\City;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Zone '.fake()->unique()->word();

        return [
            'city_id' => City::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => Status::Active,
            'sort_order' => 0,
        ];
    }
}
