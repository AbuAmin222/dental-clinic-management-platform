<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * SCHEMA/SEEDER GAP FOUND DURING AUDIT:
 *
 * PermissionSeeder populates the `permissions` catalog table, and both Role and
 * Permission models expose a full givePermissionTo()/permissions() API over the
 * `permission_roles` pivot — but nothing in the seeder suite ever actually attached a
 * single permission to a single role. User::hasPermissionTo() falls through to
 * `$this->roles->flatMap(fn (Role $role) => $role->permissions)`, which would silently
 * return an empty collection for every role, meaning direct-permission checks (used for
 * areas with no dedicated Policy class — salary payments, local payment methods, system
 * role/permission management) would never pass for anyone except admin (which bypasses
 * the permission catalog entirely via the hasRole('admin') short-circuit).
 *
 * The mapping below is inferred from the actual capabilities documented in
 * PermissionSeeder's own comments (each permission traced back to a real Policy method
 * or an un-policied service) and from each role's real-world responsibilities in this
 * clinic domain. It is idempotent — safe to re-run — via Role::givePermissionTo(), which
 * calls syncWithoutDetaching() under the hood.
 */
class RolePermissionSeeder extends Seeder
{
    /** @var array<string, string[]> role slug => list of permission name prefixes/exact names */
    private const ROLE_PERMISSIONS = [
        // Admin is intentionally NOT enumerated here with the full catalog for every
        // group — User::hasRole('admin') already short-circuits hasPermissionTo() to
        // true unconditionally (see User::hasPermissionTo()). We still attach the
        // system-management permissions explicitly so the admin UI's "role permissions"
        // screen reflects them accurately instead of appearing empty.
        UserRole::Admin->value => [
            'system.manage_roles',
            'system.manage_permissions',
            'users.viewAny', 'users.view', 'users.create', 'users.update', 'users.delete',
            'users.activate', 'users.manage_salary',
        ],

        UserRole::Doctor->value => [
            'appointments.viewAny', 'appointments.view', 'appointments.update',
            'dental_records.viewAny', 'dental_records.view', 'dental_records.create', 'dental_records.update',
            'patients.viewAny', 'patients.view',
            'pricings.viewAny', 'pricings.view',
            'invoices.view',
        ],

        UserRole::Receptionist->value => [
            'appointments.viewAny', 'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
            'patients.viewAny', 'patients.view', 'patients.create', 'patients.update',
            'invoices.viewAny', 'invoices.view', 'invoices.create',
            'pricings.viewAny', 'pricings.view',
        ],

        UserRole::Financial->value => [
            'invoices.viewAny', 'invoices.view', 'invoices.update', 'invoices.delete',
            'invoices.restore', 'invoices.forceDelete', 'invoices.pay', 'invoices.issue',
            'pricings.viewAny', 'pricings.view', 'pricings.create', 'pricings.update',
            'pricings.delete', 'pricings.restore', 'pricings.forceDelete',
            'patients.viewAny', 'patients.view',
            'salary_payments.record', 'salary_payments.approve', 'salary_payments.hold',
            'salary_payments.reject', 'salary_payments.cancel', 'salary_payments.markPaid',
            'local_payment_methods.manage',
        ],

        UserRole::Patient->value => [
            'appointments.view', 'appointments.create',
            'invoices.view', 'invoices.pay',
            'dental_records.view',
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                $this->command?->warn("⚠️ Role [{$roleName}] not found — skipping permission assignment. Run RoleSeeder first.");
                continue;
            }

            $permissions = Permission::whereIn('name', $permissionNames)->get();

            if ($permissions->isEmpty()) {
                $this->command?->warn("⚠️ No matching permissions found for role [{$roleName}] — run PermissionSeeder first.");
                continue;
            }

            $role->givePermissionTo(...$permissions);
        }

        $this->command?->info('✅ Role → permission assignments synced.');
    }
}
