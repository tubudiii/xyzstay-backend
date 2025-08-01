<?php

namespace Database\Seeders;

use App\Models\BoardingHouse;
use App\Models\Category;
use App\Models\City;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use App\Models\Room;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat admin
        User::factory()->create([
            'name' => 'Admin XYZ',
            'email' => 'admin@xyzstay.com',
            // 'role' => 'admin',
        ]);


        // Buat 9 kota dan 5 kategori
        City::factory()->count(9)->create();
        Category::factory()->count(5)->create();

        // Ambil ID yang sudah dibuat
        $cityIds = City::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();
        $users = User::factory(10)->create();

        // Buat boarding house acak dengan city_id dan category_id dari data yang sudah ada
        $boardingHouses = BoardingHouse::factory()
            ->count(10)
            ->create([
                'city_id' => fn() => fake()->randomElement($cityIds),
                'category_id' => fn() => fake()->randomElement($categoryIds),
            ])
            ->each(function ($boardingHouse) {
                // Tambahkan 3 kamar untuk setiap boarding house
                Room::factory()->count(3)->create([
                    'boarding_house_id' => $boardingHouse->id,
                ]);
            });

        // Buat 10 transaksi acak
        // Buat transaksi hanya untuk boardingHouse yang memiliki room
        Transaction::factory(10)
            ->state(new Sequence(
                ...collect(range(1, 10))->map(function () use ($users) {
                    $boardingHouse = BoardingHouse::inRandomOrder()->first();

                    if ($boardingHouse->rooms()->count() === 0) {
                        $boardingHouse->rooms()->create([
                            'name' => fake()->word(),
                            'room_type' => fake()->randomElement(['single', 'double']),
                            'square_feet' => fake()->numberBetween(15, 30),
                            'capacity' => fake()->numberBetween(1, 2),
                            // 'price_per_month' => fake()->numberBetween(1000000, 3000000),
                            'is_available' => true,
                        ]);
                    }

                    $room = $boardingHouse->rooms()->inRandomOrder()->first();

                    return [
                        'user_id' => $users->random()->id,
                        'boarding_house_id' => $boardingHouse->id,
                        'room_id' => $room->id,
                    ];
                })->toArray()
            ))->create();


    }
}
