<?php

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Enums\Status;
use App\Models\City;
use App\Models\Commune;
use App\Models\DeliveryFee;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $region = Region::query()->firstOrCreate(
            ['slug' => 'lagunes'],
            ['name' => 'Lagunes', 'status' => Status::Active, 'sort_order' => 1]
        );

        $city = City::query()->firstOrCreate(
            ['slug' => 'abidjan'],
            ['region_id' => $region->id, 'name' => 'Abidjan', 'status' => Status::Active, 'sort_order' => 1]
        );

        $fees = [
            'Cocody' => 2500,
            'Yopougon' => 2000,
            'Marcory' => 2000,
            'Treichville' => 2000,
            'Plateau' => 1500,
            'Adjamé' => 2000,
            'Abobo' => 2500,
            'Koumassi' => 2000,
            'Port-Bouët' => 2500,
            'Attécoubé' => 2000,
        ];

        $order = 1;

        foreach ($fees as $name => $fee) {
            $commune = Commune::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'city_id' => $city->id,
                    'name' => $name,
                    'status' => Status::Active,
                    'sort_order' => $order++,
                ]
            );

            DeliveryFee::query()->firstOrCreate(
                ['commune_id' => $commune->id, 'delivery_method' => DeliveryMethod::Standard],
                [
                    'city_id' => $city->id,
                    'fee' => $fee,
                    'free_above_amount' => 50000,
                    'status' => Status::Active,
                    'min_order_amount' => 0,
                ]
            );
        }
    }
}
