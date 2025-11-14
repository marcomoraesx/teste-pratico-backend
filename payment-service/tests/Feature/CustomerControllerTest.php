<?php

namespace Tests\Feature;

use App\Services\CustomerService;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Mockery;

beforeEach(function () {
    $this->withoutMiddleware();
});

afterEach(function () {
    Mockery::close();
});

test('controller: detail success returns customer', function () {
    $customer = Customer::factory()->make(['id' => 5, 'email' => 'c5@example.com']);
    $mock = Mockery::mock(CustomerService::class);
    $mock->shouldReceive('detail')->once()->with(5)->andReturn($customer);
    $this->app->instance(CustomerService::class, $mock);
    $response = $this->getJson('/api/customers/5');
    $response->assertStatus(200)->assertJsonStructure(['customer']);
});

test('controller: detail error when not found returns 400', function () {
    $mock = Mockery::mock(CustomerService::class);
    $mock->shouldReceive('detail')->once()->with(99)->andThrow(new BadRequestException('Customer not found.'));
    $this->app->instance(CustomerService::class, $mock);
    $response = $this->getJson('/api/customers/99');
    $response->assertStatus(400);
});

test('controller: list success returns paginated customers', function () {
    $items = collect([
        ['id' => 1, 'name' => 'A', 'email' => 'a@example.com'],
        ['id' => 2, 'name' => 'B', 'email' => 'b@example.com'],
    ]);
    $paginator = new LengthAwarePaginator($items, 2, 15, 1);
    $mock = Mockery::mock(CustomerService::class);
    $mock->shouldReceive('list')->once()->with(15, 'asc')->andReturn($paginator);
    $this->app->instance(CustomerService::class, $mock);
    $response = $this->getJson('/api/customers/list?page=1');
    $response->assertStatus(200)->assertJsonStructure(['customers']);
});

test('controller: list error returns 400 on service failure', function () {
    $mock = Mockery::mock(CustomerService::class);
    $mock->shouldReceive('list')->once()->andThrow(new BadRequestException('Bad request'));
    $this->app->instance(CustomerService::class, $mock);
    $response = $this->getJson('/api/customers/list?page=1');
    $response->assertStatus(400);
});
