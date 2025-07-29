<?php

namespace Database\Seeders;

use App\Models\BoardingHouse;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

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

        // Buat boarding house acak dengan city_id dan category_id dari data yang sudah ada
        BoardingHouse::factory()->count(10)->create([
            'city_id' => function () use ($cityIds) {
                return fake()->randomElement($cityIds);
            },
            'category_id' => function () use ($categoryIds) {
                return fake()->randomElement($categoryIds);
            },
        ]);

    }
}
