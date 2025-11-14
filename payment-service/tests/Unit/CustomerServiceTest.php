<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new \App\Services\CustomerService();
});

test('service: detail success returns the customer', function () {
    $customer = Customer::factory()->create();
    $found = $this->service->detail($customer->id);
    expect($found->id)->toBe($customer->id);
});

test('service: detail error when not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->detail(99999);
});

test('service: list success returns paginator', function () {
    Customer::factory()->count(3)->create();
    $p = $this->service->list(2, 'asc');
    expect($p)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($p->total())->toBe(3);
});

test('service: list error with invalid order throws InvalidArgumentException', function () {
    $this->expectException(\InvalidArgumentException::class);
    $this->service->list(15, 'INVALID_DIRECTION');
});
