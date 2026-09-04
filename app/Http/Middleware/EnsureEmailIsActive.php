<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsActive
{
    private const EXEMPT_ROUTE_NAMES = ['logout'];

    public function __construct() {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (
            ! $request->routeIs(...self::EXEMPT_ROUTE_NAMES)
        ) {
            return redirect()->route('.show');
        }

        return $next($request);
    }
}
