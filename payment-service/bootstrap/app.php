<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', App\Http\Middleware\ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
        $exceptions->respond(function (Response $response) {
            switch ($response->getStatusCode()) {
                case 422:
                    return response()->json(
                        [
                            'message' => json_decode($response->getContent())->message,
                            'errors' => json_decode($response->getContent())->errors,
                            'code' => $response->getStatusCode()
                        ],
                        $response->getStatusCode()
                    );
                case 429:
                    return response()->json(
                        [
                            'message' => 'Too many requests. Please try again in ' . $response->headers->get('retry-after') . ' seconds.',
                            'code' => $response->getStatusCode()
                        ],
                        $response->getStatusCode()
                    );
                default:
                    return response()->json(
                        [
                            'message' => json_decode($response->getContent())->message,
                            'code' => $response->getStatusCode()
                        ],
                        $response->getStatusCode()
                    );
            }
        });
    })->create();
