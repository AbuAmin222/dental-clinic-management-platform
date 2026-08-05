<?php

declare(strict_types=1);

namespace App\Factories\Authorization;

use App\Contracts\Authorization\PatientAuthorizationStrategyInterface;
use InvalidArgumentException;
use Illuminate\Support\Str;

class PatientAuthorizationFactory
{
    /**
     * Dynamically resolve the structural authorization strategy based on the domain user role.
     *
     * @param  string  $role  The user dynamic configuration role template name.
     * @return \App\Contracts\Authorization\PatientAuthorizationStrategyInterface
     *
     * @throws \InvalidArgumentException If the strategy block target implementation is missing.
     */
    public static function make(string $role): PatientAuthorizationStrategyInterface
    {
        $formattedRole = Str::studly($role);
        $className = "App\\Strategies\\Authorization\\Patient\\{$formattedRole}AuthorizationStrategy";

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Authorization structural error: Strategy class [{$className}] for role [{$role}] is not defined."
            );
        }

        return app($className);
    }
}
