<?php

declare(strict_types=1);

namespace App\Factories\Authorization;

use App\Contracts\Authorization\DentalRecordAuthorizationStrategyInterface;
use InvalidArgumentException;
use Illuminate\Support\Str;

class DentalRecordAuthorizationFactory
{
    /**
     * Dynamically resolve the structural authorization strategy based on the domain user role.
     *
     * @param  string  $role  The user dynamic configuration role template name.
     * @return \App\Contracts\Authorization\DentalRecordAuthorizationStrategyInterface
     *
     * @throws \InvalidArgumentException If the strategy block target implementation is missing.
     */
    public static function make(string $role): DentalRecordAuthorizationStrategyInterface
    {
        $formattedRole = Str::studly($role);
        $className = "App\\Strategies\\Authorization\\DentalRecord\\{$formattedRole}AuthorizationStrategy";

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Authorization structural error: Strategy class [{$className}] for role [{$role}] is not defined."
            );
        }

        return app($className);
    }
}
