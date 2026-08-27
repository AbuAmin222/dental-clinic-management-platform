<?php

declare(strict_types=1);

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Receptionist\StoreInvoiceRequest;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Pricing;
use App\Services\PaymentService\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class InvoiceController
 *
 * Governs the billing life-cycle, strictly authorizing via standard InvoicePolicy policies.
 *
 * @package App\Http\Controllers\Receptionist
 */
class InvoiceController extends Controller
{
    public function __construct(
        protected readonly InvoiceService $invoiceService
    ) {}

    /**
     * Show Create/Edit financial invoice for an appointment.
     *
     * @param Appointment $appointment
     * @return InertiaResponse
     */
    public function create(Appointment $appointment): InertiaResponse
    {
        $this->authorize('create', Invoice::class);

        $appointment->load(['patient.user', 'doctor.user']);

        $invoice = Invoice::where('appointment_id', $appointment->id)->first();
        $pricings = Pricing::where('doctor_id', $appointment->doctor_id)->get();

        return Inertia::render('Receptionist/Invoices/Create', [
            'appointment' => $appointment,
            'pricings'    => $pricings,
            'invoice'     => $invoice,
        ]);
    }

    /**
     * Save or update the appointment invoice.
     *
     * @param StoreInvoiceRequest $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function store(StoreInvoiceRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validated();
        $validated['due_date'] = Carbon::parse($validated['due_date'])->toDateTimeString();

        $pricingCatalog = Pricing::whereIn('id', array_column($validated['items'], 'pricing_id'))
            ->get()
            ->keyBy('id');

        $validated['items'] = array_map(
            static fn(array $item): array => [
                'pricing_id' => $item['pricing_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $pricingCatalog->get($item['pricing_id'])?->amount ?? 0,
            ],
            $validated['items'],
        );

        $this->invoiceService->upsertForAppointment($validated, $appointment);

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'Invoice processed successfully!');
    }

    /**
     * Safely delete the invoice associated with the specific appointment context.
     *
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $invoice = Invoice::where('appointment_id', $appointment->id)->first();

        if (!$invoice) {
            return redirect()
                ->route('receptionist.appointments.index')
                ->with('error', 'No invoice found to delete.');
        }

        $this->authorize('delete', [$invoice, $appointment]);

        $invoice->delete();

        return redirect()
            ->route('receptionist.appointments.index')
            ->with('success', 'Invoice deleted successfully!');
    }
}
