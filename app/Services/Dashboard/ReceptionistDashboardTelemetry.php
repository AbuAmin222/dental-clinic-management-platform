<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\Telemetry\DashboardTelemetryInterface;
use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
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
                'active_appointments' => Appointment::whereIn('status', [
                    AppointmentStatus::Pending,
                    AppointmentStatus::Scheduled,
                    AppointmentStatus::Confirmed,
                    AppointmentStatus::Stopped,
                ])->count(),
                'pending_collections' => Invoice::whereIn('status', [
                    InvoiceStatus::Pending,
                    InvoiceStatus::PartiallyPaid,
                ])->count(),
                'total_patients'      => Patient::count(),
            ],
            'view_path' => 'Receptionist/Dashboard'
        ];
    }
}
