<?php

declare(strict_types=1);

namespace App\Factories\Telemetry;

use App\Contracts\Telemetry\DashboardTelemetryInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class DashboardTelemetryFactory
 *
 * NEW FILE (Coherence Audit, 2026-08-02): DoctorDashboardTelemetry, PatientDashboardTelemetry,
 * and ReceptionistDashboardTelemetry were all fully implemented but completely orphaned — no
 * Factory or ServiceProvider binding resolved them, so every Dashboard Controller re-implemented
 * the same metric queries inline instead. This Factory follows the exact same naming-convention
 * resolution pattern already established by RoleProfileFactory / RoleValidationFactory
 * ("{Role}" + suffix under a fixed namespace), for architectural consistency.
 */
class DashboardTelemetryFactory
{
    /**
     * Dynamically resolve the telemetry strategy for the given role.
     *
     * @param  string  $role
     * @return \App\Contracts\Telemetry\DashboardTelemetryInterface
     *
     * @throws \InvalidArgumentException If no telemetry strategy class exists for the role.
     */
    public static function make(string $role): DashboardTelemetryInterface
    {
        $formattedRole = Str::studly($role);
        $className = "App\\Services\\Dashboard\\{$formattedRole}DashboardTelemetry";

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Telemetry structural error: Strategy class [{$className}] for role [{$role}] is not defined."
            );
        }

        return app($className);
    }
}
