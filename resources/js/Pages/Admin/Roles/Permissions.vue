<template>
  <AppLayout :title="`${role.display_name} — Permissions`">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <Link
            :href="route('admin.roles.index')"
            class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition-colors"
          >
            &larr; All roles
          </Link>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight mt-1">
            {{ role.display_name }}
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Every permission granted here applies to <strong>every user</strong> who holds
            the "{{ role.name }}" role. To grant a permission to one specific person
            without giving it to the whole role, use
            <Link
              :href="route('admin.users.index')"
              class="text-indigo-600 hover:underline"
              >Users &rarr; individual permissions</Link
            >
            instead.
          </p>
        </div>
      </div>
    </template>

    <AdminSubNav />

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto space-y-8">
        <div v-for="(perms, group) in groupedAll" :key="group" class="space-y-3">
          <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider ps-1">
            {{ group }}
          </h2>
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm divide-y divide-gray-50">
            <div
              v-for="permission in perms"
              :key="permission.id"
              class="flex items-center justify-between gap-4 p-5"
            >
              <div>
                <p class="text-sm font-semibold text-gray-900">
                  {{ permission.display_name }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ permission.name }}</p>
              </div>

              <button
                type="button"
                :disabled="pending === permission.name"
                class="px-4 py-2 text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50 shrink-0"
                :class="
                  isGranted(permission.name)
                    ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200'
                    : 'bg-indigo-600 text-white hover:bg-indigo-700'
                "
                @click="toggle(permission)"
              >
                <span v-if="pending === permission.name">Working…</span>
                <span v-else-if="isGranted(permission.name)">Revoke</span>
                <span v-else>Grant</span>
              </button>
            </div>
          </div>
        </div>

        <div
          v-if="allPermissions.length === 0"
          class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center text-gray-400 font-medium"
        >
          No permissions exist in the catalog yet.
          <Link :href="route('admin.permissions.index')" class="text-indigo-600 hover:underline">
            Create one first.
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import AdminSubNav from "@/Components/Admin/AdminSubNav.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";

const props = defineProps({
  role: { type: Object, required: true },
  allPermissions: { type: Array, required: true },
  grantedPermissionNames: { type: Array, required: true },
});

const { toast, confirmAction } = useNotifications();

// Local reactive copy so the UI updates instantly on grant/revoke without a full page
// reload — the server call is still the source of truth; this list is only ever mutated
// after a successful API response.
const granted = reactive(new Set(props.grantedPermissionNames));
const pending = ref(null);

const isGranted = (name) => granted.has(name);

const groupedAll = computed(() => {
  const groups = {};
  for (const p of props.allPermissions) {
    const key = p.group || "Ungrouped";
    if (!groups[key]) groups[key] = [];
    groups[key].push(p);
  }
  return groups;
});

const grant = (permission) => {
  pending.value = permission.name;
  window.axios
    .post(`/api/admin/roles/${props.role.id}/permissions`, { permissions: [permission.name] })
    .then(() => {
      granted.add(permission.name);
      toast(`${permission.display_name} granted to ${props.role.display_name}.`, "success");
    })
    .catch(() => toast("Could not grant that permission.", "error"))
    .finally(() => (pending.value = null));
};

const revoke = (permission) => {
  pending.value = permission.name;
  window.axios
    .delete(`/api/admin/roles/${props.role.id}/permissions/${permission.id}`)
    .then(() => {
      granted.delete(permission.name);
      toast(`${permission.display_name} revoked from ${props.role.display_name}.`, "success");
    })
    .catch(() => toast("Could not revoke that permission.", "error"))
    .finally(() => (pending.value = null));
};

const toggle = (permission) => {
  if (isGranted(permission.name)) {
    confirmAction(
      () => revoke(permission),
      "Revoke this permission?",
      `Every user with the "${props.role.display_name}" role will immediately lose "${permission.display_name}".`
    );
  } else {
    grant(permission);
  }
};
</script>
