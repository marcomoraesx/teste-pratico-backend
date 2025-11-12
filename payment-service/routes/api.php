<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'throttle:server-error'])->group(function () {
    Route::prefix('auth')
        ->middleware('throttle:auth')
        ->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    Route::prefix('user')
        ->middleware(['auth:sanctum', 'roles:ADMIN,MANAGER'])
        ->group(function () {
            Route::post('', [UserController::class, 'register'])->middleware('permissions:user.create');
            Route::get('/list', [UserController::class, 'list'])->middleware('permissions:user.list');
            Route::get('/{user_id}', [UserController::class, 'view'])->middleware('permissions:user.view');
            Route::patch('/{user_id}', [UserController::class, 'update'])->middleware('permissions:user.update');
            Route::delete('/{user_id}', [UserController::class, 'delete'])->middleware('permissions:user.delete');
        });
});
