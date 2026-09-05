<?php

namespace Database\Factories;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile>
 */
class CustomerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'address' => fake()->streetAddress(),
            'commune' => fake()->randomElement(['Cocody', 'Yopougon', 'Marcory', 'Plateau']),
            'city' => 'Abidjan',
            'country' => "Côte d'Ivoire",
            'notes' => null,
        ];
    }
}
