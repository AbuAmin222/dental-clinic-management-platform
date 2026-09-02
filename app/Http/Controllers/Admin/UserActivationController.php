<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserActivationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Closes the gap flagged in FRONTEND_ISSUES.md: EnsureUserIsActive blocks every inactive
 * account behind the `pending-review` holding page, but until this controller there was no
 * admin-side screen or endpoint to actually activate (or reject) those accounts — the gate
 * existed with no key on the other side.
 *
 * Design decision (no prior spec covered this explicitly): "reject" soft-deletes the
 * account rather than introducing a new status column, since `users` already has
 * SoftDeletes and no `status` enum exists beyond `is_active`. A rejected applicant can
 * re-register with the same email once the row is soft-deleted. If a future requirement
 * needs a distinguishable "rejected" state (vs. a normal deactivation), that needs its own
 * migration + decision — flagged here rather than guessed.
 */
class UserActivationController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $pending = User::where('is_active', false)
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'])
            ->with(['roles:id,name,display_name'])
            ->oldest()
            ->paginate((int) config('clinic.pagination.default', 15))
            ->withQueryString();

        return Inertia::render('Admin/Users/PendingReviews', [
            'pendingUsers' => $pending,
        ]);
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('activate', User::class);

        abort_if($user->is_active, 409, 'This account is already active.');

        $user->forceFill(['is_active' => true])->save();

        return redirect()
            ->route('admin.users.pendingReviews')
            ->with('success', __('Account activated. :name can now sign in.', ['name' => $user->first_name]));
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->authorize('activate', User::class);

        abort_if($user->is_active, 409, 'Cannot reject an already-active account.');

        $user->delete();

        return redirect()
            ->route('admin.users.pendingReviews')
            ->with('success', __('Account rejected.'));
    }
    public function update(UpdateUserActivationRequest $request, User $user): RedirectResponse
    {
        $this->authorize('activate', User::class);

        $user->forceFill(['is_active' => $request->boolean('is_active')])->save();

        return redirect()
            ->back()
            ->with('success', $request->boolean('is_active')
                ? __('Account activated successfully.')
                : __('Account deactivated.'));
    }
}
