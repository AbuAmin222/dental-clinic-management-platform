<?php

declare(strict_types=1);

namespace App\Contracts\Telemetry;

use App\Models\User;

/**
 * Strategy contract for retrieving role-specific dashboard metrics and telemetry payloads.
 */
interface DashboardTelemetryInterface
{
    /**
     * Compile and return the complete analytical and telemetry dataset for the specified user.
     *
     * @param User $user The authenticated domain user requesting dashboard information.
     * @return array<string, mixed> Structured payload containing telemetry metrics and view specifications.
     */
    public function getTelemetry(User $user): array;
}
