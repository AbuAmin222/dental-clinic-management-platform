<template>
  <AppLayout title="Admin Dashboard">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">System administration workspace.</p>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto">
        <div
          class="bg-white rounded-3xl border border-gray-100 shadow-sm p-10 text-center"
        >
          <div
            class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-5"
          >
            <svg
              class="w-7 h-7 text-indigo-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">
            Welcome to the Admin workspace
          </h2>
          <p class="text-sm text-gray-500 max-w-md mx-auto mb-8">
            This dashboard is intentionally minimal for now. The first available Admin
            capability is staff base-salary management — broader administrative
            functionality (account activation review, role promotion) is planned but out
            of scope for this build.
          </p>
          <Link
            :href="route('admin.staffSalaries.index')"
            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all"
          >
            Manage Staff Salaries
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
</script>
