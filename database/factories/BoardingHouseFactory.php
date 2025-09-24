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
        $names = [
            'Kos One Hostel',
            'Kos Bulan Bali',
            'DSTAY Kost Bali',
            'Kubu GWK Villa',
            'Kos 168 Jimbaran',
            'Villa Viking',
            'Pier26 Bali Homestay',
            'Coliving Bali SWEET HOME Kost Lengkap di Tabanan Kota',
            'Umah Dauh Homestay',
            'Sun Homestay Canggu',
            'Paranyogan Homestay',
            'Ary House Ubud',
            'D&D homestay',
            'PIMA Homestay',
            'Royal Kamuela Villas & Suites at Monkey Forest Ubud - Adult Only',
            'Three Brothers Bungalows & Villas',
            'Carik Bali Guest House Canggu',
            'Uluwatu Jungle Villa',
            'Flower Bud Bungalow Balangan',
            'Gaing Mas Jimbaran Villas by Gaing Mas Group',
            'Del Cielo Villa Jimbaran',
            'Kris Kos',
            "Duana's Homestay",
            'Besakih Homestay & Villa',
            'Kedonganan Beach Villas',
            'Aruni Bali Jimbaran Boutique Villa',
            'Poedja Villa Jimbaran',
            'Villa Puri Royan Jimbaran',
            'Uli Wood Villa, Jimbaran BALI - near GWK',
            'Juada Garden',
            'Bali Komang Guest House Sanur',
            'Puri Kobot',
            'Asta House'
        ];
        $name = $this->faker->randomElement($names);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'house', true, 'kost'),
            'city_id' => City::factory(),
            'category_id' => Category::factory(),
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
