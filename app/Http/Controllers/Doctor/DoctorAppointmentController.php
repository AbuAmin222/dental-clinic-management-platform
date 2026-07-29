<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class DoctorAppointmentController
 *
 * Handles indexing and history verification logs of all appointments registered under an explicit doctor profile.
 *
 * @package App\Http\Controllers\Doctor
 */
class DoctorAppointmentController extends Controller
{
    /**
     * Fetch and present the comprehensive historical ledger of appointments assigned to the active doctor profile.
     *
     * @return \Inertia\Response
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $doctor = Auth::user()?->doctor;

        if (!$doctor) {
            abort(404, 'Doctor operational contextual boundary mapping missing.');
        }

        // Optimized query: Eager load patient.user to solve N+1 access patterns for patient name resolution
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->with(['patient.user', 'dentalRecord'])
            ->latest()
            ->get()
            ->map(static function (Appointment $appointment): array {
                $patientUser = $appointment->patient?->user;
                $patientName = $patientUser
                    ? sprintf('%s %s', $patientUser->first_name, $patientUser->last_name)
                    : 'Unknown Patient';

                return [
                    'id'             => $appointment->id,
                    'patient_id'     => $appointment->patient_id,
                    'patient_name'   => $patientName,
                    'reason'         => $appointment->reason_for_visit ?? 'N/A',
                    'status'         => $appointment->status,
                    'start_time'     => substr((string) $appointment->start_time, 0, 5),
                    'has_record'     => $appointment->dentalRecord !== null,
                    'record_details' => $appointment->dentalRecord ? [
                        'id'        => $appointment->dentalRecord->id,
                        'diagnosis' => $appointment->dentalRecord->condition_type,
                        'treatment' => $appointment->dentalRecord->description,
                    ] : null,
                ];
            });

        return Inertia::render('Doctor/Appointments/Index', [
            'appointments' => $appointments
        ]);
    }
}
