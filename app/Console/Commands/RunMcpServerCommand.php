<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mcp\Server\McpServer;
use Illuminate\Console\Command;
use Throwable;

/**
 * `php artisan mcp:serve` — stdio transport. Reads one newline-delimited JSON-RPC
 * message per line from STDIN, writes one JSON-RPC response per line to STDOUT. This is
 * the transport Claude Desktop, Cursor, and Windsurf all speak natively (they launch this
 * exact command as a subprocess and talk to it over its stdin/stdout pipes) — see
 * MCP_INTEGRATION_GUIDE.md for the exact client config.
 *
 * Never write anything to STDOUT other than JSON-RPC response lines — that stream is the
 * protocol channel itself, not a log. Diagnostics go to STDERR (via $this->error()/warn(),
 * which Symfony Console routes to stderr by default) or the mcp_audit log channel.
 */
class RunMcpServerCommand extends Command
{
    protected $signature = 'mcp:serve {--transport=stdio : Currently only "stdio" is supported by this command; see routes/api.php for the SSE/HTTP transport}';

    protected $description = 'Run the Dental Clinic MCP server, speaking JSON-RPC 2.0 over stdio.';

    public function handle(McpServer $server): int
    {
        if ($this->option('transport') !== 'stdio') {
            $this->error('Only --transport=stdio is supported by this command. For HTTP+SSE, see routes/api.php (POST /api/mcp/sse) instead.');
            return self::FAILURE;
        }

        $this->info('Dental Clinic MCP server ready (stdio). Waiting for JSON-RPC messages on STDIN...', verbosity: 'v');

        $stdin = fopen('php://stdin', 'rb');
        $stdout = fopen('php://stdout', 'wb');

        if ($stdin === false || $stdout === false) {
            $this->error('Could not open stdio streams.');
            return self::FAILURE;
        }

        while (!feof($stdin)) {
            $line = fgets($stdin);

            if ($line === false || trim($line) === '') {
                continue;
            }

            $this->processLine(trim($line), $server, $stdout);
        }

        fclose($stdin);
        fclose($stdout);

        return self::SUCCESS;
    }

    /**
     * @param resource $stdout
     */
    private function processLine(string $line, McpServer $server, $stdout): void
    {
        try {
            $request = json_decode($line, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $this->writeMessage($stdout, [
                'jsonrpc' => '2.0',
                'id' => null,
                'error' => ['code' => -32700, 'message' => 'Parse error: invalid JSON'],
            ]);
            return;
        }

        if (!is_array($request)) {
            $this->writeMessage($stdout, [
                'jsonrpc' => '2.0',
                'id' => null,
                'error' => ['code' => -32600, 'message' => 'Invalid Request: expected a JSON object'],
            ]);
            return;
        }

        $response = $server->handle($request);

        if ($response !== null) {
            $this->writeMessage($stdout, $response);
        }
    }

    /**
     * @param resource $stdout
     * @param array<string, mixed> $message
     */
    private function writeMessage($stdout, array $message): void
    {
        fwrite($stdout, json_encode($message, JSON_UNESCAPED_SLASHES) . "\n");
        fflush($stdout);
    }
}
