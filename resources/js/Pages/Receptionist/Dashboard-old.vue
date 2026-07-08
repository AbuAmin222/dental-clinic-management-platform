<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

// استقبال الإحصائيات الممررة من الكنترولر مع التحقق من الأنواع والقيم الافتراضية لحماية الواجهة
defineProps({
  appointmentCount: {
    type: Number,
    required: true,
    default: 0,
  },
  invoicesCount: {
    type: Number,
    required: true,
    default: 0,
  },
  patientCount: {
    type: Number,
    required: true,
    default: 0,
  },
});
// ميزة احترافية: توليد تاريخ اليوم بشكل حيوي باللغة الإنجليزية لتعزيز الـ UX للموظف
const formattedDate = computed(() => {
  return new Date().toLocaleDateString("en-US", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
});
</script>

<template>
  <AppLayout title="Reception Dashboard">
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Receptionist Control Panel
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Monitor clinic metrics, manage live patient streams, and handle billing
            workflows.
          </p>
        </div>
        <div
          class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-2xl shadow-sm"
        >
          Date:
          <svg
            class="w-4 h-4 text-indigo-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
          <span class="text-xs font-semibold text-gray-700 tracking-wide">{{
            formattedDate
          }}</span>
        </div>
      </div>
    </template>

    <div class="py-8 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div>
          <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
            Today's Overview
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group"
            >
              <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full transition-transform group-hover:scale-110 duration-300 z-0"
              ></div>

              <div class="flex items-center justify-between relative z-10">
                <div>
                  <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Active Appointments
                  </p>
                  <h3 class="text-4xl font-extrabold text-slate-900 mt-2 tracking-tight">
                    {{ appointmentCount }}
                  </h3>
                </div>
                <div
                  class="p-3.5 bg-indigo-50 text-indigo-600 rounded-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300"
                >
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
              </div>

              <div
                class="mt-5 pt-4 border-t border-gray-50 flex items-center justify-between relative z-10"
              >
                <span
                  class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100"
                >
                  Live Stream Active
                </span>
                <Link
                  :href="route('receptionist.appointments.index')"
                  class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1 transition-colors"
                >
                  Manage Live List
                  <svg
                    class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                </Link>
              </div>
            </div>

            <div
              class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group"
            >
              <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full transition-transform group-hover:scale-110 duration-300 z-0"
              ></div>

              <div class="flex items-center justify-between relative z-10">
                <div>
                  <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Total Active Invoiced
                  </p>
                  <h3 class="text-4xl font-extrabold text-slate-900 mt-2 tracking-tight">
                    {{ invoicesCount }}
                  </h3>
                </div>
                <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-2xl">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-20c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"
                    />
                  </svg>
                </div>
              </div>

              <div
                class="mt-5 pt-4 border-t border-gray-50 flex items-center justify-between relative z-10"
              >
                <span class="text-xs text-gray-400">Financial indicator</span>
                <span class="text-xs font-medium text-gray-400"
                  >Awaiting controller sync</span
                >
              </div>
            </div>

            <div
              class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group"
            >
              <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full transition-transform group-hover:scale-110 duration-300 z-0"
              ></div>

              <div class="flex items-center justify-between relative z-10">
                <div>
                  <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Total Patients Added
                  </p>
                  <h3 class="text-4xl font-extrabold text-slate-900 mt-2 tracking-tight">
                    {{ patientCount }}
                  </h3>
                </div>
                <div class="p-3.5 bg-amber-50 text-amber-600 rounded-2xl">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                    />
                  </svg>
                </div>
              </div>

              <div
                class="mt-5 pt-4 border-t border-gray-50 flex items-center justify-between relative z-10"
              >
                <span class="text-xs text-gray-400">Demographic growth</span>
                <span class="text-xs font-medium text-gray-400"
                  >Awaiting controller sync</span
                >
              </div>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
            Quick Operations
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Link
              :href="route('receptionist.patients.create')"
              class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex items-start gap-4 group hover:border-indigo-100"
            >
              <div
                class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300"
              >
                <svg
                  class="w-6 h-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h3
                  class="text-base font-bold text-gray-900 group-hover:text-indigo-600 transition-colors"
                >
                  Register New Patient
                </h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                  Onboard a new clinic patient, upload physical IDs, and setup baseline
                  medical file profiles.
                </p>
              </div>
            </Link>

            <Link
              :href="route('receptionist.appointments.index')"
              class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex items-start gap-4 group hover:border-emerald-100"
            >
              <div
                class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300"
              >
                <svg
                  class="w-6 h-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h3
                  class="text-base font-bold text-gray-900 group-hover:text-emerald-700 transition-colors"
                >
                  Appointments Schedule
                </h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                  Monitor dentist session limits, execute real-time state changes, and
                  organize check-in pipelines.
                </p>
              </div>
            </Link>

            <Link
              :href="route('receptionist.patients.index')"
              class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex items-start gap-4 group hover:border-amber-100"
            >
              <div
                class="p-3 bg-amber-50 text-amber-600 rounded-2xl group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300"
              >
                <svg
                  class="w-6 h-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h3
                  class="text-base font-bold text-gray-900 group-hover:text-amber-700 transition-colors"
                >
                  Patients Directory
                </h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                  Lookup comprehensive historical files, check identity parameters, or
                  query phone tracking lines instantly.
                </p>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
