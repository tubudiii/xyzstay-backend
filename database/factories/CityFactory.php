<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    public function definition(): array
    {
        $baliCities = [
            'Denpasar',
            'Buleleng',
            'Tabanan',
            'Gianyar',
            'Bangli',
            'Karangasem',
            'Badung',
            'Negara',
            'Klungkung'
        ];

        $name = $this->faker->unique()->randomElement($baliCities);

        return [
            'image' => $this->faker->imageUrl(640, 480, 'city', true, 'Bali'),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
