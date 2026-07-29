<?php

use App\Http\Responses\ExceptionRenderers\ExceptionRendererRegistry;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
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
