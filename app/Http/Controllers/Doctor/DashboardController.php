<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class DashboardController
 *
 * Coordinates and renders the primary real-time workspace metrics and schedule views for authorized Doctor profiles.
 *
 * @package App\Http\Controllers\Doctor
 */
class DashboardController extends Controller
{
    /**
     * Compile and display the active doctor's localized daily appointment queue.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $doctor = $request->user()?->doctor;

        if (!$doctor) {
            abort(404, 'Doctor professional profile context not located.');
        }

        $today = Carbon::today()->toDateString();

        // Optimized query with specific indexed multi-relation eager loading to prevent N+1 queries
        $appointments = Appointment::with(['patient.user', 'invoices'])
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(static function (Appointment $appointment): array {
                return [
                    'id'           => $appointment->id,
                    'patient_id'   => $appointment->patient_id,
                    'patient_name' => $appointment->patient?->user
                        ? sprintf('%s %s', $appointment->patient->user->first_name, $appointment->patient->user->last_name)
                        : 'Unknown Patient',
                    'start_time'   => substr((string) $appointment->start_time, 0, 5),
                    'status'       => $appointment->status,
                    'reason'       => $appointment->reason_for_visit,
                    'has_invoice'  => filled($appointment->invoices),
                ];
            });

        return Inertia::render('Doctor/Dashboard', [
            'appointments' => $appointments,
            'today'        => Carbon::today()->format('l, Y-m-d'),
        ]);
    }

    /**
     * Render the centralized dental record builder interface.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function create(Request $request): InertiaResponse
    {
        $this->authorize('create', \App\Models\DentalRecord::class);

        return Inertia::render('Doctor/DentalRecords/Create');
    }
}
