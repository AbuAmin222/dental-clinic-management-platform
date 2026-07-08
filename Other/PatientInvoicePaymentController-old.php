<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;

class PatientInvoicePaymentController extends Controller
{
    /**
     * Called by InvoicePayment.vue's submitPayment() via axios.
     * Expects JSON back with a redirect_url - never a redirect response here.
     */
    public function process(Request $request, Invoice $invoice): RedirectResponse
    {
        // 1. التحقق من البيانات القادمة من الواجهة
        $request->validate([
            'gateway' => 'required|in:bop,jawwal_pay,palpay,paypal',
            'amount'  => 'required|numeric|min:1|max:' . $invoice->balance_amount,
        ]);

        // 2. توليد رقم معاملة فريد وآمن
        $transactionId = 'TXN-' . strtoupper(uniqid());

        // 3. حفظ القيمة المراد دفعها مؤقتاً في الجلسة (Session) للتحقق منها لاحقاً عند العودة
        session()->put("payment_{$transactionId}", [
            'invoice_id'  => $invoice->id,
            'amount_paid' => $request->amount,
        ]);

        // 4. التوجيه إلى مسار الـ Sandbox المكتوب في web.php وتمرير البيانات عبر الـ Query Params
        return redirect()->route('patient.payment.sandbox.gateway', [
            'gateway' => $request->gateway,
            'amount'  => $request->amount,
            'tx'      => $transactionId
        ]);
    }


    /**
     * استقبال النتيجة بعد ضغط زر المحاكاة في الـ Sandbox
     * المسار المطابق: patient.payment.callback
     */
    public function callback(Request $request, string $gateway, string $tx): RedirectResponse
    {
        // 1. جلب بيانات الدفع المؤقتة من الجلسة باستخدام رقم المعاملة الفريد {tx}
        $sessionKey = "payment_{$tx}";
        $paymentData = session()->get($sessionKey);
        $status = $request->query('status');

        // 2. التحقق من صحة المعاملة وحالة النجاح
        if (!$paymentData || $status !== 'success') {
            return redirect()->route('patient.dashboard')
                ->with('error', 'The payment process was cancelled or failed.');
        }

        // 3. جلب الفاتورة وتحديث البيانات المالية بأمان
        $invoice = Invoice::findOrFail($paymentData['invoice_id']);

        $newBalance = $invoice->balance_amount - $paymentData['amount_paid'];
        $newStatus = $newBalance <= 0 ? 'paid' : 'partially_paid';

        $invoice->update([
            'balance_amount' => max(0, $newBalance),
            'status'         => $newStatus
        ]);

        // 4. تنظيف الجلسة وحذف البيانات المؤقتة
        session()->forget($sessionKey);

        return redirect()->route('patient.dashboard')
            ->with('success', "Excellent! Your payment via " . strtoupper($gateway) . " has been computed successfully.");
    }
}
