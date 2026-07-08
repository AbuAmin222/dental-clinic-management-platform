<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

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
      <!-- Header of content side (Content title, date) -->
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Receptionist Control Center
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Real-time clinical operations, calendar timelines, and client billing
            management.
          </p>
        </div>

        <div
          class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-2xl shadow-sm"
        >
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
          <span class="text-xm font-semibold text-gray-700 tracking-wide">{{
            formattedDate
          }}</span>
        </div>
      </div>
    </template>
    <!-- Content page -->
    <div class="py-6 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Cards Content | Basic number -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Active session count | Appointment Dash link -->
          <div
            class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group"
          >
            <div
              class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full transition-transform group-hover:scale-110 duration-300 z-0"
            ></div>
            <div class="flex items-center justify-between relative z-10">
              <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Active Sessions
                </p>
                <h3 class="text-4xl font-bold text-gray-900 mt-2">
                  {{ appointmentCount }}
                </h3>
              </div>
              <div
                class="p-3.5 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300"
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
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 relative z-10">
              <Link
                :href="route('receptionist.appointments.index')"
                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1"
              >
                View Schedule
                <svg
                  class="w-3 h-3"
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

          <!-- Pending Invoices count | Appointment Dash link -->
          <div
            class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group"
          >
            <div
              class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full transition-transform group-hover:scale-110 duration-300 z-0"
            ></div>
            <div class="flex items-center justify-between relative z-10">
              <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Pending Invoices
                </p>
                <h3 class="text-4xl font-bold text-gray-900 mt-2">
                  {{ invoicesCount }}
                </h3>
              </div>
              <div
                class="p-3.5 bg-amber-50 rounded-2xl text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300"
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
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 relative z-10">
              <Link
                :href="route('receptionist.appointments.index')"
                class="text-xs font-semibold text-amber-600 hover:text-amber-700 inline-flex items-center gap-1"
              >
                Process Balances
                <svg
                  class="w-3 h-3"
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

          <!-- Total Patient | Patients Table Link -->
          <div
            class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group"
          >
            <div
              class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full transition-transform group-hover:scale-110 duration-300 z-0"
            ></div>
            <div class="flex items-center justify-between relative z-10">
              <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Total Patients
                </p>
                <h3 class="text-4xl font-bold text-gray-900 mt-2">
                  {{ patientCount }}
                </h3>
              </div>
              <div
                class="p-3.5 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300"
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
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 relative z-10">
              <Link
                :href="route('receptionist.patients.index')"
                class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1"
              >
                Manage Index Database
                <svg
                  class="w-3 h-3"
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
        </div>

        <!-- Cards Dash Links -->
        <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
          <h3 class="text-lg font-bold text-gray-900 mb-4 tracking-tight">
            Clinic Operational Management
          </h3>

          <!-- Appointments Table Link -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Link
              :href="route('receptionist.appointments.index')"
              class="p-5 bg-indigo-50/40 border border-indigo-100 rounded-2xl hover:bg-indigo-50 transition-all duration-200 group block"
            >
              <h4
                class="text-indigo-950 font-bold text-base group-hover:text-indigo-700 transition-colors"
              >
                Appointments & Dentist Schedules
              </h4>
              <p class="text-sm text-gray-500 mt-1">
                Monitor status flows, filter calendar lists, and register live patient
                check-ins.
              </p>
            </Link>

            <!-- Patient Register new link -->
            <Link
              :href="route('receptionist.patients.index')"
              class="p-5 bg-emerald-50/40 border border-emerald-100 rounded-2xl hover:bg-emerald-50 transition-all duration-200 group block"
            >
              <h4
                class="text-emerald-950 font-bold text-base group-hover:text-emerald-700 transition-colors"
              >
                Patient Profiles Registry
              </h4>
              <p class="text-sm text-gray-500 mt-1">
                Access full electronic health histories, demographic metrics, and issue
                billing claims.
              </p>
            </Link>
          </div>
          <div class="mt-10">
            <!-- Patient table info link -->
            <Link
              :href="route('receptionist.patients.create')"
              class="p-5 bg-yellow-50/40 border border-yellow-150 rounded-2xl hover:bg-yellow-100 transition-all duration-300 group block"
            >
              <h4
                class="text-yellow-950 font-bold text-base group-hover:text-yellow-700 transition-colors"
              >
                Patient New Registry
              </h4>
              <p class="text-sm text-gray-500 mt-1">Regestiring new patient.</p>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
