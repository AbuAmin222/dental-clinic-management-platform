<?php

declare(strict_types=1);

namespace App\Mcp\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Single place every tool-call audit entry passes through. Deliberately NOT a facade or
 * static-only class — it's bound in McpServiceProvider so tests can swap it for a spy.
 */
final class McpAuditLogger
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $channel,
    ) {
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function logCall(string $toolName, array $arguments, bool $mutating): void
    {
        if (!$this->enabled) {
            return;
        }

        Log::channel($this->channel)->info('mcp.tool.call', [
            'tool' => $toolName,
            'mutating' => $mutating,
            'arguments' => $this->redact($arguments),
            'at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @param array<string, mixed> $result
     */
    public function logResult(string $toolName, array $result): void
    {
        if (!$this->enabled) {
            return;
        }

        Log::channel($this->channel)->info('mcp.tool.result', [
            'tool' => $toolName,
            'result_keys' => array_keys($result),
            'at' => now()->toIso8601String(),
        ]);
    }

    public function logError(string $toolName, Throwable $exception): void
    {
        if (!$this->enabled) {
            return;
        }

        Log::channel($this->channel)->error('mcp.tool.error', [
            'tool' => $toolName,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Never write raw password/secret-looking argument values into the audit log itself —
     * the log's whole purpose is safe-to-review traceability, so it must not become a
     * second place secrets leak from.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    private function redact(array $arguments): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];

        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $arguments[$key] = $this->redact($value);
                continue;
            }

            foreach ($sensitiveKeys as $needle) {
                if (str_contains(strtolower((string) $key), $needle)) {
                    $arguments[$key] = '[REDACTED]';
                    break;
                }
            }
        }

        return $arguments;
    }
}
