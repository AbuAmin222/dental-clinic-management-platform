<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Mcp\Contracts\McpResourceInterface;
use Illuminate\Support\Facades\File;
use ReflectionEnum;
use Throwable;

/**
 * Reflects every backed enum in app/Enums at read-time, so a case added to (say)
 * InvoiceStatus is visible here immediately with no separate registration step —
 * consistent with this project's own "single source of truth" enum-centralization work.
 */
final class EnumsResource implements McpResourceInterface
{
    public function uri(): string
    {
        return 'enums://application';
    }

    public function name(): string
    {
        return 'Application Enums';
    }

    public function description(): string
    {
        return 'Every backed enum under App\\Enums and its current cases, reflected live from app/Enums.';
    }

    public function mimeType(): string
    {
        return 'application/json';
    }

    public function read(): array
    {
        $result = [];

        foreach (File::glob(app_path('Enums/*.php')) as $file) {
            $className = 'App\\Enums\\' . basename($file, '.php');

            if (!enum_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionEnum($className);
            } catch (Throwable) {
                continue;
            }

            $cases = [];
            foreach ($className::cases() as $case) {
                $cases[] = $reflection->isBacked()
                    ? ['name' => $case->name, 'value' => $case->value]
                    : ['name' => $case->name];
            }

            $result[class_basename($className)] = $cases;
        }

        return $result;
    }
}
