<?php

declare(strict_types=1);

namespace App\Factories\Validation;

use App\Contracts\Validation\RoleValidationRulesInterface;
use InvalidArgumentException;
use Illuminate\Support\Str;

class RoleValidationFactory
{
    /**
     * Explore and dynamically build a verification strategy based on the role name.
     */
    public static function make(string $role): RoleValidationRulesInterface
    {
        $formattedRole = Str::studly($role);
        $className = "App\\Strategies\\Validation\\{$formattedRole}ValidationRules";

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Architecture error: The role validation class [{$formattedRole}] does not exist in the system."
            );
        }

        return app($className);
    }
}
