<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\Telemetry\DashboardTelemetryInterface;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Override;

/**
 * Telemetry strategy implementation dedicated to compiling clinic-wide operations, active appointments, and financial collections for Receptionists.
 */
class ReceptionistDashboardTelemetry implements DashboardTelemetryInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTelemetry(User $user): array
    {
        return [
            'metrics' => [
                'active_appointments' => Appointment::whereIn('status', ['pending', 'scheduled', 'confirmed', 'no_show'])->count(),
                'pending_collections' => Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count(),
                'total_patients'      => Patient::count(),
            ],
            'view_path' => 'Receptionist/Dashboard'
        ];
    }
}
