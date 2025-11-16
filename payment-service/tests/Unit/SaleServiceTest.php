<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Enums\PaymentMethod;
use App\Enums\Status;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->paymentMock = Mockery::mock(\App\Services\PaymentService::class);
    $this->service = new \App\Services\SaleService($this->paymentMock);
});

test('service: register success with credit card creates sale and transaction', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
    $gateway = Gateway::factory()->create(['class_name' => 'TestGatewayProvider']);
    $this->paymentMock->shouldReceive('handle')
        ->once()
        ->andReturn(['TestGatewayProvider', 'ext-123']);
    $payload = [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payment' => ['payment_method' => PaymentMethod::CREDIT_CARD->value, 'card_number' => '1111222233334444', 'cvv' => '123', 'name' => 'Cust', 'email' => 'c@example.com'],
        'discount_amount' => 0,
    ];
    Transaction::factory()->create([
        'gateway_id' => $gateway->id,
        'external_transaction_id' => 'ext-999',
        'status' => Status::COMPLETED,
    ]);
    $sale = $this->service->register($payload);
    $this->assertDatabaseHas('sales', ['id' => $sale->id, 'customer_id' => $customer->id]);
    $this->assertDatabaseHas('transactions', ['external_transaction_id' => 'ext-123']);
});

test('service: register error when customer not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $payload = [
        'customer_id' => 999999,
        'items' => [],
        'payment' => ['payment_method' => PaymentMethod::CREDIT_CARD->value, 'card_number' => '1111222233334444', 'cvv' => '123'],
        'discount_amount' => 0,
    ];
    $this->service->register($payload);
});

test('service: detail success returns the sale', function () {
    $sale = Sale::factory()->create();
    $found = $this->service->detail($sale->id);
    expect($found->id)->toBe($sale->id);
});

test('service: detail error when not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->detail(99999);
});

test('service: list success returns paginator', function () {
    Sale::factory()->count(3)->create();
    $p = $this->service->list(2, 'asc');
    expect($p)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($p->total())->toBe(3);
});

test('service: list error with invalid order throws InvalidArgumentException', function () {
    $this->expectException(\InvalidArgumentException::class);
    $this->service->list(15, 'INVALID_DIRECTION');
});

test('service: refund success refunds card sale and transaction', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
    $gateway = Gateway::factory()->create(['class_name' => 'TestGatewayProvider']);
    $this->paymentMock->shouldReceive('handle')
        ->once()
        ->andReturn(['TestGatewayProvider', 'ext-123']);
    $payload = [
        'customer_id' => $customer->id,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'payment' => ['payment_method' => PaymentMethod::CREDIT_CARD->value, 'card_number' => '1111222233334444', 'cvv' => '123', 'name' => 'Cust', 'email' => 'c@example.com'],
        'discount_amount' => 0,
    ];
    $sale = $this->service->register($payload);
    $transaction = $sale->transaction;
    $fakeProvider = new class {
        public function refund(string $transaction_id): bool
        {
            return true;
        }
    };
    $this->app->instance("\App\Services\Providers\\{$gateway->class_name}", $fakeProvider);
    $this->service->refund($sale->id);
    $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => Status::REFUNDED]);
    $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => Status::REFUNDED]);
});

test('service: refund error when sale not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->refund(555555);
});
