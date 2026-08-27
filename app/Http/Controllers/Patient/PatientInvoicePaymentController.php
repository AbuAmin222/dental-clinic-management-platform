<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\Payment\GlobalPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Exclusively handles the automated global-gateway payment execution (initialization +
 * callback confirmation). The checkout VIEW itself is rendered by
 * Patient\PatientController::checkoutInvoice() — this Controller only executes the
 * transaction, closing the naming overlap that previously existed between the two.
 */
class PatientInvoicePaymentController extends Controller
{

    public function __construct(
        private readonly GlobalPaymentService $globalPaymentService,
    ) {}

    /**
     * Initiates an automated gateway payment and redirects the patient to the gateway
     * (sandbox in this environment). Only the invoice's current `due_amount` may be paid —
     * never a client-supplied figure — closing off a trivial "pay less than owed" tampering
     * vector.
     */
    public function process(Request $request, Invoice $invoice): RedirectResponse
    {
        $appointment = $invoice->appointment;

        abort_if($appointment === null, 404, 'This invoice has no linked appointment context.');

        Gate::authorize('pay', [$invoice, $appointment]);

        $validated = $request->validate([
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::automatedGatewayValues())],
        ]);

        if ((float) $invoice->due_amount <= 0.0) {
            return back()->withErrors(['payment_method' => __('This invoice has no remaining balance to pay.')]);
        }

        $result = $this->globalPaymentService->initialize(
            $invoice,
            (float) $invoice->due_amount,
            $validated['payment_method'],
        );

        return redirect()->away($result['redirect_url']);
    }

    /**
     * Gateway callback endpoint. In this environment the sandbox controller (Patient\
     * PaymentSandboxController) simulates the gateway and links back here with a `status`
     * query parameter — a real gateway integration would replace that with its own
     * signature-verified webhook payload, but the confirmation logic below (risk assessment,
     * invoice crediting) is unaffected by that swap since it all lives in GlobalPaymentService.
     */
    public function callback(Request $request, string $gateway, string $tx): RedirectResponse
    {
        $transaction = PaymentTransaction::where('transaction_id', $tx)->firstOrFail();

        if ($transaction->status !== PaymentTransactionStatus::Pending) {
            return $this->redirectToOutcome($transaction);
        }

        $gatewayReportedSuccess = $request->query('status') === 'success';

        if (! $gatewayReportedSuccess) {
            $transaction->update(['status' => PaymentTransactionStatus::Failed]);

            return redirect()
                ->route('patient.invoices.checkout', $transaction->invoice_id)
                ->with('error', __('Payment was not completed.'));
        }

        try {
            $this->globalPaymentService->confirmSuccessfulTransaction($transaction);
        } catch (BusinessRuleViolationException $e) {
            return redirect()
                ->route('patient.invoices.checkout', $transaction->invoice_id)
                ->with('error', $e->getMessage());
        }

        return $this->redirectToOutcome($transaction->refresh());
    }

    private function redirectToOutcome(PaymentTransaction $transaction): RedirectResponse
    {
        return match ($transaction->status) {
            PaymentTransactionStatus::Completed      => redirect()->route('patient.dashboard')->with('success', __('Payment completed successfully.')),
            PaymentTransactionStatus::HeldForReview  => redirect()->route('patient.dashboard')->with('success', __('Payment received and is under review.')),
            default           => redirect()->route('patient.invoices.checkout', $transaction->invoice_id)->with('error', __('Payment could not be completed.')),
        };
    }
}
