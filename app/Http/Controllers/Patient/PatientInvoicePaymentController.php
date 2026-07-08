<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\Payment\CurrencyConverter;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientInvoicePaymentController extends Controller
{
    /**
     * Called by InvoicePayment.vue's submitPayment() via axios.
     * Expects JSON back with a redirect_url - never a redirect response here.
     */
    public function process(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'payment_method' => [
                'required',
                'string',
                Rule::in(['visa', 'mastercard', 'jawwal_pay', 'palpay', 'paypal']),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->balance_amount],
        ]);

        try {
            $result = PaymentManager::make($validated['payment_method'])
                ->initializePayment($invoice, $validated['amount']);

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'The selected payment gateway is currently undergoing maintenance.',
            ], 422);
        }
    }

    /**
     * The patient's browser lands here after the sandbox gateway page.
     * In a real integration this is where you'd verify the gateway's
     * signature instead of just trusting the redirect.
     */
    public function callback(Request $request, string $gateway, string $tx)
    {
        $transaction = PaymentTransaction::where('transaction_id', $tx)->firstOrFail();
        $invoice = $transaction->invoice;

        $this->authorize('pay', $invoice);

        if ($transaction->status === 'completed') {
            return redirect()->route('patient.dashboard')
                ->with('success', 'This payment was already confirmed.');
        }

        DB::transaction(function () use ($transaction, $invoice) {
            $transaction->update(['status' => 'completed']);

            $amountInIls = $transaction->currency === 'ILS'
                ? (float) $transaction->amount
                : app(CurrencyConverter::class)->usdToIls((float) $transaction->amount);

            $invoice->pay($amountInIls);
        });

        return redirect()->route('patient.dashboard')
            ->with('success', 'Payment completed successfully!');
    }
}
