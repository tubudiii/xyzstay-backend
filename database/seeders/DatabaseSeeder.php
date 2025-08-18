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
use Spatie\Permission\Models\Role;

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

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'customer']);

        // Buat 9 kota dan 5 kategori
        City::factory()->count(9)->create();
        Category::factory()->count(5)->create();

        // Ambil ID yang sudah dibuat
        $cityIds = City::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();
        $users = User::factory(4)->create();

        // Buat boarding house acak dengan city_id dan category_id dari data yang sudah ada
        $boardingHouses = BoardingHouse::factory()
            ->count(5)
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
    }
}
