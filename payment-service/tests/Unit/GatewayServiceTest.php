<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Gateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Enums\Priority;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new \App\Services\GatewayService();
});

test('service: change_priority success updates priority', function () {
    $gateway = Gateway::factory()->create(['priority' => Priority::values()[0]]);
    $this->service->change_priority($gateway->id, Priority::values()[1]);
    $this->assertDatabaseHas('gateways', ['id' => $gateway->id, 'priority' => Priority::values()[1]]);
});

test('service: change_priority error when not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->change_priority(999999, Priority::values()[0]);
});

test('service: activate_or_deactivate success toggles status', function () {
    $gateway = Gateway::factory()->create(['is_active' => false]);
    $this->service->activate_or_deactivate($gateway->id, true);
    $this->assertDatabaseHas('gateways', ['id' => $gateway->id, 'is_active' => true]);
});

test('service: activate_or_deactivate error when already in status throws BadRequestException', function () {
    $gateway = Gateway::factory()->create(['is_active' => true]);
    $this->expectException(BadRequestException::class);
    $this->service->activate_or_deactivate($gateway->id, true);
});
