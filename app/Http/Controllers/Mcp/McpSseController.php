<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Mcp\Server\McpServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HTTP+SSE transport for clients that can't launch a subprocess (stdio) — browser-based
 * MCP clients, remote clients, etc. Claude Desktop/Cursor/Windsurf normally use stdio
 * (RunMcpServerCommand) instead; this exists for the deployment shape where the MCP
 * server needs to be reachable over the network.
 *
 * A GET request and its matching POST requests run in different PHP-FPM worker
 * processes with no shared memory, so delivering a POST's JSON-RPC response back onto
 * the GET request's open SSE stream needs a cross-process channel — this project already
 * depends on Redis (cache/session/queue), so a short-lived Redis list per session is used
 * rather than introducing a new piece of infrastructure just for this.
 */
class McpSseController extends Controller
{
    private const SESSION_TTL_SECONDS = 300;

    public function stream(Request $request, McpServer $server): StreamedResponse
    {
        $sessionId = (string) Str::uuid();
        $channelKey = $this->channelKey($sessionId);

        $response = new StreamedResponse(function () use ($channelKey, $request): void {
            $this->sendEvent('endpoint', ['uri' => route('mcp.messages', ['sessionId' => $this->sessionIdFromKey($channelKey)])]);

            while (!connection_aborted()) {
                $item = Redis::connection()->blpop([$channelKey], 15);

                if ($item === null || $item === []) {
                    // Nothing arrived within the timeout — send a comment frame purely
                    // to keep intermediate proxies/load balancers from closing an
                    // apparently-idle connection, and refresh the session TTL.
                    echo ": ping\n\n";
                    Redis::connection()->expire($channelKey, self::SESSION_TTL_SECONDS);
                    flush();
                    continue;
                }

                [, $payload] = $item;
                $this->sendEvent('message', json_decode($payload, associative: true));

                if ($request->query('close_after_first') === '1') {
                    // Used by the integration test in tests/Feature/Mcp — a normal
                    // long-lived client never sets this.
                    break;
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }

    public function receive(Request $request, McpServer $server): JsonResponse
    {
        $sessionId = (string) $request->query('sessionId', '');

        if ($sessionId === '' || !Str::isUuid($sessionId)) {
            return response()->json(['error' => 'Missing or invalid sessionId query parameter.'], 400);
        }

        $payload = $request->json()->all();
        $mcpResponse = $server->handle($payload);

        if ($mcpResponse !== null) {
            Redis::connection()->rpush($this->channelKey($sessionId), json_encode($mcpResponse, JSON_UNESCAPED_SLASHES));
            Redis::connection()->expire($this->channelKey($sessionId), self::SESSION_TTL_SECONDS);
        }

        // 202: the real JSON-RPC result (if any — notifications have none) is delivered
        // asynchronously over the SSE stream, not in this response body, per the MCP
        // HTTP+SSE transport convention.
        return response()->json(['accepted' => true], 202);
    }

    private function channelKey(string $sessionId): string
    {
        return "mcp:sse:{$sessionId}";
    }

    private function sessionIdFromKey(string $channelKey): string
    {
        return str_replace('mcp:sse:', '', $channelKey);
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function sendEvent(string $event, ?array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_SLASHES) . "\n\n";
        flush();
    }
}
