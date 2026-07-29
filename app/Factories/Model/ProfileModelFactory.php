<?php

declare(strict_types=1);

namespace App\Factories\Model;

use RuntimeException;
use Illuminate\Support\Str;

class ProfileModelFactory
{
    /**
     * Dynamically resolve the fully qualified class name of the profile model.
     * Keeps the User model completely isolated from class generation strings.
     *
     * @param string $role
     * @return string
     * @throws RuntimeException
     */
    public static function resolveClass(string $role): string
    {
        $formattedRole = Str::studly($role);
        $modelClass = "App\\Models\\{$formattedRole}";

        if (!class_exists($modelClass)) {
            throw new RuntimeException(
                "Architectural Integrity Violation: Concrete profile model [{$modelClass}] for role [{$role}] is missing."
            );
        }

        return $modelClass;
    }
}
