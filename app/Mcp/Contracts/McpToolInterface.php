<?php

declare(strict_types=1);

namespace App\Mcp\Contracts;

/**
 * Every MCP tool implements this and nothing else. The registry
 * (McpServiceProvider + config/mcp.php) is the only place that needs to
 * change to add a new tool — this interface is what makes that an
 * Open/Closed extension rather than a modification of McpServer itself.
 */
interface McpToolInterface
{
    /**
     * Unique, stable tool name exposed to MCP clients (snake_case, no spaces).
     */
    public function name(): string;

    /**
     * Human-readable description shown to the model deciding whether to call this tool.
     */
    public function description(): string;

    /**
     * JSON Schema (draft-07 subset MCP expects) describing the tool's `arguments` object.
     *
     * @return array<string, mixed>
     */
    public function inputSchema(): array;

    /**
     * True if calling this tool can change application state (write to the database,
     * dispatch a job, send a notification, etc). McpServer refuses to execute a mutating
     * tool at all when `config('mcp.allow_mutations')` is false — this is enforced
     * centrally, once, rather than trusted to each tool's own implementation.
     */
    public function isMutating(): bool;

    /**
     * Execute the tool. $arguments is already validated against inputSchema() by
     * McpServer before this is called.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed> Structured result content (JSON-serializable).
     */
    public function handle(array $arguments): array;
}
