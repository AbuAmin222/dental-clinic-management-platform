<?php

declare(strict_types=1);

namespace App\Policies\Payment;

use App\Enums\Permissions\SalaryPaymentPermission;
use App\Enums\UserRole;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\Authorization\PermissionGate;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Was entirely absent before this change — every SalaryPaymentController action relied
 * solely on the route-level `role:financial` middleware (coarse: any financial staff
 * member could do anything to any payment). This adds the fine-grained layer on top,
 * matching the same 'role AND permission' pattern as InvoicePolicy::issue(): the role is
 * still the non-negotiable baseline (a doctor can never touch payroll no matter what
 * permission they're granted), the permission lets an Admin later restrict *which*
 * financial staff can approve/reject/etc., via the RBAC permissions UI, without any
 * further code change.
 */
class SalaryPaymentPolicy
{
    use HandlesAuthorization;

    private const ALLOWED_CRUD_ROLES = [UserRole::Financial->value];
    private const ALLOWED_SENSITIVE_ACTION_ROLES = [UserRole::Admin->value];

    public function __construct(
        private readonly PermissionGate $gate,
    ) {}

    public function create(User $user): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, SalaryPaymentPermission::Record);
    }

    public function approve(User $user, SalaryPayment $salaryPayment): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, SalaryPaymentPermission::Approve);
    }

    public function hold(User $user, SalaryPayment $salaryPayment): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, SalaryPaymentPermission::Hold);
    }

    public function reject(User $user, SalaryPayment $salaryPayment): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, SalaryPaymentPermission::Reject);
    }

    public function cancel(User $user, SalaryPayment $salaryPayment): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, SalaryPaymentPermission::Cancel);
    }

    public function markPaid(User $user, SalaryPayment $salaryPayment): bool
    {
        return $this->gate->allows($user, self::ALLOWED_CRUD_ROLES, SalaryPaymentPermission::MarkPaid);
    }
}
