<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
  appointments: Array,
  today: String,
});
</script>

<template>
  <AppLayout title="Doctor Dashboard">
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Welcome, Dr. {{ $page.props.auth.user.first_name }}
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Here is your personalized medical schedule and active patient timeline for
            today.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            :href="route('doctor.pricings.index')"
            class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition-all duration-200"
          >
            <svg
              class="w-4 h-4 mr-2 text-gray-400 group-hover:text-indigo-600"
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
            Services & Pricing
          </Link>

          <div
            class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium whitespace-nowrap"
          >
            📅 {{ today }}
          </div>
        </div>
      </div>
    </template>

    <Head title="Doctor Dashboard" />

    <div class="py-12 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- 📊 الـ Stats Grid المطور -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          <!-- بطاقة: جلسات اليوم الحالية -->
          <div
            class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between"
          >
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Total Sessions Today
              </p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">
                {{ appointments.length }}
              </h3>
            </div>
            <div
              class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl font-bold"
            >
              {{ appointments.length }}
            </div>
          </div>

          <!-- 👈 البطاقة الجديدة: رابط الأرشيف الشامل للمواعيد والملفات الطبية -->
          <Link
            :href="route('doctor.appointments.index')"
            class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md hover:border-indigo-100 transition-all duration-200 group cursor-pointer"
          >
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Medical Records Archive
              </p>
              <h3
                class="text-lg font-bold text-gray-800 mt-1 group-hover:text-indigo-600 transition-colors"
              >
                All Appointments & Records →
              </h3>
            </div>
            <div
              class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center transition-colors group-hover:bg-indigo-100"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                />
              </svg>
            </div>
          </Link>
        </div>

        <!-- 📋 جدول مواعيد اليوم القديم كما هو دون أي تغيير هيدروليكي -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Today's Appointments Queue</h2>
          </div>

          <div
            v-if="appointments.length === 0"
            class="p-12 text-center flex flex-col items-center justify-center space-y-3"
          >
            <div
              class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-2xl"
            >
              📅
            </div>
            <p class="text-gray-500 font-medium">
              No appointments scheduled for today yet.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase border-b border-gray-100"
                >
                  <th class="p-4">Time Slot</th>
                  <th class="p-4">Patient Name</th>
                  <th class="p-4">Reason for Visit</th>
                  <th class="p-4">Status</th>
                  <th class="p-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                <tr
                  v-for="appointment in appointments"
                  :key="appointment.id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <td class="p-4 font-semibold text-blue-600">
                    🕒 {{ appointment.start_time }}
                  </td>
                  <td class="p-4 font-medium text-gray-900">
                    {{ appointment.patient_name }}
                  </td>
                  <td class="p-4 text-gray-500">{{ appointment.reason }}</td>
                  <td class="p-4">
                    <span
                      :class="{
                        'px-2.5 py-1 text-xs font-semibold rounded-full': true,
                        'bg-slate-100 text-slate-700': appointment.status === 'pending',
                        'bg-blue-50 text-blue-700':
                          appointment.status === 'scheduled' ||
                          appointment.status === 'confirmed',
                        'bg-green-50 text-green-700': appointment.status === 'completed',
                        'bg-red-50 text-red-700': appointment.status === 'cancelled',
                        'bg-amber-50 text-amber-700': appointment.status === 'no_show',
                      }"
                    >
                      {{ appointment.status }}
                    </span>
                  </td>
                  <td class="p-4 text-right flex items-center justify-end gap-2">
                    <Link
                      :href="route('doctor.patients.history', appointment.patient_id)"
                      class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition-colors inline-flex items-center"
                    >
                      <svg
                        class="w-3.5 h-3.5 mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 012-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                      </svg>
                      History
                    </Link>

                    <span
                      v-if="appointment.status === 'completed'"
                      class="text-xs text-green-600 font-medium bg-green-50 px-3 py-1.5 rounded-lg"
                    >
                      ✓ Checked Done
                    </span>
                    <Link
                      v-else
                      :href="route('doctor.dentalRecords.create', appointment.id)"
                      class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg transition-colors inline-block"
                    >
                      Start Checkup
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
