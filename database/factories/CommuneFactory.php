<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\City;
use App\Models\Commune;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Commune>
 */
class CommuneFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Cocody', 'Yopougon', 'Marcory', 'Treichville', 'Plateau',
            'Adjamé', 'Abobo', 'Koumassi', 'Port-Bouët', 'Attécoubé',
        ]).' '.fake()->unique()->numerify('##');

        return [
            'city_id' => City::factory(),
            'zone_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => Status::Active,
            'sort_order' => 0,
        ];
    }
}
