<script setup>
import { Link } from "@inertiajs/vue3";

const menuItems = [
  { name: "Profile & Role Data", route: "profile.edit", icon: "👤" },
  { name: "Manage Password", route: "profile.password", icon: "🔑" },
  { name: "Two-Factor Auth", route: "profile.two-factor", icon: "🛡️" },
  { name: "Active Devices", route: "profile.devices", icon: "💻" },
  {
    name: "Delete Account",
    route: "profile.delete",
    icon: "❌",
    textClass: "text-red-600 hover:text-red-700",
  },
];
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-4 md:gap-6">
      <!-- القائمة الجانبية الموحدة للاختيارات -->
      <div class="md:col-span-1 mb-6 md:mb-0">
        <div class="bg-white rounded-xl border border-slate-100 p-4 space-y-1 shadow-sm">
          <div
            class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2"
          >
            Account Settings
          </div>

          <Link
            v-for="item in menuItems"
            :key="item.route"
            :href="route(item.route)"
            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition duration-150"
            :class="
              route().current(item.route)
                ? 'bg-indigo-50 text-indigo-700 font-semibold'
                : item.textClass ||
                  'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
            "
          >
            <span>{{ item.icon }}</span>
            <span>{{ item.name }}</span>
          </Link>
        </div>
      </div>

      <!-- محتوى الصفحة النشطة الممرر ديناميكياً -->
      <div class="md:col-span-3">
        <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>
