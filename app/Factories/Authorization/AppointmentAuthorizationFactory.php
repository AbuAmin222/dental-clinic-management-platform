<?php

declare(strict_types=1);

namespace App\Factories\Authorization;

use App\Contracts\Authorization\AppointmentAuthorizationStrategyInterface;
use InvalidArgumentException;
use Illuminate\Support\Str;

class AppointmentAuthorizationFactory
{
    /**
     * Dynamically resolve the structural authorization strategy based on the domain user role.
     *
     * @param  string  $role  The user dynamic configuration role template name.
     * @return \App\Contracts\Authorization\AppointmentAuthorizationStrategyInterface
     *
     * @throws \InvalidArgumentException If the strategy block target implementation is missing.
     */
    public static function make(string $role): AppointmentAuthorizationStrategyInterface
    {
        $formattedRole = Str::studly($role);
        $className = "App\\Strategies\\Authorization\\Appointment\\{$formattedRole}AuthorizationStrategy";

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Authorization structural error: Strategy class [{$className}] for role [{$role}] is not defined."
            );
        }

        return app($className);
    }
}
