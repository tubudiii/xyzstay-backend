<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\BoardingHouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'boarding_house_id' => BoardingHouse::factory(), // default kalau tidak di-set manual
            'name' => 'Room ' . $this->faker->unique()->numberBetween(1, 100),
            'room_type' => $this->faker->randomElement(['Single', 'Double', 'Suite']),
            'square_feet' => $this->faker->numberBetween(12, 40),
            'capacity' => $this->faker->numberBetween(1, 4),
            'price_per_month' => $this->faker->numberBetween(500000, 5000000),
            'is_available' => $this->faker->boolean(80), // 80% tersedia
        ];
    }
}
