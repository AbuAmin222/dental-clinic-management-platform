<?php

declare(strict_types=1);

namespace App\Mcp\Contracts;

/**
 * MCP "resources" are read-only reference material a client can fetch without invoking a
 * tool call — schema descriptions, route maps, enum catalogs. Never mutating by
 * definition, so there is no isMutating() here (unlike McpToolInterface).
 */
interface McpResourceInterface
{
    /**
     * Stable URI identifying this resource, MCP convention e.g. "schema://database".
     */
    public function uri(): string;

    public function name(): string;

    public function description(): string;

    /**
     * MIME type of the content read() returns, e.g. "application/json".
     */
    public function mimeType(): string;

    /**
     * @return array<string, mixed> JSON-serializable resource content.
     */
    public function read(): array;
}
