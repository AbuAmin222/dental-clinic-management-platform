<?php

declare(strict_types=1);

namespace App\Routing;

use Illuminate\Routing\Router;

class RoleRouteOrchestrator
{
    /**
     * Active roles deployment mapping registry matrix.
     * @var array<string>
     */
    private static array $registrars = [
        \App\Routing\Registrars\ReceptionistRouteRegistrar::class,
        \App\Routing\Registrars\DoctorRouteRegistrar::class,
        \App\Routing\Registrars\PatientRouteRegistrar::class,
        \App\Routing\Registrars\FinancialRouteRegistrar::class,
        \App\Routing\Registrars\AdminRouteRegistrar::class,
    ];

    /**
     * Boot and insulate dynamic groups applying role middleware and structural prefixes smoothly.
     */
    public static function boot(Router $router): void
    {
        foreach (self::$registrars as $registrarClass) {
            $registrar = new $registrarClass();
            $roleKey = $registrar->getRoleKey();

            // Intercept routing registry and bind framework components systematically
            $router->middleware(["role:{$roleKey}", ...$registrar->additionalMiddleware()])
                ->prefix($roleKey)
                ->name("{$roleKey}.")
                ->group(static function (Router $subRouter) use ($registrar): void {
                    $registrar->register($subRouter);
                });
        }
    }
}
