<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
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
    Route::prefix('product')
        ->middleware(['auth:sanctum', 'roles:ADMIN,MANAGER,FINANCE'])
        ->group(function () {
            Route::post('', [ProductController::class, 'register'])->middleware('permissions:product.create');
            Route::get('/list', [ProductController::class, 'list'])->middleware('permissions:product.list');
            Route::get('/{product_id}', [ProductController::class, 'view'])->middleware('permissions:product.view');
            Route::patch('/{product_id}', [ProductController::class, 'update'])->middleware('permissions:product.update');
            Route::delete('/{product_id}', [ProductController::class, 'delete'])->middleware('permissions:product.delete');
        });
});
