<?php

declare(strict_types=1);

namespace App\Mcp\Support;

/**
 * Deliberately minimal — covers exactly the subset of JSON Schema this project's tool
 * input schemas actually use (object/string/integer/number/boolean/array, required,
 * enum, properties). Not a general-purpose validator; pulling in a full JSON Schema
 * library for four scalar types would be more dependency than the problem needs.
 */
final class JsonSchemaValidator
{
    /**
     * @param array<string, mixed> $schema
     * @param array<string, mixed> $data
     * @return string[] Validation error messages; empty array means valid.
     */
    public static function validate(array $schema, array $data): array
    {
        $errors = [];

        foreach ((array) ($schema['required'] ?? []) as $requiredField) {
            if (!array_key_exists($requiredField, $data)) {
                $errors[] = "Missing required field: {$requiredField}";
            }
        }

        $properties = (array) ($schema['properties'] ?? []);

        foreach ($data as $field => $value) {
            if (!isset($properties[$field])) {
                continue; // Unknown fields are ignored, not rejected — additive is safe here.
            }

            $fieldSchema = (array) $properties[$field];
            $expectedType = $fieldSchema['type'] ?? null;

            if ($expectedType !== null && !self::matchesType($value, $expectedType)) {
                $errors[] = "Field '{$field}' must be of type {$expectedType}, got " . get_debug_type($value);
                continue;
            }

            if (isset($fieldSchema['enum']) && !in_array($value, $fieldSchema['enum'], true)) {
                $allowed = implode(', ', array_map(strval(...), $fieldSchema['enum']));
                $errors[] = "Field '{$field}' must be one of: {$allowed}";
            }
        }

        return $errors;
    }

    private static function matchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'number' => is_int($value) || is_float($value) || is_numeric($value),
            'boolean' => is_bool($value),
            'array' => is_array($value) && array_is_list($value),
            'object' => is_array($value),
            default => true,
        };
    }
}
