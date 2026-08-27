<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the first real entry in the permission catalog. Until this seeder, `permissions`
 * was empty unless an Admin manually created one through the Admin/Permissions UI — which
 * meant useAbilities.js's can('invoices.approve') check on the frontend (and the
 * equivalent gate anywhere it's added server-side) always evaluated false for everyone,
 * including financial staff who legitimately need it. Must run after RoleSeeder (needs
 * the 'financial' role to already exist to attach to).
 */
class PermissionSeeder extends Seeder
{
    /**
     * @return array<int, array{name: string, display_name: string, group: string, roles: string[]}>
     */
    private function catalog(): array
    {
        return [
            [
                'name' => 'invoices.approve',
                'display_name' => 'Issue and approve invoices',
                'group' => 'invoices',
                'roles' => ['financial'],
            ],

            // SalaryPaymentPolicy (app/Policies/Payment/SalaryPaymentPolicy.php) gates
            // every action in Financial\SalaryPaymentController on one of these six
            // permissions ANDed with the 'financial' role. Before this addition, none of
            // them existed in the catalog, so hasPermissionTo() was false for everyone —
            // the entire payroll workflow (record → approve/hold/reject/cancel →
            // markPaid) was unusable by any financial staff member on a fresh seed,
            // despite the controller, service, and policy all being fully implemented
            // and individually correct. Granted to 'financial' by default here to match
            // the policy's own stated intent — the role stays the non-negotiable
            // baseline, an Admin can later narrow who specifically holds each of these
            // via the RBAC permissions UI without touching this seeder again.
            [
                'name' => 'salary_payments.record',
                'display_name' => 'Record a new salary payment',
                'group' => 'salary_payments',
                'roles' => ['financial'],
            ],
            [
                'name' => 'salary_payments.approve',
                'display_name' => 'Approve a salary payment',
                'group' => 'salary_payments',
                'roles' => ['financial'],
            ],
            [
                'name' => 'salary_payments.hold',
                'display_name' => 'Place a salary payment on hold',
                'group' => 'salary_payments',
                'roles' => ['financial'],
            ],
            [
                'name' => 'salary_payments.reject',
                'display_name' => 'Reject a salary payment',
                'group' => 'salary_payments',
                'roles' => ['financial'],
            ],
            [
                'name' => 'salary_payments.cancel',
                'display_name' => 'Cancel a salary payment',
                'group' => 'salary_payments',
                'roles' => ['financial'],
            ],
            [
                'name' => 'salary_payments.mark_paid',
                'display_name' => 'Mark a salary payment as paid',
                'group' => 'salary_payments',
                'roles' => ['financial'],
            ],

            // PaymentMethod management has no dedicated Policy today (authorization is
            // the coarse `role:financial` route middleware only, plus per-record
            // ownership checks inside LocalPaymentMethodController itself). This
            // permission exists so an Admin can optionally restrict which financial
            // staff manage the clinic's bank/e-wallet configuration — granted to
            // 'financial' by default so nothing changes unless an Admin acts on it.
            [
                'name' => 'payment_methods.manage',
                'display_name' => 'Manage local payment method configuration',
                'group' => 'payment_methods',
                'roles' => ['financial'],
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->catalog() as $entry) {
            $permission = Permission::updateOrCreate(
                ['name' => $entry['name']],
                [
                    'display_name' => $entry['display_name'],
                    'group' => $entry['group'],
                ]
            );

            $roleIds = Role::whereIn('name', $entry['roles'])->pluck('id');
            $permission->roles()->syncWithoutDetaching($roleIds);
        }
    }
}
