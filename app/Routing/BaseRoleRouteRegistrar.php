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
     * Hook signature requiring child registrars to inject unique domain capabilities.
     */
    abstract protected function registerSpecificRoutes(Router $router): void;
}
