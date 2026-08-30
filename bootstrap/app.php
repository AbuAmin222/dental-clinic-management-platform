<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureAccountSecurityCompleted;
use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Responses\ExceptionRenderers\ExceptionRendererRegistry;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role' => CheckRole::class,
            'active' => EnsureUserIsActive::class,
            'onboarding.completed' => EnsureOnboardingCompleted::class,
            'account-security.completed' => EnsureAccountSecurityCompleted::class,

        ]);
        $middleware->statefulApi();
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Global API Exception Interceptor driving structural strategy resolutions.
         */
        $exceptions->render(function (Throwable $exception, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ExceptionRendererRegistry::resolve($exception)->render($exception);
            }
        });
    })->create();
