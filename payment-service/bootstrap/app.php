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
        $middleware->alias([
            'permissions' => \App\Http\Middleware\PermissionsApiMiddleware::class,
            'roles' => \App\Http\Middleware\RolesApiMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
        $exceptions->respond(function (Response $response, Throwable $e) {
            $status = $response->getStatusCode();
            $message = $e->getPrevious()?->getMessage() ?? $e->getMessage();
            switch ($status) {
                case 400:
                    return response()->json(
                        [
                            'message' => $message,
                            'code' => $status
                        ],
                        $status
                    );
                case 422:
                    return response()->json(
                        [
                            'message' => json_decode($response->getContent())->message,
                            'errors' => json_decode($response->getContent())->errors,
                            'code' => $status
                        ],
                        $status
                    );
                case 429:
                    return response()->json(
                        [
                            'message' => 'Too many requests. Please try again in ' . $response->headers->get('retry-after') . ' seconds.',
                            'code' => $status
                        ],
                        $status
                    );
                case 500:
                    return response()->json(
                        [
                            'message' => 'An internal server error occurred. Please try again later.',
                            'code' => $status
                        ],
                        $status
                    );
                default:
                    return response()->json(
                        [
                            'message' => json_decode($response->getContent())->message,
                            'code' => $status
                        ],
                        $status
                    );
            }
        });
    })->create();
