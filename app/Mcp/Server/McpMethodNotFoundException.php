<?php

declare(strict_types=1);

namespace App\Mcp\Server;

use RuntimeException;

/**
 * Maps to JSON-RPC error code -32601 (Method not found).
 */
final class McpMethodNotFoundException extends RuntimeException
{
    public function __construct(string $method)
    {
        parent::__construct("Method not found: {$method}");
    }
}
