<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        $commune = fake()->randomElement(['Cocody', 'Yopougon', 'Marcory', 'Treichville', 'Plateau']);

        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Maison', 'Bureau', 'Autre']),
            'recipient_name' => fake()->name(),
            'phone' => fake()->numerify('2250########'),
            'address' => fake()->streetAddress(),
            'commune_id' => null,
            'commune' => $commune,
            'city' => 'Abidjan',
            'instructions' => fake()->optional()->sentence(),
            'is_default' => false,
        ];
    }
}
