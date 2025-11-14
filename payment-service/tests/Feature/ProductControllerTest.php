<?php

namespace Tests\Feature;

use App\Services\ProductService;
use App\Models\Product;
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

test('controller: register success returns 201 and product', function () {
    $product = Product::factory()->make(['id' => 1]);
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('register')->once()->andReturn($product);
    $this->app->instance(ProductService::class, $mock);
    $payload = [
        'bar_code' => '1234567890123',
        'name' => 'Product A',
        'description' => 'A product',
        'price' => 10.5,
        'stock' => 5,
    ];
    $response = $this->postJson('/api/products', $payload);
    $response->assertStatus(201)->assertJsonStructure(['message', 'product']);
});

test('controller: register error returns validation problem', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('register')
        ->once()
        ->andThrow(ValidationException::withMessages(['bar_code' => ['The bar code has already been taken.']]));
    $this->app->instance(ProductService::class, $mock);
    $payload = [
        'bar_code' => '1234567890123',
        'name' => 'Product A',
        'description' => 'A product',
        'price' => 10.5,
        'stock' => 5,
    ];
    $response = $this->postJson('/api/products', $payload);
    $response
        ->assertStatus(422)
        ->assertJsonPath('errors.bar_code.0', 'The bar code has already been taken.');
});

test('controller: view success returns product', function () {
    $product = Product::factory()->make(['id' => 5, 'bar_code' => '1234567890123']);
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('view')->once()->with(5)->andReturn($product);
    $this->app->instance(ProductService::class, $mock);
    $response = $this->getJson('/api/products/5');
    $response->assertStatus(200)->assertJsonStructure(['product']);
});

test('controller: view error when not found returns 400', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('view')->once()->with(99)->andThrow(new BadRequestException('Product not found.'));
    $this->app->instance(ProductService::class, $mock);
    $response = $this->getJson('/api/products/99');
    $response->assertStatus(400);
});

test('controller: list success returns paginated products', function () {
    $items = collect([
        ['id' => 1, 'name' => 'A', 'bar_code' => '1111111111111'],
        ['id' => 2, 'name' => 'B', 'bar_code' => '2222222222222'],
    ]);
    $paginator = new LengthAwarePaginator($items, 2, 15, 1);
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('list')->once()->with(15, 'asc')->andReturn($paginator);
    $this->app->instance(ProductService::class, $mock);
    $response = $this->getJson('/api/products/list?page=1&per_page=15&order=asc');
    $response->assertStatus(200)->assertJsonStructure(['products']);
});

test('controller: list error returns 400 on service failure', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('list')->once()->andThrow(new BadRequestException('Bad request'));
    $this->app->instance(ProductService::class, $mock);
    $response = $this->getJson('/api/products/list?page=1&per_page=15&order=asc');
    $response->assertStatus(400);
});

test('controller: update success returns 200', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('update')->once()->with(3, Mockery::type('array'));
    $this->app->instance(ProductService::class, $mock);
    $payload = [
        'name' => 'New name',
        'is_active' => true,
        'price' => 2.0,
        'stock' => 10
    ];
    $response = $this->patchJson('/api/products/3', $payload);
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Updated successful']);
});

test('controller: update error returns 400 when service throws', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('update')->once()->with(999, Mockery::type('array'))->andThrow(new BadRequestException('Product not found.'));
    $this->app->instance(ProductService::class, $mock);
    $payload = [
        'name' => 'New name',
        'is_active' => true,
        'price' => 2.0,
        'stock' => 10
    ];
    $response = $this->patchJson('/api/products/999', $payload);
    $response->assertStatus(400);
});

test('controller: delete success returns 200', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('delete')->once()->with(7);
    $this->app->instance(ProductService::class, $mock);
    $response = $this->deleteJson('/api/products/7');
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Deleted successful']);
});

test('controller: delete error returns 400 when not found', function () {
    $mock = Mockery::mock(ProductService::class);
    $mock->shouldReceive('delete')->once()->with(777)->andThrow(new BadRequestException('Product not found.'));
    $this->app->instance(ProductService::class, $mock);
    $response = $this->deleteJson('/api/products/777');
    $response->assertStatus(400);
});
