<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PatientInvoicePaymentController
 *
 * Exclusively handles payment-related routes and structures to isolate invoicing domain.
 *
 * @package App\Http\Controllers\Patient
 */
class PatientInvoicePaymentController extends Controller
{
    /**
     * Process checkout invoice.
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
