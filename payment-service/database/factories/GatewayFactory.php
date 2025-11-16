<?php

namespace Database\Factories;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gateway>
 */
class GatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_name' => fake()->unique()->lexify('?????GatewayProvider'),
            'name' => fake()->unique()->company(),
            'is_active' => fake()->boolean(85),
            'priority' => fake()->randomElement(Priority::values()),
        ];
    }
}
