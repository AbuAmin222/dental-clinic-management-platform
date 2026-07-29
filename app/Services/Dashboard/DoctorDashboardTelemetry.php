<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\Telemetry\DashboardTelemetryInterface;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Override;

/**
 * Telemetry strategy implementation dedicated to compiling medical and operational metrics for Doctors.
 */
class DoctorDashboardTelemetry implements DashboardTelemetryInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTelemetry(User $user): array
    {
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return ['metrics' => [], 'view_path' => 'Doctor/Dashboard'];
        }

        return [
            'metrics' => [
                'today_appointments' => Appointment::where('doctor_id', $doctor->id)
                    ->where('appointment_date', today()->toDateString())
                    ->where('status', 'confirmed')
                    ->count(),
                'total_consultations' => Appointment::where('doctor_id', $doctor->id)
                    ->where('status', 'completed')
                    ->count(),
            ],
            'view_path' => 'Doctor/Dashboard'
        ];
    }
}
