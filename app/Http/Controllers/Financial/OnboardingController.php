<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Implements the mandatory self-onboarding step from the architecture document §2.b.
 * Reachable only by a `financial`-role user whose profile is not yet complete — enforced
 * by EnsureOnboardingCompleted, not re-checked here (Single Responsibility: this Controller
 * only handles the form, not the gate).
 */
class OnboardingController extends Controller
{
    public function show(Request $request): InertiaResponse
    {
        return Inertia::render('Financial/Onboarding/Complete', [
            'financial' => $request->user()?->financial,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Confirmed decision: salary is set exclusively by Admin (on users.base_salary),
        // for EVERY staff role — never self-reported by the employee. This form now only
        // collects employment metadata the officer legitimately owns about themselves.
        $data = $request->validate([
            'employee_number'  => ['required', 'string', 'unique:financials,employee_number'],
            'hiring_date'      => ['required', 'date', 'before_or_equal:today'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:60'],
            'specialization'   => ['nullable', 'string', 'max:255'],
        ]);

        /** @var Financial $financial */
        $financial = $request->user()->financial()->firstOrCreate([]);

        $financial->update([
            ...$data,
            'is_profile_completed' => true,
        ]);

        return redirect()->route('financial.dashboard')->with('success', __('Profile completed — welcome aboard.'));
    }
}
