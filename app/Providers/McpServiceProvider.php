<?php

declare(strict_types=1);

namespace App\Providers;

use App\Mcp\Contracts\McpResourceInterface;
use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Resources\DatabaseSchemaResource;
use App\Mcp\Server\McpServer;
use App\Mcp\Support\McpAuditLogger;
use App\Mcp\Tools\ExecuteDatabaseQueryTool;
use App\Mcp\Tools\RunDomainActionTool;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * The single place that turns config/mcp.php into live objects. Adding a new tool or
 * resource never touches this file — only the two classes with constructor arguments
 * that come from config (ExecuteDatabaseQueryTool, RunDomainActionTool,
 * DatabaseSchemaResource) need explicit `make()` calls with those arguments; every other
 * tool/resource is resolved by the container with zero-argument construction, which is
 * why they're just listed in config('mcp.tools')/config('mcp.resources') as plain class
 * names.
 */
class McpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('mcp', static function ($request) {
            return Limit::perSeconds(
                (int) config('mcp.rate_limit.decay_seconds'),
                (int) config('mcp.rate_limit.max_attempts'),
            )->by($request->user()?->id ?: $request->ip());
        });
    }

    public function register(): void
    {
        // No mergeConfigFrom() call needed here: config/mcp.php lives directly in this
        // application's own config/ directory, so Laravel's normal config bootstrapping
        // already loads it automatically under the 'mcp' key — mergeConfigFrom() is only
        // needed when a config file ships from outside config/ (e.g. a Composer package).

        $this->app->singleton(McpAuditLogger::class, static function (): McpAuditLogger {
            return new McpAuditLogger(
                enabled: (bool) config('mcp.audit.enabled'),
                channel: (string) config('mcp.audit.channel'),
            );
        });

        $this->app->singleton(McpServer::class, function (): McpServer {
            $server = new McpServer(
                serverConfig: (array) config('mcp.server'),
                allowMutations: (bool) config('mcp.allow_mutations'),
                audit: $this->app->make(McpAuditLogger::class),
            );

            foreach ($this->resolveTools() as $tool) {
                $server->registerTool($tool);
            }

            foreach ($this->resolveResources() as $resource) {
                $server->registerResource($resource);
            }

            return $server;
        });
    }

    /**
     * @return McpToolInterface[]
     */
    private function resolveTools(): array
    {
        $tools = [];

        foreach ((array) config('mcp.tools') as $toolClass) {
            $tool = match ($toolClass) {
                ExecuteDatabaseQueryTool::class => new ExecuteDatabaseQueryTool(
                    allowedTables: (array) config('mcp.database_query_tool.allowed_tables'),
                    maxRows: (int) config('mcp.database_query_tool.max_rows'),
                    timeoutSeconds: (int) config('mcp.database_query_tool.timeout_seconds'),
                ),
                RunDomainActionTool::class => new RunDomainActionTool(
                    container: $this->app,
                    actions: $this->validatedActionRegistry(),
                ),
                default => $this->app->make($toolClass),
            };

            if (!$tool instanceof McpToolInterface) {
                throw new RuntimeException("{$toolClass} does not implement McpToolInterface.");
            }

            $tools[] = $tool;
        }

        return $tools;
    }

    /**
     * @return McpResourceInterface[]
     */
    private function resolveResources(): array
    {
        $resources = [];

        foreach ((array) config('mcp.resources') as $resourceClass) {
            $resource = match ($resourceClass) {
                DatabaseSchemaResource::class => new DatabaseSchemaResource(
                    allowedTables: (array) config('mcp.database_query_tool.allowed_tables'),
                ),
                default => $this->app->make($resourceClass),
            };

            if (!$resource instanceof McpResourceInterface) {
                throw new RuntimeException("{$resourceClass} does not implement McpResourceInterface.");
            }

            $resources[] = $resource;
        }

        return $resources;
    }

    /**
     * Fails at boot time — not at first tool call — if config/mcp.php ever points an
     * action key at a class that doesn't expose execute(array $data): a wrong config
     * entry becomes an immediate, obvious startup error instead of a confusing runtime
     * failure the first time an MCP client happens to call that specific action.
     *
     * @return array<string, array{class: class-string, description: string, input_schema: array<string, mixed>}>
     */
    private function validatedActionRegistry(): array
    {
        $actions = (array) config('mcp.actions');

        foreach ($actions as $key => $definition) {
            $class = $definition['class'] ?? null;

            if (!is_string($class) || !class_exists($class)) {
                throw new RuntimeException("mcp.actions.{$key}.class is not a valid class.");
            }

            if (!method_exists($class, 'execute')) {
                throw new RuntimeException("mcp.actions.{$key}.class ({$class}) must expose an execute(array \$data) method.");
            }
        }

        return $actions;
    }
}
