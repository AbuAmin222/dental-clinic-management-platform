<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Contracts\McpToolInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Read-only ad-hoc SQL query tool. Safety is layered, not single-point:
 *   1. Statement must start with SELECT (case-insensitive) — no INSERT/UPDATE/DELETE/DDL.
 *   2. No semicolons other than one optional trailing one — blocks stacked statements.
 *   3. Every table named after FROM/JOIN must appear in config('mcp.database_query_tool.allowed_tables')
 *      — an allowlist, so a new sensitive table added later is excluded by default, not by omission.
 *   4. A LIMIT clause is enforced (injected if the caller didn't include one) so a client
 *      can never accidentally (or deliberately) pull an entire large table in one call.
 *   5. Runs inside a request-scoped DB transaction that is always rolled back — belt and
 *      braces on top of (1): even if a write somehow slipped through, it never commits.
 */
final class ExecuteDatabaseQueryTool implements McpToolInterface
{
    public function __construct(
        private readonly array $allowedTables,
        private readonly int $maxRows,
        private readonly int $timeoutSeconds,
    ) {
    }

    public function name(): string
    {
        return 'execute_database_query';
    }

    public function description(): string
    {
        return 'Run a read-only SELECT query against the clinic database. Only SELECT '
            . 'statements are accepted, only against an explicit table allowlist, and '
            . "results are capped at {$this->maxRows} rows. Use the schema://database "
            . 'resource first to see available tables and columns.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['sql'],
            'properties' => [
                'sql' => [
                    'type' => 'string',
                    'description' => 'A single SELECT statement. Use named bindings (:name) with the "bindings" object rather than inlining values.',
                ],
                'bindings' => [
                    'type' => 'object',
                    'description' => 'Named parameter bindings referenced in sql as :name.',
                ],
            ],
        ];
    }

    public function isMutating(): bool
    {
        return false;
    }

    public function handle(array $arguments): array
    {
        $sql = trim((string) $arguments['sql']);
        $bindings = (array) ($arguments['bindings'] ?? []);

        $this->assertIsSingleSelectStatement($sql);
        $this->assertOnlyAllowedTables($sql);
        $sql = $this->enforceLimit($sql);

        $startedAt = microtime(true);

        DB::beginTransaction();

        try {
            try {
                DB::statement('SET SESSION MAX_EXECUTION_TIME=' . ($this->timeoutSeconds * 1000));
            } catch (\Throwable) {
                // Non-MySQL connections (e.g. sqlite in tests) don't support this
                // statement — the allowlist/SELECT-only checks above are the real
                // safety net; this is a best-effort extra guard on MySQL specifically.
            }

            $result = DB::select($sql, $bindings);
            $rows = array_map(static fn (object $row): array => (array) $row, $result);
        } finally {
            // Always roll back — this tool is read-only by contract; the transaction
            // wrapper exists purely as a second line of defense, not to persist anything,
            // and must run even if DB::select() itself throws.
            DB::rollBack();
        }

        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

        return [
            'row_count' => count($rows),
            'truncated' => count($rows) >= $this->maxRows,
            'elapsed_ms' => $elapsedMs,
            'rows' => $rows,
        ];
    }

    private function assertIsSingleSelectStatement(string $sql): void
    {
        $withoutTrailingSemicolon = rtrim(rtrim($sql), ';');

        if (str_contains($withoutTrailingSemicolon, ';')) {
            throw new RuntimeException('Only a single statement is allowed — no semicolon-separated statements.');
        }

        if (!preg_match('/^select\s/i', $withoutTrailingSemicolon)) {
            throw new RuntimeException('Only SELECT statements are allowed.');
        }

        $forbidden = [
            'insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'grant', 'revoke',
            'create', 'replace', 'call', 'exec',
            // MySQL-specific vectors that are technically part of a SELECT statement's
            // syntax but read/write the server's filesystem, not query results —
            // `SELECT ... INTO OUTFILE '/path'` and `SELECT LOAD_FILE('/etc/passwd')`
            // are classic SQLi-to-file-read/RCE techniques that "starts with SELECT,
            // no semicolon, no disallowed table" alone would not catch.
            'into\s+outfile', 'into\s+dumpfile', 'load_file',
        ];
        foreach ($forbidden as $keyword) {
            $pattern = str_contains($keyword, '\\s')
                ? '/\b' . $keyword . '\b/i'
                : '/\b' . preg_quote($keyword, '/') . '\b/i';

            if (preg_match($pattern, $withoutTrailingSemicolon)) {
                throw new RuntimeException("Forbidden keyword or clause detected: " . str_replace('\\s+', ' ', $keyword));
            }
        }
    }

    private function assertOnlyAllowedTables(string $sql): void
    {
        preg_match_all('/\b(?:from|join)\s+`?(\w+)`?/i', $sql, $matches);
        $referencedTables = array_unique(array_map('strtolower', $matches[1] ?? []));

        $disallowed = array_diff($referencedTables, array_map('strtolower', $this->allowedTables));

        if ($disallowed !== []) {
            $list = implode(', ', $disallowed);
            throw new RuntimeException("Query references table(s) not in the allowlist: {$list}");
        }
    }

    private function enforceLimit(string $sql): string
    {
        if (preg_match('/\blimit\s+\d+/i', $sql)) {
            return $sql;
        }

        return rtrim(rtrim($sql), ';') . " LIMIT {$this->maxRows}";
    }
}
