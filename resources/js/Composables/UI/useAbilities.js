import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

/**
 * Reads the current user's role and effective permission list from Inertia's shared
 * props (auth.role / auth.permissions — populated by HandleInertiaRequests::share() on
 * every request) so components can hide or disable controls without a network round
 * trip per button.
 *
 * IMPORTANT: this is a UI convenience, never the actual security boundary. Every action
 * gated here is — and must remain — independently enforced server-side by a Policy or
 * route middleware. Hiding a button only improves the experience for users who
 * shouldn't see it; it does not, by itself, stop a request sent directly to the
 * endpoint. Treat a mismatch between what this shows and what the server allows as a
 * backend bug to fix there, never as a reason to loosen a check here.
 *
 * @returns {{
 *   can: (permission: string) => boolean,
 *   canAny: (permissions: string[]) => boolean,
 *   canAll: (permissions: string[]) => boolean,
 *   hasRole: (role: string | string[]) => boolean,
 *   role: import('vue').ComputedRef<string | null>,
 *   permissions: import('vue').ComputedRef<string[]>,
 * }}
 */
export function useAbilities() {
  const page = usePage();

  const role = computed(() => page.props.auth?.user?.role ?? null);
  const permissions = computed(() => page.props.auth?.user?.permissions ?? []);

  /**
   * True if the current user holds this exact permission name — direct grant or via
   * one of their roles, exactly mirroring the server's User::hasPermissionTo(). An
   * admin's permission list is the full catalog (see
   * User::effectivePermissionNames()), so this is naturally always true for admins
   * without any special-casing needed here.
   */
  const can = (permission) => permissions.value.includes(permission);

  const canAny = (perms) => perms.some((permission) => can(permission));

  const canAll = (perms) => perms.every((permission) => can(permission));

  const hasRole = (roles) => {
    const list = Array.isArray(roles) ? roles : [roles];
    return role.value !== null && list.includes(role.value);
  };

  return { can, canAny, canAll, hasRole, role, permissions };
}
