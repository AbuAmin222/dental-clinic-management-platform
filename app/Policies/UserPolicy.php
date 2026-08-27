<?php

namespace App\Policies;

use App\Enums\AdminAccessLevel;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
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

    /**
     * Determine whether the user can browse the full account directory.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Admin->value);
    }

    /**
     * Determine whether the user can view a specific account record.
     */
    public function view(User $user, User $target): bool
    {
        return $user->hasRole(UserRole::Admin->value) || $user->id === $target->id;
    }

    /**
     * Determine whether the user can create new accounts through the administrative endpoint.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin->value);
    }

    /**
     * Determine whether the user can update the target account.
     * A user may always update their own account; only an admin may update another's.
     */
    public function update(User $user, User $target): bool
    {
        return $user->hasRole(UserRole::Admin->value) || $user->id === $target->id;
    }

    /**
     * Determine whether the user can delete the target account.
     *
     * حماية المسؤول الجذري (2026-08-22): لا يجوز حذف حساب SuperAdmin إلا من قِبل
     * SuperAdmin آخر، ولا يجوز أبداً حذف آخر SuperAdmin متبقٍّ في النظام — بدون هذا
     * القيد، حذف الحساب الوحيد صاحب `AdminAccessLevel::SuperAdmin` كان سيقفل كل مسارات
     * إدارة الأدوار/الصلاحيات في النظام بلا رجعة (لا أحد يملك صلاحية استعادتها بعدها).
     */
    public function delete(User $user, User $target): bool
    {
        if (! ($user->hasRole(UserRole::Admin->value) || $user->id === $target->id)) {
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
}
