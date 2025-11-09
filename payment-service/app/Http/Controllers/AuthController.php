<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $auth_service;

    function __construct(AuthService $auth_service)
    {
        $this->auth_service = $auth_service;
    }

    function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        [$user, $token] = $this->auth_service->login($data);
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        $this->auth_service->logout($token);
        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }
}
