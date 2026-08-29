<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Exceptions\BusinessRuleViolationException;
use App\Factories\Telemetry\DashboardTelemetryFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Receptionist\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Services\Appointment\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PatientController
 *
 * Coordinates self-service processes for verified patients, adhering strictly to SRP.
 *
 * @package App\Http\Controllers\Patient
 */
class PatientController extends Controller
{
    /**
     * PatientController constructor.
     */
    public function __construct(
        protected readonly AppointmentService $bookingService
    ) {}

    /**
     * Map aggregated telemetry profile statistics and structural invoices ledger.
     *
     * FIX (Coherence Audit): previously re-implemented the exact same query structure
     * already built in PatientDashboardTelemetry (an orphaned class — no Factory or
     * binding resolved it anywhere). Now delegates to it via DashboardTelemetryFactory,
     * keeping the existing prop names ('patient', 'status') so the frontend contract
     * is unaffected.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $telemetry = DashboardTelemetryFactory::make('patient')->getTelemetry(Auth::user());

        return Inertia::render('Patient/Dashboard', [
            'patient'  => $telemetry['patient'],
            'status'   => $telemetry['metrics'],
            'invoices' => $telemetry['patient']?->invoices ?? [],
        ]);
    }

    /**
     * FIX (D9): render the self-service appointment booking form.
     * This method was missing entirely, despite being wired to the
     * `patient.appointment.create` route in PatientRouteRegistrar.
     *
     * @return InertiaResponse
     */
    public function createAppointment(): InertiaResponse
    {
        $this->authorize('create', Appointment::class);

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
    public function storeAppointment(StoreAppointmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $patient = Auth::user()?->patient;

        if (!$patient) {
            abort(403, 'Unauthorized contextual boundary mapping missing.');
        }

        try {
            $this->bookingService->bookAppointment($request->validated(), $patient->id);
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
     * Hydrate centralized point-of-sale visualization terminals.
     *
     * FIX: InvoicePolicy::pay() requires (User, Invoice, Appointment) — the previous call
     * `Gate::authorize('pay', $invoice)` supplied only the Invoice, which would throw a
     * TypeError before ever reaching the actual authorization logic.
     *
     * @param Invoice $invoice
     * @return InertiaResponse
     */
    public function checkoutInvoice(Invoice $invoice): InertiaResponse
    {
        $appointment = $invoice->appointment;

        abort_if($appointment === null, 404, 'This invoice has no linked appointment context.');

        Gate::authorize('pay', [$invoice, $appointment]);

        return Inertia::render('Patient/InvoicePayment', [
            'invoice' => $invoice->load('doctor.user')
        ]);
    }
}
