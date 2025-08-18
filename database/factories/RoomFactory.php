<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\BoardingHouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $roomType = $this->faker->randomElement(['Single', 'Double', 'Suite']);

        // Harga berdasarkan tipe kamar
        $priceMap = [
            'Single' => 100000,
            'Double' => 200000,
            'Suite' => 500000,
        ];

        return [
            'boarding_house_id' => BoardingHouse::factory(),
            'name' => 'Room ' . $this->faker->unique()->numberBetween(1, 100),
            'slug' => fn(array $attributes) => Str::slug($attributes['name']), // <— otomatis bikin slug
            'room_type' => $roomType,
            'square_feet' => $this->faker->numberBetween(12, 40),
            'capacity' => $this->faker->numberBetween(1, 4),
            'price_per_day' => $priceMap[$roomType],
            'is_available' => $this->faker->boolean(80),
        ];
    }

}
