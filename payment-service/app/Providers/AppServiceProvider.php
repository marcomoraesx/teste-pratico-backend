<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(50)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(10),
                Limit::perMinute(5)->by($request->input('email') ?: $request->ip()),
            ];
        });
        RateLimiter::for('server-error', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->after(function (Response $response) {
                    return $response->isServerError();
                });
        });
    }
}
