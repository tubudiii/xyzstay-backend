<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // public function definition(): array
    // {
    // $startDate = fake()->dateTimeThisMonth();
    // $endDate = (clone $startDate)->modify('+' . fake()->numberBetween(1, 30) . ' days');
    // $totalDays = (new Carbon($startDate))->diffInDays($endDate) + 1;
    // $pricePerDay = fake()->numberBetween(10000, 50000);
    // $totalPrice = $totalDays * $pricePerDay;
    // $fee = $totalPrice * 0.1;

    // return [
    //     'name' => fake()->sentence(3),
    //     'email' => fake()->unique()->safeEmail(),
    //     'phone_number' => fake()->phoneNumber(),
    //     'start_date' => $startDate,
    //     'end_date' => $endDate,
    //     'code' => fake()->unique()->bothify('XYZ-#####'),
    //     'payment_status' => fake()->randomElement(['waiting', 'approved', 'canceled']),
    // 'payment_method' => fake()->randomElement(['down_payment', 'full_payment']),
    // 'price_per_day' => $pricePerDay,
    // 'total_days' => $totalDays,
    // 'fee' => $fee,
    // 'total_price' => $totalPrice,
    // ];
    // }
}
