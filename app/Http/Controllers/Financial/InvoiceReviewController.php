<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financial;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PaymentService\FinancialAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Implements the confirmed financial-responsibility split (architecture document §5.a):
 * a receptionist may only submit an invoice REQUEST (Invoice stays in `draft`); only a
 * Financial officer may formally issue it, moving it to `pending` so the patient can be
 * asked to pay. Every issuance is written to the immutable FinancialAuditLog.
 */
class InvoiceReviewController extends Controller
{
    public function __construct(
        private readonly FinancialAuditLogger $auditLogger,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $draftInvoices = Invoice::where('status', InvoiceStatus::Draft)
            ->with(['patient.user', 'doctor.user', 'items'])
            ->latest()
            ->paginate((int) config('clinic.pagination.financial', 20));

        return Inertia::render('Financial/Invoices/Index', [
            'invoices' => $draftInvoices,
        ]);
    }

    /**
     * Issues a draft invoice request: draft -> pending. Uses the State Pattern's
     * transitionTo(), so an invoice that is not actually in `draft` (e.g. already issued
     * by someone else in a race) fails loudly with IllegalInvoiceStateTransitionException
     * instead of silently double-issuing.
     */
    public function issue(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('issue', $invoice);

        $before = $invoice->only(['status']);

        $invoice->transitionTo(InvoiceStatus::Pending);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'invoice_issued',
            invoice: $invoice,
            before: $before,
            after: $invoice->only(['status']),
            ip: $request->ip(),
        );

        return redirect()
            ->route('financial.invoices.index')
            ->with('success', __('Invoice issued to patient.'));
    }
}
