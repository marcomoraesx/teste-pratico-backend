<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class PermissionsApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();
        if (!$user) throw UnauthorizedException::notLoggedIn();
        if (!$user->hasAllPermissions($permissions)) throw UnauthorizedException::forPermissions([$permissions]);
        return $next($request);
    }
}
