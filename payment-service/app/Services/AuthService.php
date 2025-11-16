<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthService
{
    public function login($data): array
    {
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'some' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return [$user, $token];
    }

    public function logout(string | null $token): void
    {
        if (!$token) throw new BadRequestException('Token not provided');
        $access_token = PersonalAccessToken::findToken($token);
        if (!$access_token) throw new BadRequestException('Token is not valid');
        $access_token->delete();
    }
}
