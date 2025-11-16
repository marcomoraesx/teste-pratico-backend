<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'throttle:server-error'])->group(function () {
    Route::prefix('auth')
        ->middleware('throttle:auth')
        ->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    Route::prefix('users')
        ->middleware(['auth:sanctum', 'roles:ADMIN,MANAGER'])
        ->group(function () {
            Route::post('', [UserController::class, 'register'])->middleware('permissions:user.create');
            Route::get('/list', [UserController::class, 'list'])->middleware('permissions:user.list');
            Route::get('/{user_id}', [UserController::class, 'view'])->middleware('permissions:user.view');
            Route::patch('/{user_id}', [UserController::class, 'update'])->middleware('permissions:user.update');
            Route::delete('/{user_id}', [UserController::class, 'delete'])->middleware('permissions:user.delete');
        });
    Route::prefix('products')
        ->middleware(['auth:sanctum', 'roles:ADMIN,MANAGER,FINANCE'])
        ->group(function () {
            Route::post('', [ProductController::class, 'register'])->middleware('permissions:product.create');
            Route::get('/list', [ProductController::class, 'list'])->middleware('permissions:product.list');
            Route::get('/{product_id}', [ProductController::class, 'view'])->middleware('permissions:product.view');
            Route::patch('/{product_id}', [ProductController::class, 'update'])->middleware('permissions:product.update');
            Route::delete('/{product_id}', [ProductController::class, 'delete'])->middleware('permissions:product.delete');
        });
    Route::prefix('gateways')
        ->middleware(['auth:sanctum', 'roles:USER,ADMIN'])
        ->group(function () {
            Route::patch('/{gateway_id}/change-priority', [GatewayController::class, 'change_priority'])->middleware('permissions:gateway.change-priority');
            Route::patch('/{gateway_id}/activate-or-deactivate', [GatewayController::class, 'activate_or_deactivate'])->middleware('permissions:gateway.activate,gateway.deactivate');
        });
    Route::prefix('customers')
        ->middleware(['auth:sanctum', 'roles:ADMIN,USER'])
        ->group(function () {
            Route::get('/list', [CustomerController::class, 'list'])->middleware('permissions:customer.list');
            Route::get('/{customer_id}', [CustomerController::class, 'detail'])->middleware('permissions:customer.detail');
        });
    Route::prefix('sales')
        ->middleware(['auth:sanctum', 'roles:ADMIN,FINANCE,USER'])
        ->group(function () {
            Route::post('/', [SaleController::class, 'register'])->withoutMiddleware(['auth:sanctum', 'roles:ADMIN,FINANCE,USER']);
            Route::get('/list', [SaleController::class, 'list'])->middleware('permissions:sale.list');
            Route::get('/{sale_id}', [SaleController::class, 'detail'])->middleware('permissions:sale.detail');
            Route::patch('/{sale_id}/refund', [SaleController::class, 'refund'])->middleware('permissions:sale.refund');
        });
});
