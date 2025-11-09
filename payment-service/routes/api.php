<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'throttle:server-error'])->group(function () {
    Route::middleware(['throttle:auth'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
            Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        // TO DO
    });
});
