<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bar_code' => fake()->unique()->numberBetween(1000000000000, 9999999999999),
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(8),
            'is_active' => fake()->boolean(90),
            'price' => fake()->randomFloat(2, 10, 2000),
            'stock' => fake()->numberBetween(0, 500),
        ];
    }
}
