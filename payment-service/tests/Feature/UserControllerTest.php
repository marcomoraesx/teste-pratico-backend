<?php

namespace Tests\Feature;

use App\Services\UserService;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Mockery;

beforeEach(function () {
    $this->withoutMiddleware();
    Role::firstOrCreate(['name' => 'USER']);
});

afterEach(function () {
    Mockery::close();
});

test('controller: register success returns 201 and token', function () {
    $user = User::factory()->make(['id' => 1]);
    $token = 'plain-token';
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('register')->once()->andReturn([$user, $token]);
    $this->app->instance(UserService::class, $mock);
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret',
        'password_confirmation' => 'secret',
        'role' => 'USER',
    ];
    $response = $this->postJson('/api/user', $payload);
    $response->assertStatus(201)->assertJsonStructure(['message', 'user', 'token']);
});

test('controller: register error returns validation problem', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('register')
        ->once()
        ->andThrow(ValidationException::withMessages(['email' => ['The email has already been taken.']]));
    $this->app->instance(UserService::class, $mock);
    $payload = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret',
        'password_confirmation' => 'secret',
        'role' => 'USER',
    ];
    $response = $this->postJson('/api/user', $payload);
    $response
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email has already been taken.');
});

test('controller: view success returns user', function () {
    $user = User::factory()->make(['id' => 5, 'email' => 'u5@example.com']);
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('view')->once()->with(5)->andReturn($user);
    $this->app->instance(UserService::class, $mock);
    $response = $this->getJson('/api/user/5');
    $response->assertStatus(200)->assertJsonStructure(['user']);
});

test('controller: view error when not found returns 400', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('view')->once()->with(99)->andThrow(new BadRequestException('User not found.'));
    $this->app->instance(UserService::class, $mock);
    $response = $this->getJson('/api/user/99');
    $response->assertStatus(400);
});

test('controller: list success returns paginated users', function () {
    $items = collect([
        ['id' => 1, 'name' => 'A', 'email' => 'a@example.com'],
        ['id' => 2, 'name' => 'B', 'email' => 'b@example.com'],
    ]);
    $paginator = new LengthAwarePaginator($items, 2, 15, 1);
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('list')->once()->with(15, 'asc')->andReturn($paginator);
    $this->app->instance(UserService::class, $mock);
    $response = $this->getJson('/api/user/list');
    $response->assertStatus(200)->assertJsonStructure(['users']);
});

test('controller: list error returns 400 on service failure', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('list')->once()->andThrow(new BadRequestException('Bad request'));
    $this->app->instance(UserService::class, $mock);
    $response = $this->getJson('/api/user/list');
    $response->assertStatus(400);
});

test('controller: update success returns 200', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('update')->once()->with(3, Mockery::type('array'));
    $this->app->instance(UserService::class, $mock);
    $payload = ['name' => 'New name'];
    $response = $this->patchJson('/api/user/3', $payload);
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Updated successful']);
});

test('controller: update error returns 400 when service throws', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('update')->once()->with(999, Mockery::type('array'))->andThrow(new BadRequestException('User not found.'));
    $this->app->instance(UserService::class, $mock);
    $payload = ['name' => 'New name'];
    $response = $this->patchJson('/api/user/999', $payload);
    $response->assertStatus(400);
});

test('controller: delete success returns 200', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('delete')->once()->with(7);
    $this->app->instance(UserService::class, $mock);
    $response = $this->deleteJson('/api/user/7');
    $response->assertStatus(200)->assertJsonFragment(['message' => 'Deleted successful']);
});

test('controller: delete error returns 400 when not found', function () {
    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('delete')->once()->with(777)->andThrow(new BadRequestException('User not found.'));
    $this->app->instance(UserService::class, $mock);
    $response = $this->deleteJson('/api/user/777');
    $response->assertStatus(400);
});
