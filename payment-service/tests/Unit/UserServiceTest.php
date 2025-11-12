<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new \App\Services\UserService();
});

test('service: register success creates user and returns token', function () {
    Role::create(['name' => 'USER']);
    [$user, $token] = $this->service->register([
        'name' => 'Register Name',
        'email' => 'reg@example.com',
        'password' => 'secret',
        'role' => 'USER',
    ]);
    $this->assertDatabaseHas('users', ['email' => 'reg@example.com']);
    expect($user)->toBeInstanceOf(User::class);
    expect(is_string($token))->toBeTrue();
});

test('service: register error when duplicate email throws validation', function () {
    Role::create(['name' => 'USER']);
    User::factory()->create(['email' => 'dup@example.com']);
    $this->expectException(ValidationException::class);
    $this->service->register([
        'name' => 'Dup',
        'email' => 'dup@example.com',
        'password' => 'secret',
        'role' => 'USER',
    ]);
});

test('service: view success returns the user', function () {
    $user = User::factory()->create();
    $found = $this->service->view($user->id);
    expect($found->id)->toBe($user->id);
});

test('service: view error when not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->view(99999);
});

test('service: list success returns paginator', function () {
    User::factory()->count(3)->create();
    $p = $this->service->list(2, 'asc');
    expect($p)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($p->total())->toBe(3);
});

test('service: list error with invalid order throws InvalidArgumentException', function () {
    $this->expectException(\InvalidArgumentException::class);
    $this->service->list(15, 'INVALID_DIRECTION');
});

test('service: update success modifies the user name', function () {
    $user = User::factory()->create(['name' => 'Old']);
    $this->service->update($user->id, ['name' => 'New']);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New']);
});

test('service: update error when user not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->update(123456, ['name' => 'X']);
});

test('service: delete success removes the user', function () {
    $user = User::factory()->create();
    $this->service->delete($user->id);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('service: delete error when user not found throws BadRequestException', function () {
    $this->expectException(BadRequestException::class);
    $this->service->delete(555555);
});
