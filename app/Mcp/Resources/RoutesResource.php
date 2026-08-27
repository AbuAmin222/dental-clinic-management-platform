<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Mcp\Contracts\McpResourceInterface;
use Illuminate\Support\Facades\Route;

/**
 * Live route map from the actual router (Route::getRoutes()) rather than a maintained
 * document — this project's routing is dynamically bootstrapped per-role at runtime
 * (RoleRouteOrchestrator), so any static list would already be wrong the moment a new
 * Registrar is added.
 */
final class RoutesResource implements McpResourceInterface
{
    public function uri(): string
    {
        return 'routes://application';
    }

    public function name(): string
    {
        return 'Application Routes';
    }

    public function description(): string
    {
        return 'Every registered HTTP route (method, URI, name, middleware), read live from the router.';
    }

    public function mimeType(): string
    {
        return 'application/json';
    }

    public function read(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $routes[] = [
                'methods' => $route->methods(),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'middleware' => array_values($route->gatherMiddleware()),
            ];
        }

        return $routes;
    }
}
