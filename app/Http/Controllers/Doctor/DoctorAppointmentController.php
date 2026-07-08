<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DoctorAppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('doctor_id', Auth::id())
            ->with(['patient', 'dentalRecord'])
            ->latest()
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'patient_name' => $appointment->patient?->full_name ?? 'Unknown Patient',
                    'reason' => $appointment->reason,
                    'status' => $appointment->status,
                    'start_time' => $appointment->start_time,
                    'has_record' => $appointment->dentalRecord !== null,
                    'record_details' => $appointment->dentalRecord ? [
                        'id' => $appointment->dentalRecord->id,
                        'diagnosis' => $appointment->dentalRecord->diagnosis ?? 'No diagnosis written',
                        'treatment' => $appointment->dentalRecord->treatment ?? 'N/A',
                    ] : null,
                ];
            });

        return Inertia::render('Doctor/Appointments/Index', [
            'appointments' => $appointments
        ]);
    }
}
