<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Customer;
use App\Models\Gateway;
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
        return [
            'customer_id' => Customer::factory(),
            'gateway_id' => Gateway::factory(),
            'external_transaction_id' => fake()->uuid(),
            'status' => fake()->randomElement(Status::values()),
            'total_amount' => fake()->randomFloat(2, 10, 1000000),
            'last_digits_card' => fake()->randomNumber(4),
        ];
    }
}
