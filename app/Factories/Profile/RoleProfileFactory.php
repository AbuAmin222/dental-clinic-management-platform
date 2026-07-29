<?php

declare(strict_types=1);

namespace App\Factories\Profile;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use InvalidArgumentException;
use Illuminate\Support\Str;

class RoleProfileFactory
{
    /**
     * Dynamically resolve and instantiate the profile strategy based on the user role.
     *
     * @param  string  $role  The role name (e.g., 'patient', 'doctor', 'receptionist').
     * @return \App\Contracts\Profile\RoleProfileStrategyInterface
     *
     * @throws \InvalidArgumentException If the strategy class for the given role does not exist.
     */
    public static function make(string $role): RoleProfileStrategyInterface
    {
        $formattedRole = Str::studly($role);

        $className = "App\\Strategies\\Profile\\{$formattedRole}ProfileStrategy";

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Architecture error: The profile strategy class [{$className}] target for role [{$role}] is not defined."
            );
        }

        return app($className);
    }
}
