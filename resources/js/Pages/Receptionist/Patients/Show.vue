<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-5xl mx-auto">
      <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <Link
          :href="route('receptionist.patients.index')"
          class="hover:text-indigo-600 font-medium transition-colors"
          >Patients</Link
        >
        <svg
          class="w-4 h-4 text-gray-400"
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
        <span class="text-gray-900 font-semibold">Medical Profile</span>
      </div>

      <div
        class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6 flex flex-col sm:flex-row items-center justify-between gap-6"
      >
        <div
          class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left"
        >
          <div
            class="w-20 h-20 rounded-full bg-indigo-50 border-4 border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl shadow-sm"
          >
            <div v-if="patient.user.profile_photo_path"></div>
            {{ patient.user.first_name[0] }}{{ patient.user.last_name[0] }}
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-950">
              {{ patient.user.first_name }} {{ patient.user.middle_name }}
              {{ patient.user.last_name }}
            </h1>
            <div
              class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-1.5"
            >
              <span
                class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-xs font-bold uppercase tracking-wider"
                >Patient</span
              >
              <span class="text-gray-300">|</span>
              <span class="text-sm text-gray-500 font-medium"
                >ID: {{ patient.user.identity_number }}</span
              >
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <Link
            :href="route('receptionist.appointments.create', { patient_id: patient.id })"
            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200"
          >
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>

            Book Appointment
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
          <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              Personal Information
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
              <div>
                <span class="text-gray-400 block mb-0.5">Email Address</span>
                <span class="font-semibold text-gray-900">{{ patient.user.email }}</span>
              </div>
              <div>
                <span class="text-gray-400 block mb-0.5">Phone Number</span>
                <span class="font-semibold text-gray-900">{{ patient.user.phone }}</span>
              </div>
              <div>
                <span class="text-gray-400 block mb-0.5">Gender</span>
                <span class="font-semibold text-gray-900">{{ patient.user.gender }}</span>
              </div>
              <div>
                <span class="text-gray-400 block mb-0.5">Age / Date of Birth</span>
                <span class="font-semibold text-gray-900">
                  {{ patientAge }} Years
                  <span class="text-gray-400 font-normal"
                    >({{ patient.user.date_of_birth }})</span
                  >
                </span>
              </div>
              <div class="sm:col-span-2 border-t border-gray-50 pt-3 mt-1">
                <span class="text-gray-400 block mb-0.5">Residential Address</span>
                <span class="font-semibold text-gray-900">{{
                  patient.user.address
                }}</span>
              </div>
            </div>
          </div>

          <div class="mt-8 bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg
                class="w-5 h-5 text-indigo-500"
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
              Patient Appointments History
            </h3>

            <div
              v-if="patient.appointments.length === 0"
              class="text-center py-8 text-gray-400 text-sm"
            >
              No appointments scheduled for this patient yet.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr
                    class="border-b border-gray-100 text-xs text-gray-400 uppercase font-bold tracking-wider"
                  >
                    <th class="py-3 px-4">Doctor</th>
                    <th class="py-3 px-4">Date & Time</th>
                    <th class="py-3 px-4">Reason</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Financial</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                  <tr
                    v-for="app in patient.appointments"
                    :key="app.id"
                    class="hover:bg-gray-50/50 transition-colors"
                  >
                    <!-- Doctor Name -->
                    <td class="py-3.5 px-4 font-semibold text-gray-900">
                      Dr. {{ app.doctor.user.first_name }} {{ app.doctor.user.last_name }}
                    </td>

                    <!-- Date -->
                    <td class="py-3.5 px-4 text-gray-600">
                      <div class="font-medium">{{ app.appointment_date }}</div>
                      <div class="text-xs text-gray-400">{{ app.start_time }}</div>
                    </td>

                    <!-- Reason for Visit -->
                    <td class="py-3.5 px-4 text-gray-500 max-w-xs truncate">
                      {{ app.reason_for_visit || "General Checkup" }}
                    </td>

                    <!-- Status -->
                    <td class="py-3.5 px-4">
                      <span
                        :class="getStatusClass(app.status)"
                        class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                      >
                        {{ app.status }}
                      </span>
                    </td>

                    <!-- Invoice -->
                    <td class="py-3.5 px-4">
                      <span
                        v-if="app.invoices && app.invoices.length > 0"
                        :class="getInvoiceBadgeClass(app.invoices[0].status)"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg"
                      >
                        {{ app.invoices[0].status.toUpperCase() }} ({{
                          app.invoices[0].balance_amount
                        }}
                        Left)
                      </span>
                      <span v-else class="text-gray-400 text-xs italic">No Invoice</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                />
              </svg>
              Medical Information
            </h2>

            <div
              class="mb-4 flex items-center justify-between bg-red-50/50 border border-red-100 p-3 rounded-2xl"
            >
              <span class="text-sm font-semibold text-red-900">Blood Group</span>
              <span
                class="px-3 py-1 bg-red-600 text-white font-extrabold text-xs rounded-xl shadow-sm"
                >{{ patient.blood_group }}</span
              >
            </div>

            <div class="space-y-3.5 text-sm">
              <div>
                <span class="text-gray-400 block mb-1">Allergies</span>
                <p
                  class="p-3 bg-gray-50 rounded-xl font-medium"
                  :class="patient.allergies ? 'text-gray-900' : 'text-gray-400 italic'"
                >
                  {{ patient.allergies || "No known allergies reported." }}
                </p>
              </div>
              <div>
                <span class="text-gray-400 block mb-1">Chronic Diseases</span>
                <p
                  class="p-3 bg-gray-50 rounded-xl font-medium"
                  :class="
                    patient.chronic_diseases ? 'text-gray-900' : 'text-gray-400 italic'
                  "
                >
                  {{ patient.chronic_diseases || "No chronic diseases reported." }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
              Emergency Contact
            </h2>
            <div class="space-y-3 text-sm">
              <div class="bg-amber-50/30 border border-amber-100/70 p-3 rounded-2xl">
                <span class="text-gray-400 block text-xs mb-0.5">Contact Name</span>
                <span class="font-bold text-gray-900">{{
                  patient.emergency_contact_name
                }}</span>
              </div>
              <div class="bg-amber-50/30 border border-amber-100/70 p-3 rounded-2xl">
                <span class="text-gray-400 block text-xs mb-0.5">Contact Phone</span>
                <span class="font-bold text-gray-900">{{
                  patient.emergency_contact_phone
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  patient: Object,
});

const patientAge = computed(() => {
  if (!props.patient.user.date_of_birth) return "";
  const ageDifMs = Date.now() - new Date(props.patient.user.date_of_birth).getTime();
  const ageDate = new Date(ageDifMs);
  return Math.abs(ageDate.getUTCFullYear() - 1970);
});

const getInvoiceBadgeClass = (status) => {
  switch (status) {
    case "paid":
      return "bg-emerald-50 text-emerald-700 border border-emerald-100 px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm";
    case "partially_paid":
      return "bg-amber-50 text-amber-700 border border-amber-100 px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm";
    case "unpaid":
      return "bg-red-50 text-red-700 border border-red-100 px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm";
    default:
      return "bg-gray-50 text-gray-500 border border-gray-200 px-2.5 py-1 text-xs font-semibold rounded-full";
  }
};

const getInvoiceStatusLabel = (status) => {
  switch (status) {
    case "paid":
      return "Paid";
    case "partially_paid":
      return "Partially Paid";
    case "unpaid":
      return "Unpaid";
    default:
      return "No Invoice Issued";
  }
};
const getStatusClass = (status) => {
  switch (status) {
    case "scheduled":
      return "bg-blue-50 text-blue-700 border border-blue-100";
    case "pending":
      return "bg-slate-100 text-slate-700 border border-slate-200";
    case "confirmed":
      return "bg-emerald-50 text-emerald-700 border border-emerald-100";
    case "completed":
      return "bg-indigo-50 text-indigo-700 border border-indigo-100";
    case "cancelled":
      return "bg-red-50 text-red-700 border border-red-100";
    case "no_show":
      return "bg-amber-50 text-amber-700 border border-amber-100";
    default:
      return "bg-gray-50 text-gray-700";
  }
};
</script>
