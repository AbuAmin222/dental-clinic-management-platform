<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financial\StoreLocalPaymentMethodRequest;
use App\Models\LocalPaymentMethod;
use App\Services\PaymentService\FinancialAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LocalPaymentMethodController extends Controller
{
    public function __construct(
        private readonly FinancialAuditLogger $auditLogger,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $methods = LocalPaymentMethod::where('financial_id', $request->user()?->financial?->id)->get();

        return Inertia::render('Financial/PaymentMethods/Index', ['methods' => $methods]);
    }

    public function store(StoreLocalPaymentMethodRequest $request): RedirectResponse
    {
        $method = LocalPaymentMethod::create([
            ...$request->validated(),
            'financial_id' => $request->user()->financial->id,
        ]);

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'local_method_created',
            after: $method->only(['id', 'title', 'is_active', 'is_visible_to_patient']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Payment method added.'));
    }

    public function update(StoreLocalPaymentMethodRequest $request, LocalPaymentMethod $localPaymentMethod): RedirectResponse
    {
        $this->authorizeOwnership($request, $localPaymentMethod);

        $before = $localPaymentMethod->only(['title', 'is_active', 'is_visible_to_patient']);
        $localPaymentMethod->update($request->validated());

        $this->auditLogger->log(
            actor: $request->user(),
            action: 'local_method_updated',
            before: $before,
            after: $localPaymentMethod->only(['title', 'is_active', 'is_visible_to_patient']),
            ip: $request->ip(),
        );

        return redirect()->back()->with('success', __('Payment method updated.'));
    }

    public function destroy(Request $request, LocalPaymentMethod $localPaymentMethod): RedirectResponse
    {
        $this->authorizeOwnership($request, $localPaymentMethod);

        $localPaymentMethod->delete();

        return redirect()->back()->with('success', __('Payment method removed.'));
    }

    /**
     * A financial officer may only manage the payment methods they themselves created.
     * No dedicated Policy/Strategy pair was introduced for this single ownership check —
     * it is a simple equality check, not a multi-role authorization matrix like the other
     * 5 domains, so a full Policy→Factory→Strategy chain would be needless ceremony here.
     */
    private function authorizeOwnership(Request $request, LocalPaymentMethod $method): void
    {
        abort_unless(
            $method->financial_id === $request->user()?->financial?->id,
            403,
            'You may only manage your own payment methods.'
        );
    }
}
