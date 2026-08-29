<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\Telemetry\DashboardTelemetryInterface;
use App\Models\Patient;
use App\Models\User;
use Override;

/**
 * Telemetry strategy implementation dedicated to compiling personal clinical histories, appointments, and billing data for Patients.
 */
class PatientDashboardTelemetry implements DashboardTelemetryInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTelemetry(User $user): array
    {
        $patientData = Patient::where('user_id', $user->id)
            ->with([
                'appointments' => static function ($query): void {
                    $query->with(['doctor.user', 'invoice'])->latest();
                },
                'dentalRecords' => static function ($query): void {
                    $query->with(['doctor.user', 'appointment'])->latest();
                },
                'invoices' => static function ($query): void {
                    $query->with(['doctor.user', 'appointment'])->latest();
                }
            ])->first();

        return [
            'patient' => $patientData,
            'metrics' => [
                'total_appointments'   => $patientData ? $patientData->appointments->count() : 0,
                'pending_appointments' => $patientData
                    ? $patientData->appointments->where('status', 'pending')->count()
                    : 0,
                'total_treatments'     => $patientData
                    ? $patientData->appointments->where('status', 'completed')->count()
                    : 0,
                'remaining_balance'    => $patientData
                    ? $patientData->invoices->sum('balance_amount')
                    : 0,
            ],
            'view_path' => 'Patient/Dashboard'
        ];
    }
}
