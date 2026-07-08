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
        <div class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium">
          📅 {{ today }}
        </div>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div
            class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4"
          >
            <div
              class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xl"
            >
              {{ props.appointments.length }}
            </div>
            <div>
              <span class="text-gray-400 text-xs block uppercase font-bold tracking-wider"
                >Total Sessions</span
              >
              <span class="text-lg font-bold text-gray-800">Today's Patients</span>
            </div>
          </div>
        </div>

        <div
          class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden"
        >
          <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Today's Appointments Queue</h3>
          </div>

          <div v-if="props.appointments.length === 0" class="p-12 text-center">
            <svg
              class="w-12 h-12 text-gray-300 mx-auto mb-4"
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
            <p class="text-gray-500 text-sm font-medium">
              No appointments scheduled for today yet.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-gray-50/70 border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider"
                >
                  <th class="py-4 px-6">Time</th>
                  <th class="py-4 px-6">Patient Name</th>
                  <th class="py-4 px-6">Reason for Visit</th>
                  <th class="py-4 px-6">Status</th>
                  <th class="py-4 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 text-sm font-medium text-gray-700">
                <tr
                  v-for="appointment in props.appointments"
                  :key="appointment.id"
                  class="hover:bg-gray-50/40 transition-colors"
                >
                  <td class="py-4 px-6 text-indigo-600 font-semibold">
                    ⏱️ {{ appointment.start_time }}
                  </td>
                  <td class="py-4 px-6 font-bold text-gray-900">
                    {{ appointment.patient_name }}
                  </td>
                  <td class="py-4 px-6 text-gray-500 max-w-xs truncate">
                    {{ appointment.reason || "Routine Checkup" }}
                  </td>
                  <td class="py-4 px-6">
                    <span
                      :class="getStatusClass(appointment.status)"
                      class="px-2.5 py-1 text-xs font-semibold rounded-full border shadow-sm uppercase tracking-wider"
                    >
                      {{ appointment.status }}
                    </span>
                  </td>
                  <td class="p-4 text-right">
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

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
  appointments: Array,
  today: String,
});

// ميثود لتلوين الحالات بشكل متناسق
const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case "scheduled":
      return "bg-blue-50 text-blue-700 border-blue-100";
    case "confirmed":
      return "bg-emerald-50 text-emerald-700 border-emerald-100";
    case "completed":
      return "bg-indigo-50 text-indigo-700 border-indigo-100";
    case "cancelled":
      return "bg-red-50 text-red-700 border-red-100";
    default:
      return "bg-gray-50 text-gray-600 border-gray-100";
  }
};
</script>
