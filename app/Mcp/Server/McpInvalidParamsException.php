<?php

declare(strict_types=1);

namespace App\Mcp\Server;

use RuntimeException;

/**
 * Maps to JSON-RPC error code -32602 (Invalid params).
 */
final class McpInvalidParamsException extends RuntimeException
{
}
