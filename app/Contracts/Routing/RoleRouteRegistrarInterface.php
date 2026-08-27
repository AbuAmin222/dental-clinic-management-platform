<?php

declare(strict_types=1);

namespace App\Contracts\Routing;

use Illuminate\Routing\Router;

interface RoleRouteRegistrarInterface
{
    /**
     * Orchestrate the registration of all routes bound to a specific security role.
     *
     * @param Router $router
     * @return void
     */
    public function register(Router $router): void;

    /**
     * Optional per-role middleware appended after the mandatory `role:{key}` guard.
     *
     * @return string[]
     */
    public function additionalMiddleware(): array;
}
