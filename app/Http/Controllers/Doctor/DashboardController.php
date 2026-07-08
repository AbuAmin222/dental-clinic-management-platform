<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

        $doctor = $request->user()->doctor;

        if (!$doctor) {
            abort(404, 'Doctor profile not found.');
        }

        $today = Carbon::today()->toDateString();

        $appointments = Appointment::with(['patient.user', 'invoices'])
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'patient_name' => $appointment->patient->user->first_name . ' ' . $appointment->patient->user->last_name,
                    'start_time' => substr($appointment->start_time, 0, 5),
                    'status' => $appointment->status,
                    'reason' => $appointment->reason_for_visit,
                    'has_invoice' => filled($appointment->invoices),
                ];
            });

        return inertia('Doctor/Dashboard', [
            'appointments' => $appointments,
            'today' => Carbon::today()->format('l, Y-m-d')
        ]);
    }

    public function create(Request $request)
    {
        return inertia('Doctor/DentalRecords/Create');
    }
}
