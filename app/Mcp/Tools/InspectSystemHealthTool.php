<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Contracts\McpToolInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Read-only operational snapshot — no arguments, deliberately. Every check is
 * independently wrapped so one failing dependency (e.g. Redis down) reports itself
 * without preventing the rest of the report from coming back.
 */
final class InspectSystemHealthTool implements McpToolInterface
{
    public function name(): string
    {
        return 'inspect_system_health';
    }

    public function description(): string
    {
        return 'Read-only operational snapshot: database connectivity, queue/cache '
            . 'connectivity, disk space, migration status, and the tail of the most '
            . 'recent application log entries. No arguments.';
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => []];
    }

    public function isMutating(): bool
    {
        return false;
    }

    public function handle(array $arguments): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache_and_queue' => $this->checkRedis(),
            'disk' => $this->checkDisk(),
            'migrations' => $this->checkMigrations(),
            'recent_log_entries' => $this->tailLog(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkDatabase(): array
    {
        $startedAt = microtime(true);

        try {
            DB::connection()->getPdo();
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            return [
                'connected' => true,
                'driver' => DB::connection()->getDriverName(),
                'latency_ms' => $latencyMs,
            ];
        } catch (Throwable $exception) {
            return [
                'connected' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function checkRedis(): array
    {
        try {
            $startedAt = microtime(true);
            Redis::connection()->ping();
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            $queueLength = null;
            try {
                $queueLength = (int) Redis::connection()->llen('queues:default');
            } catch (Throwable) {
                // Queue may not be Redis-backed in every environment — connectivity
                // above is what actually matters for this check.
            }

            return [
                'connected' => true,
                'latency_ms' => $latencyMs,
                'default_queue_length' => $queueLength,
            ];
        } catch (Throwable $exception) {
            return [
                'connected' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function checkDisk(): array
    {
        $storagePath = storage_path();
        $freeBytes = disk_free_space($storagePath);
        $totalBytes = disk_total_space($storagePath);

        if ($freeBytes === false || $totalBytes === false) {
            return ['available' => false];
        }

        return [
            'available' => true,
            'free_gb' => round($freeBytes / 1024 ** 3, 2),
            'total_gb' => round($totalBytes / 1024 ** 3, 2),
            'used_percent' => round((1 - $freeBytes / $totalBytes) * 100, 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkMigrations(): array
    {
        try {
            $ranCount = DB::table('migrations')->count();
            $fileCount = count(File::glob(database_path('migrations/*.php')));

            return [
                'ran' => $ranCount,
                'files_on_disk' => $fileCount,
                'up_to_date' => $ranCount >= $fileCount,
            ];
        } catch (Throwable $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * @return string[]
     */
    private function tailLog(int $lines = 20): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (!File::exists($logPath)) {
            return [];
        }

        // A hand-rolled tail rather than reading the whole file — Laravel's log file can
        // grow to tens of megabytes in a long-lived install; reading it all into memory
        // for a 20-line preview would be wasteful every single call.
        $handle = fopen($logPath, 'rb');
        if ($handle === false) {
            return [];
        }

        $buffer = '';
        $chunkSize = 4096;
        fseek($handle, 0, SEEK_END);
        $position = ftell($handle);
        $lineCount = 0;

        while ($position > 0 && $lineCount <= $lines) {
            $readSize = min($chunkSize, $position);
            $position -= $readSize;
            fseek($handle, $position);
            $chunk = fread($handle, $readSize);
            $buffer = $chunk . $buffer;
            $lineCount = substr_count($buffer, "\n");
        }

        fclose($handle);

        $allLines = explode("\n", trim($buffer));

        return array_slice($allLines, -$lines);
    }
}
