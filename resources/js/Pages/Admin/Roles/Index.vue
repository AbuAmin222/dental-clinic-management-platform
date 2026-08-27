<template>
  <AppLayout title="Roles">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">System Roles</h1>
        <p class="text-sm text-gray-500 mt-1">
          The five roles are fixed by the system design (admin, doctor, patient,
          receptionist, financial) — this screen manages what each one can <em>do</em>,
          not the list of roles itself.
        </p>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto space-y-4">
        <div
          v-for="role in roles"
          :key="role.id"
          class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 flex items-center justify-between gap-4"
        >
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base font-bold text-gray-900">{{ role.display_name }}</h2>
              <span
                v-if="role.is_system"
                class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5"
              >
                System
              </span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">{{ role.name }}</p>
            <p v-if="role.description" class="text-xs text-gray-400 mt-1 max-w-md">
              {{ role.description }}
            </p>
          </div>

          <div class="flex items-center gap-4 shrink-0">
            <div class="text-right">
              <p class="text-lg font-bold text-gray-900">{{ role.users_count }}</p>
              <p class="text-[11px] text-gray-400 uppercase tracking-wide">Users</p>
            </div>
            <Link
              :href="route('admin.roles.permissions', role.id)"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all"
            >
              Manage permissions
            </Link>
          </div>
        </div>

        <div
          v-if="roles.length === 0"
          class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center text-gray-400 font-medium"
        >
          No roles found. Run the RoleSeeder.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
  roles: { type: Array, required: true },
});
</script>
