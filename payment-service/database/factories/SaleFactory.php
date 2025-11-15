<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\Status;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
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
            'transaction_id' => Transaction::factory(),
            'payment_method' => fake()->randomElement(PaymentMethod::values()),
            'total_amount' => fake()->randomFloat(2, 10, 2000000),
            'discount_amount' => fake()->randomFloat(2, 10, 500000),
            'net_amount' => fake()->randomFloat(2, 10, 1000000),
            'status' => fake()->randomElement(Status::values()),
        ];
    }
}
