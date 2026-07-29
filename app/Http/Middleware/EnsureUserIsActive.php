<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /** Route names exempt from the redirect to prevent loops. */
    private const EXEMPT_ROUTE_NAMES = ['pending-review', 'logout', 'profile.delete'];

    /**
     * Handle an incoming request.
     *
     * Intercepts request to verify user account status.
     * If deactivated, session is purged immediately and user is logged out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active && !$request->routeIs(...self::EXEMPT_ROUTE_NAMES)) {
            return redirect()->route('pending-review');
        }


        return $next($request);
    }
}
