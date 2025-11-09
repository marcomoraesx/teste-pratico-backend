<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'throttle:server-error'])->group(function () {
    Route::middleware(['throttle:auth'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/login', function () {
                return response()->json(['message' => 'Ok']);
            });
        });
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        // TO DO
    });
});
