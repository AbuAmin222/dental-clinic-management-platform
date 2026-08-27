<?php

declare(strict_types=1);

namespace App\Policies\Payment;

use App\Enums\UserRole;
use App\Models\SalaryPayment;
use App\Models\User;
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

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Financial->value)
            && $user->hasPermissionTo('salary_payments.record');
    }

    public function approve(User $user, SalaryPayment $salaryPayment): bool
    {
        return $user->hasRole(UserRole::Financial->value)
            && $user->hasPermissionTo('salary_payments.approve');
    }

    public function hold(User $user, SalaryPayment $salaryPayment): bool
    {
        return $user->hasRole(UserRole::Financial->value)
            && $user->hasPermissionTo('salary_payments.hold');
    }

    public function reject(User $user, SalaryPayment $salaryPayment): bool
    {
        return $user->hasRole(UserRole::Financial->value)
            && $user->hasPermissionTo('salary_payments.reject');
    }

    public function cancel(User $user, SalaryPayment $salaryPayment): bool
    {
        return $user->hasRole(UserRole::Financial->value)
            && $user->hasPermissionTo('salary_payments.cancel');
    }

    public function markPaid(User $user, SalaryPayment $salaryPayment): bool
    {
        return $user->hasRole(UserRole::Financial->value)
            && $user->hasPermissionTo('salary_payments.mark_paid');
    }
}
