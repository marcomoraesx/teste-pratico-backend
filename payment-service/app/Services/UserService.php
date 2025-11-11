<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UserService
{
    public function register($data): array
    {
        return DB::transaction(function () use ($data) {
            $user_exists = User::where('email', $data['email'])->exists();
            if ($user_exists) {
                ValidationException::withMessages([
                    'email' => ['The email has already been taken.'],
                ]);
            }
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole($data['role']);
            $token = $user->createToken('api-token')->plainTextToken;
            return [$user, $token];
        });
    }

    public function view(int $user_id): User
    {
        $user = User::find($user_id);
        if (!$user) throw new BadRequestException('User not found.');
        return $user;
    }

    public function list(int $per_page, string $order): LengthAwarePaginator
    {
        return User::query()
            ->orderBy('id', $order)
            ->paginate($per_page);
    }

    public function update(int $user_id, $data): void
    {
        DB::transaction(function () use ($user_id, $data) {
            $user = User::find($user_id);
            if (!$user) throw new BadRequestException('User not found.');
            $user->update([
                'name' => $data['name'],
            ]);
        });
    }

    public function delete(int $user_id): void
    {
        DB::transaction(function () use ($user_id) {
            $user = User::find($user_id);
            if (!$user) throw new BadRequestException('User not found.');
            $user->delete();
        });
    }
}
