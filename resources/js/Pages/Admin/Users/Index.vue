<template>
  <AppLayout title="Users">
    <template #header>
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Users</h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage roles and individual permissions per user.
          </p>
        </div>
        <Link
          :href="route('admin.users.pendingReviews')"
          class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-semibold rounded-xl transition-all"
        >
          Pending account reviews →
        </Link>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-5xl mx-auto space-y-4">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <table class="w-full text-left border-collapse hidden sm:table">
            <thead>
              <tr
                class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
              >
                <th class="py-4 px-6">Name</th>
                <th class="py-4 px-6">Email</th>
                <th class="py-4 px-6">Roles</th>
                <th class="py-4 px-6">Status</th>
                <th class="py-4 px-6 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50/50 transition-colors">
                <td class="py-4 px-6 font-semibold text-gray-900">
                  {{ user.first_name }} {{ user.last_name }}
                </td>
                <td class="py-4 px-6 text-gray-500">{{ user.email }}</td>
                <td class="py-4 px-6">
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="role in user.roles"
                      :key="role.id"
                      class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-50 border border-gray-200 text-gray-500"
                    >
                      {{ role.display_name }}
                    </span>
                    <span v-if="user.roles.length === 0" class="text-xs text-gray-300 italic">
                      No role
                    </span>
                  </div>
                </td>
                <td class="py-4 px-6">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold"
                    :class="
                      user.is_active
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200'
                    "
                  >
                    {{ user.is_active ? "Active" : "Pending" }}
                  </span>
                </td>
                <td class="py-4 px-6 text-right">
                  <Link
                    :href="route('admin.users.rolesPermissions', user.id)"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all"
                  >
                    Manage
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="sm:hidden divide-y divide-gray-50">
            <div v-for="user in users.data" :key="user.id" class="p-5 space-y-2">
              <div class="flex items-center justify-between">
                <p class="font-semibold text-gray-900">
                  {{ user.first_name }} {{ user.last_name }}
                </p>
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                  :class="
                    user.is_active
                      ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                      : 'bg-amber-50 text-amber-700 border border-amber-200'
                  "
                >
                  {{ user.is_active ? "Active" : "Pending" }}
                </span>
              </div>
              <p class="text-xs text-gray-500">{{ user.email }}</p>
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="role in user.roles"
                  :key="role.id"
                  class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-50 border border-gray-200 text-gray-500"
                >
                  {{ role.display_name }}
                </span>
              </div>
              <Link
                :href="route('admin.users.rolesPermissions', user.id)"
                class="inline-block mt-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all"
              >
                Manage
              </Link>
            </div>
          </div>

          <div v-if="users.data.length === 0" class="p-12 text-center text-gray-400 font-medium">
            No users found.
          </div>
        </div>

        <Pagination :links="users.links" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";

defineProps({
  users: { type: Object, required: true },
});
</script>
