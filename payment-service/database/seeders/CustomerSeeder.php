<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::factory()->create([
            'document' => 12345678901,
            'name' => 'João da Silva',
            'email' => 'joao.silva@example.com',
            'is_active' => true,
        ]);
        Customer::factory()->create([
            'document' => 98765432100,
            'name' => 'Maria Oliveira',
            'email' => 'maria.oliveira@example.com',
            'is_active' => false,
        ]);
        Customer::factory(8)->create();
    }
}
