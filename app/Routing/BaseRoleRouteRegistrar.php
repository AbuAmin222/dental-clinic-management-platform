<?php

declare(strict_types=1);

namespace App\Routing;

use App\Contracts\Routing\RoleRouteRegistrarInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

abstract class BaseRoleRouteRegistrar implements RoleRouteRegistrarInterface
{
    /**
     * Template Method enforcing unified route patterns alongside dynamic context extensions.
     */
    public final function register(Router $router): void
    {
        $router->get('/dashboard', $this->dashboardAction())->name('dashboard');

        $this->registerSpecificRoutes($router);
    }

    /**
     * Helper to isolate the exact lowercase identifier matching the role domain.
     */
    public function getRoleKey(): string
    {
        return Str::lower(str_replace('RouteRegistrar', '', class_basename($this)));
    }

    abstract protected function dashboardAction(): array;

    /**
     * Optional per-role middleware appended after the mandatory `role:{key}` guard —
     * override in a concrete Registrar only when that role needs something extra (e.g.
     * `onboarding.completed`).
     * Returning an empty array is the correct default: NOT every
     * role registrar needs to override this, and RoleRouteOrchestrator::boot() calls it
     * unconditionally on every registrar, so a registrar that omits the override must not
     * crash the entire application's routing
     *
     * @return string[]
     */
    public function additionalMiddleware(): array
    {
        return [];
    }

    /**
     * Hook signature requiring child registrars to inject unique domain capabilities.
     */
    abstract protected function registerSpecificRoutes(Router $router): void;
}
