<template>
  <AppLayout title="Admin Dashboard">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">System administration workspace.</p>
      </div>
    </template>

    <AdminSubNav />

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto grid gap-4 sm:grid-cols-2">
        <Link
          v-for="card in cards"
          :key="card.href"
          :href="card.href"
          class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:border-indigo-200 hover:shadow-md transition-all flex items-start gap-4"
        >
          <div class="w-11 h-11 shrink-0 rounded-2xl bg-indigo-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="card.iconPath" />
            </svg>
          </div>
          <div>
            <h2 class="text-sm font-bold text-gray-900">{{ card.title }}</h2>
            <p class="text-xs text-gray-500 mt-1">{{ card.description }}</p>
          </div>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import AdminSubNav from "@/Components/Admin/AdminSubNav.vue";

const cards = [
  {
    title: "Users",
    description: "Manage roles and individual permissions per user.",
    href: route("admin.users.index"),
    iconPath: "M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4",
  },
  {
    title: "Pending Account Reviews",
    description: "Activate or reject newly registered accounts.",
    href: route("admin.users.pendingReviews"),
    iconPath: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    title: "Roles",
    description: "Manage what each of the five system roles can do.",
    href: route("admin.roles.index"),
    iconPath: "M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z",
  },
  {
    title: "Permissions Catalog",
    description: "View and create the system's permission catalog.",
    href: route("admin.permissions.index"),
    iconPath: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    title: "Staff Salaries",
    description: "Set the policy-level base salary for each staff member.",
    href: route("admin.staffSalaries.index"),
    iconPath: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2",
  },
];
</script>
