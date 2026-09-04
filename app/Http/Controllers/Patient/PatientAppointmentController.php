<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Enums\AppointmentStatus;
use App\Enums\Permissions\AppointmentPermission;
use App\Exceptions\BusinessRuleViolationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\Appointment\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Str;

/**
 * Class PatientAppointmentController
 *
 * Coordinates self-service processes for verified patients, adhering strictly to SRP.
 *
 * @package App\Http\Controllers\Patient
 */
class PatientAppointmentController extends Controller
{
    /**
     * PatientAppointmentController constructor.
     */
    public function __construct(
        protected readonly AppointmentService $bookingService
    ) {}

    /**
     * This method was missing entirely, despite being wired to the
     * `patient.appointment.index` route in PatientRouteRegistrar.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $name = self::claimValue(AppointmentPermission::Create);
        $this->authorize($name, Appointment::class);


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

        return Inertia::render('Patient/Appointment/Create', [
            'doctors' => $doctors,
        ]);
    }

    /**
     * AppointmentService::bookAppointment() returns an Appointment
     * on success or throws DomainException on conflict — it never returns false.
     *
     * @param StoreAppointmentRequest $request
     * @return RedirectResponse
     */

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $name = self::claimValue(AppointmentPermission::Create);
        $this->authorize($name, Appointment::class);
        $patient = Auth::user()?->patient;

        if (!$patient) {
            abort(403, 'Unauthorized contextual boundary mapping missing.');
        }

        try {
            $status = AppointmentStatus::Pending->value;

            $fullData = array_merge($request->validated(), [
                'status' => $status
            ]);
            $this->bookingService->bookAppointment($fullData, $patient->id);
        } catch (BusinessRuleViolationException $e) {
            return back()->withErrors([
                'start_time' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Your appointment has been requested successfully.');
    }

    /**
     * AppointmentService::bookAppointment() returns an Appointment
     * on success or throws DomainException on conflict — it never returns false.
     *
     * @param StoreAppointmentRequest $request
     * @return RedirectResponse
     */
    public function update(StoreAppointmentRequest $request): RedirectResponse
    {
        $name = self::claimValue(AppointmentPermission::Update);
        $this->authorize($name, Appointment::class);

        $patient = Auth::user()?->patient;

        if (!$patient) {
            abort(403, 'Unauthorized contextual boundary mapping missing.');
        }

        try {

            $this->bookingService->updateAppointment(Appointment, $patient->id);
        } catch (BusinessRuleViolationException $e) {
            return back()->withErrors([
                'start_time' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Your appointment has been requested successfully.');
    }

    /**
     * AppointmentService::bookAppointment() returns an Appointment
     * on success or throws DomainException on conflict — it never returns false.
     *
     * @param StoreAppointmentRequest $request
     * @return RedirectResponse
     */
    public function destroy(StoreAppointmentRequest $request): RedirectResponse
    {
        $name = self::claimValue(AppointmentPermission::Delete);
        $this->authorize($name, Appointment::class);

        $patient = Auth::user()?->patient;

        if (!$patient) {
            abort(403, 'Unauthorized contextual boundary mapping missing.');
        }

        try {
            $status = AppointmentStatus::Pending->value;

            $fullData = array_merge($request->validated(), [
                'status' => $status
            ]);
            $this->bookingService->bookAppointment($fullData, $patient->id);
        } catch (BusinessRuleViolationException $e) {
            return back()->withErrors([
                'start_time' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Your appointment has been requested successfully.');
    }

    private function claimValue(object $object): string
    {
        $value = $object->name;
        $name = Str::lower($value);
        return (string) $name;
    }
}
