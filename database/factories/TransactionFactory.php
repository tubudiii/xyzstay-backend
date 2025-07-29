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
    public function definition(): array
    {
        $startDate = fake()->dateTimeThisMonth();
        return [
            'name' => fake()->sentence(3),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'start_date' => $startDate,
            'end_date' => Carbon::createFromDate($startDate)->addDays(fake()->numberBetween(1, 30)),
            'code' => fake()->unique()->bothify('XYZ-#####'),
            'payment_status' => fake()->randomElement(['waiting', 'approved', 'canceled']),
            'payment_method' => fake()->randomElement(['down_payment', 'full_payment']),
        ];
    }
}
