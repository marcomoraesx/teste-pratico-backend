<?php

namespace Tests\Feature;

use App\Services\SaleService;
use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Mockery;

beforeEach(function () {
    $this->withoutMiddleware();
});

afterEach(function () {
    Mockery::close();
});

test('controller: register success returns 201 and sale', function () {
    $sale = Sale::factory()->make(['id' => 1, 'status' => 'COMPLETED']);
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('register')->once()->andReturn($sale);
    $this->app->instance(SaleService::class, $mock);
    $payload = [
        'customer_id' => 1,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'payment' => ['payment_method' => 'CREDIT_CARD', 'card_number' => '1111222233334444', 'cvv' => '123'],
        'discount_amount' => 0,
    ];
    $response = $this->postJson('/api/sales', $payload);
    $response->assertStatus(201)->assertJsonStructure(['message', 'sale']);
});

test('controller: register error returns validation problem', function () {
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('register')
        ->once()
        ->andThrow(ValidationException::withMessages(['customer_id' => ['Customer not found.']]));
    $this->app->instance(SaleService::class, $mock);
    $payload = [
        'customer_id' => 999,
        'items' => [['product_id' => 1, 'quantity' => 1]],
        'payment' => ['payment_method' => 'CREDIT_CARD', 'card_number' => '1111222233334444', 'cvv' => '123'],
        'discount_amount' => 0,
    ];
    $response = $this->postJson('/api/sales', $payload);
    $response
        ->assertStatus(422)
        ->assertJsonPath('errors.customer_id.0', 'Customer not found.');
});

test('controller: detail success returns sale', function () {
    $sale = Sale::factory()->make(['id' => 5]);
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('detail')->once()->with(5)->andReturn($sale);
    $this->app->instance(SaleService::class, $mock);
    $response = $this->getJson('/api/sales/5');
    $response->assertStatus(200)->assertJsonStructure(['sale']);
});

test('controller: detail error when not found returns 400', function () {
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('detail')->once()->with(99)->andThrow(new BadRequestException('Sale not found.'));
    $this->app->instance(SaleService::class, $mock);
    $response = $this->getJson('/api/sales/99');
    $response->assertStatus(400);
});

test('controller: list success returns paginated sales', function () {
    $items = collect([
        ['id' => 1, 'net_amount' => 10],
        ['id' => 2, 'net_amount' => 20],
    ]);
    $paginator = new LengthAwarePaginator($items, 2, 15, 1);
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('list')->once()->with(15, 'asc')->andReturn($paginator);
    $this->app->instance(SaleService::class, $mock);
    $response = $this->getJson('/api/sales/list?page=1');
    $response->assertStatus(200)->assertJsonStructure(['sales']);
});

test('controller: list error returns 400 on service failure', function () {
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('list')->once()->andThrow(new BadRequestException('Bad request'));
    $this->app->instance(SaleService::class, $mock);
    $response = $this->getJson('/api/sales/list?page=1');
    $response->assertStatus(400);
});

test('controller: refund success returns 200', function () {
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('refund')->once()->with(3);
    $this->app->instance(SaleService::class, $mock);
    $response = $this->patchJson('/api/sales/3/refund');
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Refunded successful']);
});

test('controller: refund error returns 400 when not refundable', function () {
    $mock = Mockery::mock(SaleService::class);
    $mock->shouldReceive('refund')->once()->with(999)->andThrow(new BadRequestException('This sale is non-refundable.'));
    $this->app->instance(SaleService::class, $mock);
    $response = $this->patchJson('/api/sales/999/refund');
    $response->assertStatus(400);
});
