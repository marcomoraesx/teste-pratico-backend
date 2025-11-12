<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListUsersRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    private UserService $user_service;

    function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
    }

    function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        [$user, $token] = $this->user_service->register($data);
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    function view(string $user_id): JsonResponse
    {
        $user = $this->user_service->view(intval($user_id));
        return response()->json([
            'user' => $user,
        ], 200);
    }

    function list(ListUsersRequest $request): JsonResponse
    {
        $data = $request->validated();
        $per_page = $data['per_page'] ?? 15;
        $order = $data['order'] ?? 'asc';
        $users = $this->user_service->list($per_page, $order);
        return response()->json([
            'users' => $users
        ], 200);
    }

    function update(string $user_id, UpdateUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->user_service->update(intval($user_id), $data);
        return response()->json([
            'message' => 'Updated successful',
        ], 200);
    }

    function delete(string $user_id): JsonResponse
    {
        $this->user_service->delete(intval($user_id));
        return response()->json([
            'message' => 'Deleted successful',
        ], 200);
    }
}
