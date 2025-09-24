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
        $user = User::firstOrCreate(
            ['email' => 'admin@xyzstay.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );

        // pastikan role super-admin ada
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // assign role ke user
        $user->assignRole($role);

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

        // Buat 33 boarding house dengan nama dari array dan boarding_house_id 1-3
        $boardingHousesData = [
            ['name' => 'Kos One Hostel', 'address' => 'JL. Pantai Batu Bolong No.78, 80361 Canggu, Indonesia'],
            ['name' => 'Kos Bulan Bali', 'address' => 'III/8 Gang Ratna, Tuban, 80361 Kuta, Indonesia'],
            ['name' => 'DSTAY Kost Bali', 'address' => 'Jalan Goa Gong No. 19, 80361 Jimbaran, Indonesia'],
            ['name' => 'Kubu GWK Villa', 'address' => 'Jl Raya Uluwatu No.111, 80361 Jimbaran, Indonesia'],
            ['name' => 'Kos 168 Jimbaran', 'address' => '80361, Kos 168, Nomor 168, Gang Buanasari, Jimbaran, Indonesia'],
            ['name' => 'Villa Viking', 'address' => 'Jalan Jepun Block E, no. 6, 80361 Jimbaran, Indonesia'],
            ['name' => 'Pier26 Bali Homestay', 'address' => 'Jalan Darmawangsa - Gubug Sari, Gg. Nuri 3, 80361 Jimbaran, Indonesia'],
            ['name' => 'Coliving Bali SWEET HOME Kost Lengkap di Tabanan Kota', 'address' => 'Gg. II, Delod Peken, 82121 Tabanan, Indonesia'],
            ['name' => 'Umah Dauh Homestay', 'address' => 'Jl. Gautama No.16, Br Padang Tegal Kaja, 80571 Ubud, Indonesia'],
            ['name' => 'Sun Homestay Canggu', 'address' => 'Jalan Tanah Barak, 80365 Canggu, Indonesia'],
            ['name' => 'Paranyogan Homestay', 'address' => 'Jalan Pantai Balangan No 11, Kabupaten Badung, Indonesia'],
            ['name' => 'Ary House Ubud', 'address' => 'Jl. Cok Rai Pudak, Serongga, Br. Tengah Kangin, 80571 Ubud, Indonesia'],
            ['name' => 'D&D homestay', 'address' => 'Jalan Tukad Punggawa Gang buntu II serangan, 80229 Denpasar, Indonesia'],
            ['name' => 'PIMA Homestay', 'address' => '96 Jalan Tukad Punggawa, 80229 Pesanggaran, Indonesia'],
            ['name' => 'Royal Kamuela Villas & Suites at Monkey Forest Ubud - Adult Only', 'address' => 'Jl. Monkey Forest, Bali, 80571 Ubud, Indonesia'],
            ['name' => 'Three Brothers Bungalows & Villas', 'address' => 'Jl. Legian Tengah gg Three brother, 80361 Legian, Indonesia'],
            ['name' => 'Carik Bali Guest House Canggu', 'address' => 'Jl. Raya Babakan Canggu No.18, Canggu, Kec. Kuta Utara, Indonesia'],
            ['name' => 'Uluwatu Jungle Villa', 'address' => 'Jalan Pantai Suluban, 80361 Uluwatu, Indonesia'],
            ['name' => 'Flower Bud Bungalow Balangan', 'address' => 'Jalan Pantai Balangan, 80361 Jimbaran, Indonesia'],
            ['name' => 'Gaing Mas Jimbaran Villas by Gaing Mas Group', 'address' => 'Jalan Tegal Mas no 8 , 80361 Jimbaran, Indonesia'],
            ['name' => 'Del Cielo Villa Jimbaran', 'address' => 'Jalan Bukit Permai Lot B4 No. 8, 80361 Jimbaran, Indonesia'],
            ['name' => 'Kris Kos', 'address' => 'Jl. Nelayan No. 4, Banjar Canggu, 80361 Canggu, Indonesia'],
            ['name' => "Duana's Homestay", 'address' => 'Jalan Sriwedari No.21 A, 80571 Ubud, Indonesia'],
            ['name' => 'Besakih Homestay & Villa', 'address' => 'Jalan Raya Besakih, 80863 Besakih, Indonesia'],
            ['name' => 'Kedonganan Beach Villas', 'address' => '33 Jalan Segara Wangi, 80361 Jimbaran, Indonesia'],
            ['name' => 'Aruni Bali Jimbaran Boutique Villa', 'address' => 'Jalan Karang Mas Sejahtera No. 88, 80361 Jimbaran, Indonesia'],
            ['name' => 'Poedja Villa Jimbaran', 'address' => 'Jalan Raya Uluwatu, no.124 Jimbaran, 80361 Jimbaran, Indonesia'],
            ['name' => 'Villa Puri Royan Jimbaran', 'address' => '25, Jl. Pantai Sari No.25, Jimbaran, Bali, 80361 Jimbaran, Indonesia'],
            ['name' => 'Uli Wood Villa, Jimbaran BALI - near GWK', 'address' => 'Jalan Raya Uluwatu, Gg. Mantili, 80361 Jimbaran, Indonesia'],
            ['name' => 'Juada Garden', 'address' => 'Jl. Raya Seminyak No. 501, 80361 Seminyak, Indonesia'],
            ['name' => 'Bali Komang Guest House Sanur', 'address' => 'Jl. Sekuta Gg. Bambu, Sanur, Kec. Denpasar Selatan, Indonesia'],
            ['name' => 'Puri Kobot', 'address' => 'Br. Pengosekan Kaja Mas Ubud, 80571 Ubud, Indonesia'],
            ['name' => 'Asta House', 'address' => 'Jl. Segara Merta Gg. Jepun No.26, 80361 Kuta, Indonesia'],
        ];
        $id = 1;
        foreach ($boardingHousesData as $i => $data) {
            if ($i == 11)
                $id = 2;
            if ($i == 22)
                $id = 3;
            $boardingHouse = BoardingHouse::create([
                'name' => $data['name'],
                'thumbnail' => fake()->imageUrl(640, 480, 'house', true, 'kost'),
                'description' => fake()->paragraph(4),
                'address' => $data['address'],
                'city_id' => fake()->randomElement($cityIds),
                'category_id' => fake()->randomElement($categoryIds),
            ]);
            // Tambahkan 3 kamar untuk setiap boarding house
            Room::factory()->count(3)->create([
                'boarding_house_id' => $boardingHouse->id,
            ]);
        }
    }
}
