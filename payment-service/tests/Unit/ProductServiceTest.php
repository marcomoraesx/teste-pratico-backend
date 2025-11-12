<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new \App\Services\ProductService();
});

test('service: register success creates product and returns product', function () {
    $data = [
        'bar_code' => '1234567890123',
        'name' => 'Prod Register',
        'description' => 'Desc',
        'price' => 99.9,
        'stock' => 10,
    ];
    $product = $this->service->register($data);
    $this->assertDatabaseHas('products', ['bar_code' => '1234567890123']);
    expect($product)->toBeInstanceOf(Product::class);
});

test('service: register error when duplicate bar_code throws validation', function () {
    Product::factory()->create(['bar_code' => '9999999999999']);
    $this->expectException(ValidationException::class);
    $this->service->register([
        'bar_code' => '9999999999999',
        'name' => 'Dup',
        'description' => 'X',
        'price' => 1,
        'stock' => 1,
    ]);
});

test('service: view success returns the product', function () {
    $product = Product::factory()->create();
    $found = $this->service->view($product->id);
    expect($found->id)->toBe($product->id);
});

test('service: view error when not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->view(99999);
});

test('service: list success returns paginator', function () {
    Product::factory()->count(3)->create();
    $p = $this->service->list(2, 'asc');
    expect($p)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($p->total())->toBe(3);
});

test('service: list error with invalid order throws InvalidArgumentException', function () {
    $this->expectException(\InvalidArgumentException::class);
    $this->service->list(15, 'INVALID_DIRECTION');
});

test('service: update success modifies the product name', function () {
    $product = Product::factory()->create(['name' => 'Old']);
    $this->service->update($product->id, ['name' => 'New', 'description' => $product->description, 'is_active' => $product->is_active, 'price' => $product->price, 'stock' => $product->stock]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New']);
});

test('service: update error when product not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->update(123456, ['name' => 'X']);
});

test('service: delete success removes the product', function () {
    $product = Product::factory()->create();
    $this->service->delete($product->id);
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('service: delete error when product not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->delete(555555);
});
