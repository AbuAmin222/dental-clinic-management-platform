<?php

namespace App\Policies;

use App\Enums\AdminAccessLevel;
use App\Enums\Permissions\UserPermission;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use App\Services\Authorization\PermissionGate;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class UserPolicy
 *
 * Governs administrative-grade account management via App\Http\Controllers\UserController
 * (generic multi-role create/update/delete — distinct from the role-specific self-service
 * flows: RegisterPatientAction, Fortify's CreateNewUser/UpdateUserProfileInformation).
 *
 * DESIGN NOTE: this Policy is intentionally admin-only. The `admin` role is confirmed
 * as "founded, deliberately deferred" (see DECISIONS_LOG.md). Until it is implemented,
 * UserController's routes are correctly inaccessible to everyone — this is expected
 * behavior, not a bug, and should not be relaxed to give other roles access as a
 * workaround.
 */

class UserPolicy
{
    use HandlesAuthorization, HasClinicalProfiles;

    private const ALLOWED_CRUD_ROLES = [UserRole::Admin->value];
    private const ALLOWED_SENSITIVE_ACTION_ROLES = [UserRole::Admin->value];

    public function __construct(
        private readonly PermissionGate $gate,
    ) {}

    /**
     * Determine whether the user can browse the full account directory.
     */
    public function viewAny(User $user): bool
    {
        return $this->gate->allows($user, UserRole::staffRoleValues(), UserPermission::ViewAny);
    }

    /**
     * Determine whether the user can view a specific account record.
     */
    public function view(User $user, User $target): bool
    {
        return $this->gate->allows($user, UserRole::staffRoleValues(), UserPermission::View) || $user->id === $target->id;
    }

    /**
     * Determine whether the user can create new accounts through the administrative endpoint.
     */
    public function create(User $user): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, UserPermission::Create);
    }

    /**
     * Determine whether the user can update the target account.
     * A user may always update their own account; only an admin may update another's.
     */
    public function update(User $user, User $target): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, UserPermission::Update) || $user->id === $target->id;
    }

    /**
     * Determine whether the user can delete the target account.
     *
     * حماية المسؤول الجذري: لا يجوز حذف حساب SuperAdmin إلا من قِبل
     * SuperAdmin آخر، ولا يجوز أبداً حذف آخر SuperAdmin متبقٍّ في النظام .
     * 
     * The `users.delete` permission is only required on the admin-acting-on-another
     * branch; self-deletion of one's own account must never depend on the admin
     * permission catalog, or a non-admin user could be locked out of closing their
     * own account.
     */
    public function delete(User $user, User $target): bool
    {
        $isAdminActing = $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, UserPermission::Delete);
        $isSelf = $user->id === $target->id;

        if (! ($isAdminActing || $isSelf)) {
            return false;
        }

        if ($target->admin?->isSuperAdmin()) {
            if (! $user->admin?->isSuperAdmin()) {
                return false;
            }

            if (Admin::where('access_level', AdminAccessLevel::SuperAdmin)->count() <= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can activate or reject a pending account
     * (App\Http\Controllers\Admin\UserActivationController). Previously ungated by any
     * Policy — only the route-level `role:admin` middleware protected these actions, so
     * the seeded `users.activate` permission had no actual effect: any Admin could
     * activate/reject/toggle any account regardless of whether that specific permission
     * had been revoked from them via the RBAC UI.
     */
    public function activate(User $user): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, UserPermission::Activate);
    }

    /**
     * Determine whether the user can set or update a staff member's base salary
     * (App\Http\Controllers\Admin\UserSalaryController). Same gap as activate() above —
     * was gated only by route-level `role:admin` middleware, so `users.manage_salary`
     * was seeded but never actually checked.
     */
    public function manageSalary(User $user): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, UserPermission::ManageSalary);
    }
}
