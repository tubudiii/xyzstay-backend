<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $category = [
            'Kost Putra',
            'Kost Putri',
            'Kost Campur',
            'Villa',
            'Homestay',
        ];

        $name = $this->faker->unique()->randomElement($category);

        return [
            'image' => $this->faker->imageUrl(640, 480, 'real-estate', true, 'kost'),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
