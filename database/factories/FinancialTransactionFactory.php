<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                return User::factory()->create()->id;
            },
            'transaction_type' => $this->faker->randomElement(['DEPOSIT', 'WITHDRAWAL', 'INVESTMENT', 'DIVIDENDS']),
            'transaction_date' => $this->faker->date,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
