<template>
  <AppLayout title="Permissions Catalog">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Permissions Catalog
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Creating a permission here only adds it to the catalog — it grants it to
            nobody. Grant it from
            <Link :href="route('admin.roles.index')" class="text-indigo-600 hover:underline"
              >Roles</Link
            >
            or a specific user's page.
          </p>
        </div>
        <button
          type="button"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all shrink-0"
          @click="showCreateModal = true"
        >
          + New permission
        </button>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto space-y-4">
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
            :class="
              activeGroup === null
                ? 'bg-gray-900 text-white border-gray-900'
                : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'
            "
            @click="activeGroup = null"
          >
            All
          </button>
          <button
            v-for="group in groups"
            :key="group"
            type="button"
            class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
            :class="
              activeGroup === group
                ? 'bg-gray-900 text-white border-gray-900'
                : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'
            "
            @click="activeGroup = group"
          >
            {{ group }}
          </button>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <table class="w-full text-left border-collapse hidden sm:table">
            <thead>
              <tr
                class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
              >
                <th class="py-4 px-6">Display name</th>
                <th class="py-4 px-6">Slug</th>
                <th class="py-4 px-6">Group</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
              <tr v-for="permission in filtered" :key="permission.id">
                <td class="py-4 px-6 font-semibold text-gray-900">
                  {{ permission.display_name }}
                </td>
                <td class="py-4 px-6 text-gray-500 font-mono text-xs">
                  {{ permission.name }}
                </td>
                <td class="py-4 px-6">
                  <span
                    class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-50 border border-gray-200 text-gray-500"
                  >
                    {{ permission.group || "Ungrouped" }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="sm:hidden divide-y divide-gray-50">
            <div v-for="permission in filtered" :key="permission.id" class="p-5">
              <p class="font-semibold text-gray-900 text-sm">
                {{ permission.display_name }}
              </p>
              <p class="text-xs text-gray-400 font-mono mt-0.5">{{ permission.name }}</p>
              <span
                class="inline-block mt-2 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-50 border border-gray-200 text-gray-500"
              >
                {{ permission.group || "Ungrouped" }}
              </span>
            </div>
          </div>

          <div v-if="filtered.length === 0" class="p-12 text-center text-gray-400 font-medium">
            No permissions in this group yet.
          </div>
        </div>
      </div>
    </div>

    <CreatePermissionModal
      :show="showCreateModal"
      :groups="groups"
      @close="showCreateModal = false"
      @created="onCreated"
    />
  </AppLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import CreatePermissionModal from "@/Components/CreatePermissionModal.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";

const props = defineProps({
  permissions: { type: Array, required: true },
  groups: { type: Array, required: true },
});

const { toast } = useNotifications();

const list = ref([...props.permissions]);
const groups = ref([...props.groups]);
const activeGroup = ref(null);
const showCreateModal = ref(false);

const filtered = computed(() =>
  activeGroup.value === null
    ? list.value
    : list.value.filter((p) => (p.group || "Ungrouped") === activeGroup.value)
);

const onCreated = (permission) => {
  list.value.push(permission);
  if (permission.group && !groups.value.includes(permission.group)) {
    groups.value.push(permission.group);
  }
  showCreateModal.value = false;
  toast(`"${permission.display_name}" added to the catalog.`, "success");
};
</script>
