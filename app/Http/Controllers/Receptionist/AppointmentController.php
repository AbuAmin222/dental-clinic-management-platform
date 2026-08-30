<?php

declare(strict_types=1);

namespace App\Http\Controllers\Receptionist;

use App\Actions\Appointment\BookAppointmentAction;
use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Receptionist\StoreReceptionistAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AppointmentController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $query = Appointment::with(['patient.user', 'doctor.user', 'invoice']);

        if (
            $request->filled('status') && $request->input('status') !== 'all'
            && in_array($request->input('status'), AppointmentStatus::values(), true)
        ) {
            $query->where('status', $request->input('status'));
        }

        // Smart Search by Patient Name or Patient-ID
        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            // Lock conditions for security other searchs service
            $query->where(static function ($mainQuery) use ($search): void {

                // Search in Patient data.
                $mainQuery->whereHas('patient.user', static function ($q) use ($search): void {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%");

                    // Serach in Doctor data
                })->orWhereHas('doctor.user', static function ($q) use ($search): void {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('invoice_status') && $request->invoice_status !== 'all') {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('status', $request->invoice_status);
            });
        }

        $appointments = $query->latest()->paginate((int) config('clinic.pagination.default', 15))->withQueryString();

        return Inertia::render('Receptionist/Appointments/Index', [
            'appointments' => $appointments,
            'filters'      => $request->only(['status', 'search']),
        ]);
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Appointment::class);

        $patients = Patient::join('users', 'patients.user_id', '=', 'users.id')
            ->select([
                'patients.id',
                'users.first_name',
                'users.last_name',
                'users.identity_number',
            ])
            ->limit(100)
            ->get()
            ->map(static fn($p): array => [
                'id'   => $p->id,
                'name' => "{$p->first_name} {$p->last_name} ({$p->identity_number})",
            ]);

        $doctors = Doctor::join('users', 'doctors.user_id', '=', 'users.id')
            ->leftJoin('specializations', 'doctors.specialization_id', '=', 'specializations.id')
            ->select([
                'doctors.id',
                'users.first_name',
                'users.last_name',
                'specializations.name as specialization_name',
            ])
            ->get()
            ->map(static fn($d): array => [
                'id'             => $d->id,
                'name'           => "Dr. {$d->first_name} {$d->last_name}",
                'specialization' => $d->specialization_name ?? 'General Practice',
            ]);

        return Inertia::render('Receptionist/Appointments/Create', [
            'patients' => $patients,
            'doctors'  => $doctors,
        ]);
    }

    public function store(
        StoreReceptionistAppointmentRequest $request,
        BookAppointmentAction $bookAppointmentAction
    ): RedirectResponse {
        $this->authorize('create', Appointment::class);

        $status = AppointmentStatus::Confirmed->value;

        $fullData = array_merge($request->validated(), [
            'status' => $status,
        ]);

        $bookAppointmentAction->execute($fullData);

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'The appointment has been securely scheduled with absolute concurrency protection.');
    }

    /**
     * This method was missing entirely, despite being wired to the
     * `receptionist.appointments.updateStatus` PATCH route.
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(AppointmentStatus::values()),
            ],
        ]);

        $appointment->update(['status' => $validated['status']]);

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'Appointment status updated successfully.');
    }
}
