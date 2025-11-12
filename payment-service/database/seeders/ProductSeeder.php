<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->create([
            'bar_code' => 1234567890123,
            'name' => 'Notebook Gamer',
            'description' => 'Notebook com RTX e processador Ryzen.',
            'is_active' => true,
            'price' => 8999.90,
            'stock' => 12,
        ]);
        Product::factory()->create([
            'bar_code' => 1234567890124,
            'name' => 'Teclado Mecânico RGB',
            'description' => 'Switches vermelhos e iluminação personalizável.',
            'is_active' => true,
            'price' => 399.99,
            'stock' => 50,
        ]);
        Product::factory(30)->create();
    }
}
