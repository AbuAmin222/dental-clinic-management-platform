<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financial\StoreSalaryPaymentRequest;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\PaymentService\FinancialAuditLogger;
use App\Services\PaymentService\SalaryPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Exposes the full Financial-owned payroll lifecycle (record → approve/hold/reject/cancel →
 * markPaid). Admin's only payroll surface is Admin\UserSalaryController (base rate policy) —
 * this Controller intentionally has no Admin-reachable route.
 */
class SalaryPaymentController extends Controller
{
    public function __construct(
        private readonly SalaryPaymentService $salaryPaymentService,
        private readonly FinancialAuditLogger $auditLogger,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $payments = SalaryPayment::with('user', 'processedBy.user')
            ->latest()
            ->paginate((int) config('clinic.pagination.financial', 20));

        return Inertia::render('Financial/SalaryPayments/Index', [
            'payments' => $payments,
        ]);
    }

    public function store(StoreSalaryPaymentRequest $request): RedirectResponse
    {
        $this->authorize('create', SalaryPayment::class);

        $data = $request->validated();
        $recipient = User::findOrFail($data['user_id']);

        $payment = $this->salaryPaymentService->record(
            recipient: $recipient,
            processedBy: $request->user()->financial,
            periodStart: $data['pay_period_start'],
            periodEnd: $data['pay_period_end'],
            baseAmount: isset($data['base_amount']) ? (float) $data['base_amount'] : null,
            deductionAmount: (float) ($data['deduction_amount'] ?? 0),
            bonusAmount: (float) ($data['bonus_amount'] ?? 0),
            notes: $data['notes'] ?? null,
        );

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'salary_payment_recorded',
            amountChanged: (float) $payment->amount,
            after: $payment->only(['id', 'user_id', 'status', 'base_amount', 'deduction_amount', 'bonus_amount', 'amount']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Salary payment recorded.'));
    }

    public function approve(Request $request, SalaryPayment $salaryPayment): RedirectResponse
    {
        $this->authorize('approve', $salaryPayment);

        $payment = $this->salaryPaymentService->approve($salaryPayment);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'salary_payment_approved',
            after: $payment->only(['id', 'status']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Salary payment approved.'));
    }

    public function hold(Request $request, SalaryPayment $salaryPayment): RedirectResponse
    {
        $this->authorize('hold', $salaryPayment);

        $reason = $request->validate(['reason' => ['required', 'string', 'max:2000']])['reason'];

        $payment = $this->salaryPaymentService->hold($salaryPayment, $reason);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'salary_payment_held',
            after: $payment->only(['id', 'status', 'notes']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Salary payment placed on hold.'));
    }

    public function cancel(Request $request, SalaryPayment $salaryPayment): RedirectResponse
    {
        $this->authorize('cancel', $salaryPayment);

        $reason = $request->validate(['reason' => ['required', 'string', 'max:2000']])['reason'];

        $payment = $this->salaryPaymentService->cancel($salaryPayment, $reason);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'salary_payment_cancelled',
            after: $payment->only(['id', 'status', 'notes']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Salary payment cancelled.'));
    }

    public function reject(Request $request, SalaryPayment $salaryPayment): RedirectResponse
    {
        $this->authorize('reject', $salaryPayment);

        $reason = $request->validate(['reason' => ['required', 'string', 'max:2000']])['reason'];

        $payment = $this->salaryPaymentService->reject($salaryPayment, $reason);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'salary_payment_rejected',
            after: $payment->only(['id', 'status', 'notes']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Salary payment rejected.'));
    }

    public function markPaid(Request $request, SalaryPayment $salaryPayment): RedirectResponse
    {
        $this->authorize('markPaid', $salaryPayment);

        $payment = $this->salaryPaymentService->markAsPaid($salaryPayment);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'salary_payment_paid',
            after: $payment->only(['id', 'status', 'paid_at']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Salary payment marked as paid.'));
    }
}
