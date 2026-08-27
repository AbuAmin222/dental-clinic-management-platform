<?php

declare(strict_types=1);

namespace App\Mcp\Server;

use App\Mcp\Contracts\McpResourceInterface;
use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Support\JsonSchemaValidator;
use App\Mcp\Support\McpAuditLogger;
use Throwable;

/**
 * Transport-agnostic MCP JSON-RPC 2.0 dispatcher. Neither the stdio Artisan command nor
 * the SSE HTTP controller know anything about tools/resources or the protocol's message
 * shapes — they only decode a request array, call handle(), and write the response
 * array back out. That's the "Middleware/Wrapper" separation the brief asked for: this
 * class is the only thing that knows about MCP; everything below it is plain Laravel
 * (Actions, Eloquent, Schema facade), and everything above it is plain transport I/O.
 */
final class McpServer
{
    /** @var array<string, McpToolInterface> keyed by tool name */
    private array $tools = [];

    /** @var array<string, McpResourceInterface> keyed by resource uri */
    private array $resources = [];

    public function __construct(
        private readonly array $serverConfig,
        private readonly bool $allowMutations,
        private readonly McpAuditLogger $audit,
    ) {
    }

    public function registerTool(McpToolInterface $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    public function registerResource(McpResourceInterface $resource): void
    {
        $this->resources[$resource->uri()] = $resource;
    }

    /**
     * @return array<string, McpToolInterface>
     */
    public function tools(): array
    {
        return $this->tools;
    }

    /**
     * @return array<string, McpResourceInterface>
     */
    public function resources(): array
    {
        return $this->resources;
    }

    /**
     * Dispatch one decoded JSON-RPC request. Returns null for notifications (methods
     * with no "id" — per JSON-RPC 2.0, no response is sent for those at all).
     *
     * @param array<string, mixed> $request
     * @return array<string, mixed>|null
     */
    public function handle(array $request): ?array
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? null;
        $params = (array) ($request['params'] ?? []);

        if ($method === null) {
            return $this->errorResponse($id, -32600, 'Invalid Request: missing method');
        }

        try {
            $result = match ($method) {
                'initialize' => $this->handleInitialize(),
                'notifications/initialized' => null,
                'tools/list' => $this->handleToolsList(),
                'tools/call' => $this->handleToolsCall($params),
                'resources/list' => $this->handleResourcesList(),
                'resources/read' => $this->handleResourcesRead($params),
                'ping' => [],
                default => throw new McpMethodNotFoundException($method),
            };
        } catch (McpMethodNotFoundException $exception) {
            return $this->errorResponse($id, -32601, $exception->getMessage());
        } catch (McpInvalidParamsException $exception) {
            return $this->errorResponse($id, -32602, $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);
            return $this->errorResponse($id, -32603, 'Internal error: ' . $exception->getMessage());
        }

        // Notifications (id === null on the incoming request) never get a response,
        // per JSON-RPC 2.0 — regardless of whether $result is null.
        if ($id === null) {
            return null;
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handleInitialize(): array
    {
        return [
            'protocolVersion' => $this->serverConfig['protocol_version'],
            'capabilities' => [
                'tools' => ['listChanged' => false],
                'resources' => ['listChanged' => false, 'subscribe' => false],
            ],
            'serverInfo' => [
                'name' => $this->serverConfig['name'],
                'version' => $this->serverConfig['version'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handleToolsList(): array
    {
        return [
            'tools' => array_values(array_map(
                static fn (McpToolInterface $tool): array => [
                    'name' => $tool->name(),
                    'description' => $tool->description(),
                    'inputSchema' => $tool->inputSchema(),
                ],
                $this->tools
            )),
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function handleToolsCall(array $params): array
    {
        $name = $params['name'] ?? null;

        if (!is_string($name) || !isset($this->tools[$name])) {
            throw new McpInvalidParamsException("Unknown tool: " . (string) $name);
        }

        $tool = $this->tools[$name];
        $arguments = (array) ($params['arguments'] ?? []);

        if ($tool->isMutating() && !$this->allowMutations) {
            return $this->toolErrorResult(
                "Tool '{$name}' is disabled: this MCP server is running in read-only mode "
                . "(config('mcp.allow_mutations') is false). Set MCP_ALLOW_MUTATIONS=true "
                . "to enable it — deliberately opt-in, never on by default."
            );
        }

        $validationErrors = JsonSchemaValidator::validate($tool->inputSchema(), $arguments);
        if ($validationErrors !== []) {
            throw new McpInvalidParamsException(implode(' | ', $validationErrors));
        }

        $this->audit->logCall($name, $arguments, $tool->isMutating());

        try {
            $result = $tool->handle($arguments);
        } catch (Throwable $exception) {
            $this->audit->logError($name, $exception);
            return $this->toolErrorResult($exception->getMessage());
        }

        $this->audit->logResult($name, $result);

        return [
            'content' => [
                ['type' => 'text', 'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)],
            ],
            'isError' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toolErrorResult(string $message): array
    {
        return [
            'content' => [
                ['type' => 'text', 'text' => $message],
            ],
            'isError' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handleResourcesList(): array
    {
        return [
            'resources' => array_values(array_map(
                static fn (McpResourceInterface $resource): array => [
                    'uri' => $resource->uri(),
                    'name' => $resource->name(),
                    'description' => $resource->description(),
                    'mimeType' => $resource->mimeType(),
                ],
                $this->resources
            )),
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function handleResourcesRead(array $params): array
    {
        $uri = $params['uri'] ?? null;

        if (!is_string($uri) || !isset($this->resources[$uri])) {
            throw new McpInvalidParamsException("Unknown resource: " . (string) $uri);
        }

        $resource = $this->resources[$uri];

        return [
            'contents' => [
                [
                    'uri' => $resource->uri(),
                    'mimeType' => $resource->mimeType(),
                    'text' => json_encode($resource->read(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResponse(mixed $id, int $code, string $message): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => ['code' => $code, 'message' => $message],
        ];
    }
}
