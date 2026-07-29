<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Receptionist\StoreAppointmentRequest;
use App\Models\Invoice;
use App\Models\Patient;
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
    protected AppointmentService $bookingService;

    /**
     * PatientController constructor.
     *
     * @param AppointmentService $bookingService
     */
    public function __construct(AppointmentService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Map aggregated telemetry profile statistics and structural invoices ledger.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $user = Auth::user();

        $patientData = Patient::where('user_id', $user?->id)->with([
            'appointments' => static function ($query): void {
                $query->with(['doctor.user', 'invoices'])->latest();
            },
            'dentalRecords' => static function ($query): void {
                $query->with(['doctor.user', 'appointment'])->latest();
            },
            'invoices' => static function ($query): void {
                $query->with(['doctor.user', 'appointment'])->latest();
            }
        ])->first();

        $stats = [
            'total_appointments' => $patientData ? $patientData->appointments->count() : 0,
        ];

        return Inertia::render('Patient/Dashboard', [
            'patient' => $patientData,
            'stats'   => $stats,
        ]);
    }

    /**
     * Execute transactional appointment booking with strict validation.
     *
     * @param StoreAppointmentRequest $request
     * @return RedirectResponse
     */
    public function book(StoreAppointmentRequest $request): RedirectResponse
    {
        $patient = Auth::user()?->patient;

        if (!$patient) {
            abort(403, 'Unauthorized contextual boundary mapping missing.');
        }

        $success = $this->bookingService->bookAppointment($request->validated(), $patient);

        if (!$success) {
            return back()->withErrors([
                'start_time' => 'The selected time slot conflicts with an existing appointment for this doctor. Please choose a different time or date.'
            ]);
        }

        return redirect()
            ->route('patient.dashboard')
            ->with('success', 'Your appointment has been requested successfully.');
    }

    /**
     * Hydrate centralized point-of-sale visualization terminals.
     *
     * @param Invoice $invoice
     * @return InertiaResponse
     */
    public function checkoutInvoice(Invoice $invoice): InertiaResponse
    {
        Gate::authorize('pay', $invoice);

        return Inertia::render('Patient/InvoicePayment', [
            'invoice' => $invoice->load('doctor.user')
        ]);
    }
}
