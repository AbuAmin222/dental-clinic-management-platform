<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (Auth::check() && $role && Auth::user()->role === $role) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized, Canno`t have permission to access this page.');
    }
}
