<?php

namespace Tests\Feature;

use App\Services\GatewayService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Mockery;

beforeEach(function () {
    $this->withoutMiddleware();
});

afterEach(function () {
    Mockery::close();
});

test('controller: change_priority success returns 200', function () {
    $mock = Mockery::mock(GatewayService::class);
    $mock->shouldReceive('change_priority')->once()->with(4, 2);
    $this->app->instance(GatewayService::class, $mock);
    $payload = ['priority' => 2];
    $response = $this->patchJson('/api/gateways/4/change-priority', $payload);
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Updated successful']);
});

test('controller: change_priority error when not found returns 400', function () {
    $mock = Mockery::mock(GatewayService::class);
    $mock->shouldReceive('change_priority')->once()->with(404, 1)->andThrow(new BadRequestException('Gateway not found.'));
    $this->app->instance(GatewayService::class, $mock);
    $payload = ['priority' => 1];
    $response = $this->patchJson('/api/gateways/404/change-priority', $payload);
    $response->assertStatus(400);
});

test('controller: activate_or_deactivate success returns 200', function () {
    $mock = Mockery::mock(GatewayService::class);
    $mock->shouldReceive('activate_or_deactivate')->once()->with(7, true);
    $this->app->instance(GatewayService::class, $mock);
    $payload = ['activate' => true];
    $response = $this->patchJson('/api/gateways/7/activate-or-deactivate', $payload);
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Updated successful']);
});

test('controller: activate_or_deactivate error when already in status returns 400', function () {
    $mock = Mockery::mock(GatewayService::class);
    $mock->shouldReceive('activate_or_deactivate')->once()->with(10, false)->andThrow(new BadRequestException('Gateway is already in that status.'));
    $this->app->instance(GatewayService::class, $mock);
    $payload = ['activate' => false];
    $response = $this->patchJson('/api/gateways/10/activate-or-deactivate', $payload);
    $response->assertStatus(400);
});
