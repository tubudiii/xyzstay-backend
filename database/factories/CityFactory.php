<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    public function definition(): array
    {
        $baliCities = [
            'Badung',
            'Denpasar',
            'Bangli',
            'Buleleng',
            'Gianyar',
            'Jembrana',
            'Karangasem',
            'Klungkung',
            'Tabanan',
        ];

        $name = $this->faker->unique()->randomElement($baliCities);

        return [
            // Path image lokal, diasumsikan format jpg dan nama file sama dengan nama city
            'image' => 'cities/' . $name . '.png',
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
