<script setup>
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
  appointments: Array,
});

// متغيرات للتحكم في نافذة تفاصيل السجل الطبي المنبثقة (Modal)
const isModalOpen = ref(false);
const selectedRecord = ref(null);

const openRecordModal = (record) => {
  selectedRecord.value = record;
  isModalOpen.value = true;
};

const closeModal = () => {
  selectedRecord.value = null;
  isModalOpen.value = false;
};
</script>

<template>
  <AppLayout title="Appointments & Dental Records">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Appointments & Dental Records
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            A historical archive of all your patients sessions and full clinical records.
          </p>
        </div>
        <Link
          :href="route('dashboard')"
          class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition"
        >
          ← Back to Dashboard
        </Link>
      </div>
    </template>

    <Head title="Appointments Archive" />

    <div class="py-12 bg-slate-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
        >
          <div
            v-if="appointments.length === 0"
            class="p-16 text-center flex flex-col items-center justify-center space-y-3"
          >
            <div
              class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-2xl"
            >
              📋
            </div>
            <p class="text-slate-500 font-medium">
              You don't have any recorded appointments in the clinic system yet.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-slate-50 text-xs font-bold text-slate-500 uppercase border-b border-slate-100 tracking-wider"
                >
                  <th class="p-4">Date & Time</th>
                  <th class="p-4">Patient</th>
                  <th class="p-4">Reason</th>
                  <th class="p-4">Status</th>
                  <th class="p-4">Dental Record</th>
                  <th class="p-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <tr
                  v-for="appointment in appointments"
                  :key="appointment.id"
                  class="hover:bg-slate-50/40 transition"
                >
                  <td class="p-4 font-semibold text-indigo-600">
                    📅 {{ appointment.start_time }}
                  </td>
                  <td class="p-4 font-medium text-slate-900">
                    {{ appointment.patient_name }}
                  </td>
                  <td class="p-4 text-slate-500">
                    {{ appointment.reason }}
                  </td>
                  <td class="p-4">
                    <span
                      :class="{
                        'px-2.5 py-1 text-xs font-semibold rounded-full inline-block': true,
                        'bg-slate-100 text-slate-700': appointment.status === 'pending',
                        'bg-blue-50 text-blue-700':
                          appointment.status === 'scheduled' ||
                          appointment.status === 'confirmed',
                        'bg-green-50 text-green-700': appointment.status === 'completed',
                        'bg-red-50 text-red-700': appointment.status === 'cancelled',
                      }"
                    >
                      {{ appointment.status }}
                    </span>
                  </td>
                  <!-- قسم فحص وحالة السجل الطبي -->
                  <td class="p-4">
                    <button
                      v-if="appointment.has_record"
                      @click="openRecordModal(appointment.record_details)"
                      class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium text-xs rounded-lg transition"
                    >
                      📄 View Dental Record
                    </button>
                    <span v-else class="text-xs text-slate-400 italic"
                      >No record added</span
                    >
                  </td>
                  <!-- أزرار الإجراءات السريعة -->
                  <td class="p-4 text-right">
                    <Link
                      :href="route('doctor.patients.history', appointment.patient_id)"
                      class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition"
                    >
                      Patient Full History
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 🌌 النافذة المنبثقة التفاعلية (Dental Record Preview Modal) -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fadeIn"
    >
      <div
        class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100 space-y-4"
      >
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <span>🦷</span> Dental Session Details
          </h3>
          <button
            @click="closeModal"
            class="text-slate-400 hover:text-slate-600 font-bold text-xl"
          >
            &times;
          </button>
        </div>

        <div class="space-y-3">
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
              Diagnosis
            </h4>
            <p
              class="text-slate-700 mt-1 bg-slate-50 p-3 rounded-xl border border-slate-100/60 font-medium"
            >
              {{ selectedRecord?.diagnosis }}
            </p>
          </div>
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
              Treatment Provided
            </h4>
            <p
              class="text-slate-700 mt-1 bg-slate-50 p-3 rounded-xl border border-slate-100/60 font-medium"
            >
              {{ selectedRecord?.treatment }}
            </p>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition"
          >
            Close Record
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
