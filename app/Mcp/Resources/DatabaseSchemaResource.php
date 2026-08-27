<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Mcp\Contracts\McpResourceInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Introspects the real, current database schema (via Laravel's Schema facade — never a
 * hand-maintained description that can drift from the actual migrations) restricted to
 * the same allowlist ExecuteDatabaseQueryTool uses, so a client reading this resource
 * never sees a table it isn't also allowed to query.
 */
final class DatabaseSchemaResource implements McpResourceInterface
{
    public function __construct(
        private readonly array $allowedTables,
    ) {
    }

    public function uri(): string
    {
        return 'schema://database';
    }

    public function name(): string
    {
        return 'Database Schema';
    }

    public function description(): string
    {
        return 'Tables and columns currently allowlisted for execute_database_query, introspected live from the database.';
    }

    public function mimeType(): string
    {
        return 'application/json';
    }

    public function read(): array
    {
        $existingTables = array_map(
            static fn (array $table): string => $table['name'],
            Schema::getTables()
        );

        $schema = [];

        foreach ($this->allowedTables as $table) {
            if (!in_array($table, $existingTables, true)) {
                continue; // Allowlisted but not (yet) migrated — omit rather than error.
            }

            $columns = Schema::getColumns($table);

            $schema[$table] = array_map(
                static fn (array $column): array => [
                    'name' => $column['name'],
                    'type' => $column['type'],
                    'nullable' => $column['nullable'],
                    'default' => $column['default'],
                ],
                $columns
            );
        }

        return $schema;
    }
}
