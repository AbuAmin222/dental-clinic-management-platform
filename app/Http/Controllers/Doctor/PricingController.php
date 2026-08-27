<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StorePricingRequest;
use App\Http\Requests\Doctor\UpdatePricingRequest;
use App\Models\Pricing;
use App\Services\PaymentService\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PricingController
 *
 * Controls custom operational fee indices and treatment cost configurations cataloged metrics managed explicitly per doctor profile.
 *
 * store()/update() previously bypassed the already-built StorePricingRequest /
 * UpdatePricingRequest (both were orphaned — never referenced by any Controller) and
 * PricingService, re-implementing the same validation and persistence logic inline.
 * Both are now wired in, closing the duplication and making PricingService reachable.
 *
 * @package App\Http\Controllers\Doctor
 */
class PricingController extends Controller
{
    public function __construct(
        protected readonly PricingService $pricingService
    ) {}

    /**
     * Display the dynamic personal service pricing directory sheet managed by the authenticating Doctor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Pricing::class);

        $doctor = $request->user()?->doctor;

        if (!$doctor) {
            abort(404, 'Doctor credentials portfolio tracking entity not found.');
        }

        $pricings = Pricing::where('doctor_id', $doctor->id)
            ->when($request->input('search'), static function ($query, $search): void {
                $query->where('service_name', 'like', "%{$search}%");
            })
            ->orderBy('service_name', 'asc')
            ->get();

        return Inertia::render('Doctor/Pricings/Index', [
            'pricings' => $pricings,
            'filters'  => $request->only(['search']),
        ]);
    }

    /**
     * Store a brand-new custom clinical price index item entry securely attached inside current domain tracking scopes.
     *
     * @param  StorePricingRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePricingRequest $request): RedirectResponse
    {
        $this->authorize('create', Pricing::class);

        $doctor = $request->user()?->doctor;

        if (!$doctor) {
            abort(403, 'Action blocked: Insufficient institutional medical claims profiles.');
        }

        $this->pricingService->createPricing($request->validated(), $doctor->id);

        return redirect()->back()->with('success', 'Service catalog entry injected successfully.');
    }

    /**
     * Update the attributes of an existing verified price catalog resource.
     *
     * @param  UpdatePricingRequest  $request
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePricingRequest $request, Pricing $pricing): RedirectResponse
    {
        $this->authorize('update', $pricing);

        $this->pricingService->updatePricing($pricing, $request->validated());

        return redirect()->back()->with('success', 'Service transaction configurations synchronized.');
    }

    /**
     * Permanently excise a targeted pricing index record safely backed up by direct authorization gate parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Pricing $pricing): RedirectResponse
    {
        $this->authorize('delete', $pricing);

        $pricing->delete();

        return redirect()->back()->with('success', 'Service pricing entry decoupled safely.');
    }
}
