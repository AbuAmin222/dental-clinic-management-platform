<template>
  <nav
    class="bg-white border-b border-gray-100 sticky top-16 z-30"
    aria-label="Admin section navigation"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-2">
        <Link
          v-for="item in items"
          :key="item.routeName"
          :href="route(item.routeName)"
          class="shrink-0 flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors"
          :class="
            isActive(item)
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
          "
        >
          <svg
            class="w-4 h-4 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.iconPath" />
          </svg>
          {{ item.label }}
        </Link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

// Single source of truth for the Admin section's tab bar, so every Admin page renders the
// exact same set of links in the exact same order — adding a new Admin page means adding
// one entry here, not copy-pasting a nav block into every .vue file. `activePattern`
// lets a group of related route names (e.g. every users.* route) all highlight the same
// tab, since route().current() supports wildcards.
const items = [
  {
    label: "Dashboard",
    routeName: "admin.dashboard",
    iconPath: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
  },
  {
    label: "Users",
    routeName: "admin.users.index",
    // Also highlights "Users" while drilled into a specific user's roles/permissions
    // page, but deliberately NOT for admin.users.pendingReviews — that gets its own tab.
    activePatterns: ["admin.users.index", "admin.users.rolesPermissions"],
    iconPath: "M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4",
  },
  {
    label: "Pending Reviews",
    routeName: "admin.users.pendingReviews",
    iconPath: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Roles",
    routeName: "admin.roles.index",
    activePatterns: ["admin.roles.index", "admin.roles.permissions"],
    iconPath: "M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z",
  },
  {
    label: "Permissions",
    routeName: "admin.permissions.index",
    iconPath: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Staff Salaries",
    routeName: "admin.staffSalaries.index",
    iconPath: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2",
  },
];

// Ziggy's route().current() takes one pattern; items with multiple relevant route names
// (e.g. Users' index + detail page) are checked with .some() instead.
const isActive = (item) => {
  const patterns = item.activePatterns ?? [item.routeName];
  return patterns.some((pattern) => route().current(pattern));
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
