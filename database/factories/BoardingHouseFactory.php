<?php

namespace Database\Factories;

use App\Models\BoardingHouse;
use App\Models\Category;
use App\Models\City;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardingHouse>
 */
class BoardingHouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true); // Contoh: "Kost Putri Damai"

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'house', true, 'kost'), // URL palsu
            'city_id' => City::factory(), // pastikan kamu punya CityFactory
            'category_id' => Category::factory(), // pastikan kamu punya CategoryFactory
            'description' => $this->faker->paragraph(4),
            'address' => $this->faker->address,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (BoardingHouse $boardingHouse) {
            Room::factory()
                ->count(3)
                ->create(['boarding_house_id' => $boardingHouse->id]);
        });
    }
}
