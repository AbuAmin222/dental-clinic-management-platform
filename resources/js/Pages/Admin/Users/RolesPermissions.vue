<template>
  <AppLayout :title="`${user.first_name} ${user.last_name} — Access`">
    <template #header>
      <div>
        <Link
          :href="route('admin.users.index')"
          class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition-colors"
        >
          &larr; All users
        </Link>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight mt-1">
          {{ user.first_name }} {{ user.last_name }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">{{ user.email }}</p>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto space-y-8">
        <!-- Roles -->
        <section class="space-y-3">
          <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider ps-1">
            Roles
          </h2>
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
            <div class="flex flex-wrap gap-2">
              <span
                v-for="role in currentRoles"
                :key="role.id"
                class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full text-xs font-semibold border bg-indigo-50 border-indigo-200 text-indigo-700"
              >
                {{ role.display_name }}
                <span v-if="role.is_primary" class="text-[9px] font-bold uppercase tracking-wide opacity-70">
                  primary
                </span>
                <button
                  type="button"
                  :disabled="roleActionPending === role.name"
                  class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-black/10 disabled:opacity-40 transition-colors"
                  :title="`Remove ${role.display_name}`"
                  @click="confirmRemoveRole(role)"
                >
                  <svg viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                    <path
                      fill-rule="evenodd"
                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </span>
              <span v-if="currentRoles.length === 0" class="text-xs text-gray-300 italic py-1.5">
                No role assigned yet.
              </span>
            </div>

            <div class="flex items-end gap-3 pt-3 border-t border-gray-50">
              <div class="flex-1">
                <InputLabel value="Assign a role" class="text-xs" />
                <select
                  v-model="roleToAssign"
                  class="mt-1 w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 outline-none focus:border-indigo-500 focus:bg-white transition-all"
                >
                  <option value="" disabled>Choose a role…</option>
                  <option
                    v-for="role in assignableRoles"
                    :key="role.id"
                    :value="role.name"
                  >
                    {{ role.display_name }}
                  </option>
                </select>
              </div>
              <label class="flex items-center gap-2 text-xs font-medium text-gray-500 pb-2.5">
                <input v-model="assignAsPrimary" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                Primary
              </label>
              <button
                type="button"
                :disabled="!roleToAssign || roleActionPending === roleToAssign"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
                @click="assignRole"
              >
                {{ roleActionPending === roleToAssign ? "Assigning…" : "Assign" }}
              </button>
            </div>
          </div>
        </section>

        <!-- Direct permissions -->
        <section class="space-y-3">
          <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider ps-1">
            Individual permissions
          </h2>
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
            <p class="text-xs text-gray-400">
              Grants a permission to <strong>only this user</strong>, independently of
              their role(s). Use this for exceptions — e.g. one receptionist who also
              needs a finance permission, without giving it to every receptionist.
            </p>

            <div class="flex flex-wrap gap-2">
              <PermissionBadge
                v-for="perm in effective"
                :key="perm.name"
                :name="perm.name"
                :display-name="perm.display_name"
                :source="perm.source"
                :removable="perm.source === 'direct'"
                :removing="permPending === perm.name"
                @remove="confirmRevokePermission(perm)"
              />
              <span v-if="effective.length === 0" class="text-xs text-gray-300 italic py-1">
                No effective permissions (direct or via role) yet.
              </span>
            </div>

            <div class="flex items-end gap-3 pt-3 border-t border-gray-50">
              <div class="flex-1">
                <InputLabel value="Grant a direct permission" class="text-xs" />
                <select
                  v-model="permissionToGrant"
                  class="mt-1 w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 outline-none focus:border-indigo-500 focus:bg-white transition-all"
                >
                  <option value="" disabled>Choose a permission…</option>
                  <option
                    v-for="perm in grantablePermissions"
                    :key="perm.id"
                    :value="perm.name"
                  >
                    {{ perm.display_name }} ({{ perm.group || "Ungrouped" }})
                  </option>
                </select>
              </div>
              <button
                type="button"
                :disabled="!permissionToGrant || permPending === permissionToGrant"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
                @click="grantPermission"
              >
                {{ permPending === permissionToGrant ? "Granting…" : "Grant" }}
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PermissionBadge from "@/Components/PermissionBadge.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";

const props = defineProps({
  user: { type: Object, required: true },
  userRoles: { type: Array, required: true },
  allRoles: { type: Array, required: true },
  allPermissions: { type: Array, required: true },
  effectivePermissions: { type: Array, required: true },
});

const { toast, confirmAction } = useNotifications();

const currentRoles = reactive([...props.userRoles]);
const effective = reactive([...props.effectivePermissions]);

const roleToAssign = ref("");
const assignAsPrimary = ref(false);
const roleActionPending = ref(null);

const permissionToGrant = ref("");
const permPending = ref(null);

const assignableRoles = computed(() =>
  props.allRoles.filter((r) => !currentRoles.some((cr) => cr.id === r.id))
);

const grantablePermissions = computed(() =>
  props.allPermissions.filter((p) => !effective.some((e) => e.name === p.name && e.source === "direct"))
);

const assignRole = () => {
  const role = props.allRoles.find((r) => r.name === roleToAssign.value);
  if (!role) return;

  roleActionPending.value = role.name;
  window.axios
    .post(`/api/admin/users/${props.user.id}/roles`, {
      role: role.name,
      is_primary: assignAsPrimary.value,
    })
    .then(() => {
      currentRoles.push({ ...role, is_primary: assignAsPrimary.value });
      toast(`${role.display_name} assigned to ${props.user.first_name}.`, "success");
      roleToAssign.value = "";
      assignAsPrimary.value = false;
    })
    .catch(() => toast("Could not assign that role.", "error"))
    .finally(() => (roleActionPending.value = null));
};

const removeRole = (role) => {
  roleActionPending.value = role.name;
  window.axios
    .delete(`/api/admin/users/${props.user.id}/roles/${role.id}`)
    .then(() => {
      const idx = currentRoles.findIndex((r) => r.id === role.id);
      if (idx !== -1) currentRoles.splice(idx, 1);
      toast(`${role.display_name} removed from ${props.user.first_name}.`, "success");
    })
    .catch(() => toast("Could not remove that role.", "error"))
    .finally(() => (roleActionPending.value = null));
};

const confirmRemoveRole = (role) => {
  confirmAction(
    () => removeRole(role),
    "Remove this role?",
    `${props.user.first_name} will immediately lose every permission granted through "${role.display_name}".`
  );
};

const grantPermission = () => {
  const permission = props.allPermissions.find((p) => p.name === permissionToGrant.value);
  if (!permission) return;

  permPending.value = permission.name;
  window.axios
    .post(`/api/admin/users/${props.user.id}/permissions`, { permissions: [permission.name] })
    .then(() => {
      effective.push({ ...permission, source: "direct" });
      toast(`${permission.display_name} granted directly to ${props.user.first_name}.`, "success");
      permissionToGrant.value = "";
    })
    .catch(() => toast("Could not grant that permission.", "error"))
    .finally(() => (permPending.value = null));
};

const revokePermission = (perm) => {
  const permission = props.allPermissions.find((p) => p.name === perm.name);
  if (!permission) return;

  permPending.value = permission.name;
  window.axios
    .delete(`/api/admin/users/${props.user.id}/permissions/${permission.id}`)
    .then(() => {
      const idx = effective.findIndex((e) => e.name === perm.name && e.source === "direct");
      if (idx !== -1) effective.splice(idx, 1);
      toast(`${permission.display_name} revoked from ${props.user.first_name}.`, "success");
    })
    .catch(() => toast("Could not revoke that permission.", "error"))
    .finally(() => (permPending.value = null));
};

const confirmRevokePermission = (perm) => {
  confirmAction(
    () => revokePermission(perm),
    "Revoke this permission?",
    `${props.user.first_name} will immediately lose "${perm.display_name}".`
  );
};
</script>
