<?php

declare(strict_types=1);

namespace App\Http\Controllers\Receptionist;

use App\Actions\Appointment\BookAppointmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Receptionist\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AppointmentController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Appointment::with(['patient.user', 'doctor.user', 'invoices']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(static function ($mainQuery) use ($search): void {
                $mainQuery->whereHas('patient.user', static function ($q) use ($search): void {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%");
                })->orWhereHas('doctor.user', static function ($q) use ($search): void {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Receptionist/Appointments/Index', [
            'appointments' => $appointments,
            'filters'      => $request->only(['status', 'search']),
        ]);
    }

    public function create(): InertiaResponse
    {
        // High-efficiency querying: Select only mandatory projections to ensure high memory scalability.
        $patients = Patient::join('users', 'patients.user_id', '=', 'users.id')
            ->select([
                'patients.id',
                'users.first_name',
                'users.last_name',
                'users.identity_number',
            ])
            ->limit(100) // Keep query scope bounded for response speed
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
        StoreAppointmentRequest $request,
        BookAppointmentAction $bookAppointmentAction
    ): RedirectResponse {
        $bookAppointmentAction->execute($request->validated());

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'The appointment has been securely scheduled with absolute concurrency protection.');
    }
}
