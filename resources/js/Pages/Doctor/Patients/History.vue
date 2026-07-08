<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
  patient: Object,
  history: Array,
});
</script>

<template>
  <AppLayout title="Patient Medical History">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link
            :href="route('doctor.dashboard')"
            class="p-2 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
              Medical File Archive
            </h1>
            <p class="text-sm text-gray-500">
              Comprehensive timeline for {{ patient.name }}
            </p>
          </div>
        </div>
      </div>
    </template>

    <div class="py-10 bg-gray-50 min-h-screen">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3"
          >
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Patient Profile
            </h3>
            <div>
              <p class="text-lg font-bold text-gray-800">{{ patient.name }}</p>
              <p class="text-sm text-gray-500">
                Age: {{ patient.age }} Years | {{ patient.gender }}
              </p>
            </div>
            <div class="text-xs text-gray-600 pt-2 border-t border-gray-50 space-y-1">
              <p><strong>ID:</strong> {{ patient.identity_number }}</p>
              <p><strong>Phone:</strong> {{ patient.phone }}</p>
            </div>
          </div>

          <div
            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3"
          >
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Medical Status
            </h3>
            <div class="flex items-center gap-2">
              <span
                class="px-2.5 py-1 bg-red-50 text-red-600 font-bold rounded-lg text-xs"
              >
                Blood Group: {{ patient.blood_group }}
              </span>
            </div>
            <div class="text-sm text-gray-700">
              <p class="font-medium text-gray-500 text-xs">Chronic Diseases:</p>
              <p class="mt-0.5">{{ patient.chronic_diseases || "None reported." }}</p>
            </div>
          </div>

          <div
            :class="[
              'p-5 rounded-2xl border shadow-sm space-y-3',
              patient.allergies
                ? 'bg-amber-50 border-amber-100 text-amber-900'
                : 'bg-white border-gray-100',
            ]"
          >
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
              Allergies & Alerts
            </h3>
            <div class="flex items-center gap-2">
              <span
                :class="[
                  'px-2.5 py-1 text-xs font-bold rounded-lg',
                  patient.allergies
                    ? 'bg-amber-600 text-white'
                    : 'bg-gray-100 text-gray-600',
                ]"
              >
                {{ patient.allergies ? "⚠️ ALERT" : "Clear" }}
              </span>
            </div>
            <div class="text-sm">
              <p class="text-xs font-medium opacity-70">Known Allergies:</p>
              <p class="mt-0.5 font-semibold">
                {{ patient.allergies || "No known allergies." }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
          <h2 class="text-xl font-bold text-gray-900 mb-8">Clinical Timeline</h2>

          <div v-if="history.length === 0" class="text-center py-12 text-gray-400">
            <svg
              class="w-12 h-12 mx-auto mb-3 opacity-60"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
              />
            </svg>
            <p class="font-medium text-gray-500">
              No previous dental treatment records registered for this patient.
            </p>
          </div>

          <div v-else class="relative border-l-2 border-gray-100 ml-3 space-y-8">
            <div v-for="record in history" :key="record.id" class="relative pl-6 group">
              <span
                class="absolute -left-[9px] top-1.5 w-4 h-4 bg-blue-600 rounded-full border-4 border-white shadow group-hover:scale-125 transition-transform"
              ></span>

              <div
                class="bg-gray-50 rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow"
              >
                <div
                  class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-200/60"
                >
                  <div class="flex items-center gap-3">
                    <span
                      class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg uppercase tracking-tight"
                    >
                      Tooth #{{ record.tooth_number }}
                    </span>
                    <h3 class="text-base font-bold text-gray-800">
                      {{ record.condition_type }}
                    </h3>
                  </div>
                  <div class="text-xs text-gray-400 font-medium">
                    <span>{{ record.date }}</span> |
                    <span class="text-gray-600 font-semibold">{{
                      record.doctor_name
                    }}</span>
                  </div>
                </div>

                <div class="mt-3 text-sm text-gray-600 leading-relaxed">
                  <p class="font-medium text-xs text-gray-400 mb-1">
                    Clinical Notes & Action:
                  </p>
                  {{ record.description }}
                </div>

                <div v-if="record.has_xray" class="mt-4 pt-4 border-t border-gray-200/60">
                  <p class="font-medium text-xs text-gray-400 mb-2">
                    Attached Radiological Data (X-Ray):
                  </p>
                  <div
                    class="relative inline-block max-w-[240px] rounded-xl overflow-hidden border border-gray-200 bg-black group-hover:border-blue-400 transition-colors"
                  >
                    <img
                      :src="route('dental-records.xray', record.id)"
                      alt="Patient Dental X-Ray"
                      class="opacity-90 hover:opacity-100 transition-opacity cursor-pointer object-cover max-h-40"
                    />
                    <a
                      :href="route('dental-records.xray', record.id)"
                      target="_blank"
                      class="absolute bottom-2 right-2 px-2 py-1 bg-black/70 text-white rounded text-[10px] font-semibold tracking-wide hover:bg-black transition"
                    >
                      Open Full Size ↗
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
