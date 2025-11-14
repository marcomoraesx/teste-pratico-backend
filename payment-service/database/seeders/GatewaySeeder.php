<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gateway::factory()->create([
            'class_name' => 'PagSeguroGatewayProvider',
            'name' => 'PagSeguro',
            'is_active' => true,
            'priority' => Priority::MEDIUM,
        ]);
        Gateway::factory()->create([
            'class_name' => 'MercadoPagoGatewayProvider',
            'name' => 'MercadoPago',
            'is_active' => true,
            'priority' => Priority::HIGH,
        ]);
        Gateway::factory(8)->create();
    }
}
