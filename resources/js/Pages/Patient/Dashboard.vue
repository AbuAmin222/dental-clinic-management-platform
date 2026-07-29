<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { onMounted } from "vue";

import { formatCurrency } from "@/Utils";
import { useNotifications } from "@/Composables";

defineProps({
  patient: {
    type: Object,
    default: () => ({ appointments: [], dental_records: [] }),
  },
  stats: {
    type: Object,
    default: () => ({
      total_appointments: 0,
      pending_appointments: 0,
      total_treatments: 0,
      remaining_balance: 0,
    }),
  },
  invoices: {
    type: Array,
    default: () => [],
  },
});

const { toast } = useNotifications();

onMounted(() => {
  toast("Welcome back to your secure health portal!", "success");
});

const getStatusBadge = (status) => {
  const styles = {
    pending: "bg-amber-50 text-amber-700 border-amber-200",
    confirmed: "bg-emerald-50 text-emerald-700 border-emerald-200",
    completed: "bg-blue-50 text-blue-700 border-blue-200",
    cancelled: "bg-rose-50 text-rose-700 border-rose-200",
  };
  return styles[status] || "bg-gray-50 text-gray-700 border-gray-200";
};

const getInvoiceStatusBadge = (status) => {
  const styles = {
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    partially_paid: "bg-amber-50 text-amber-700 border-amber-200",
    unpaid: "bg-rose-50 text-rose-700 border-rose-200",
  };
  return styles[status] || "bg-gray-50 text-gray-700 border-gray-200";
};
</script>

<template>
  <AppLayout title="My Medical Portal">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Medical Records - My Medical File
      </h2>
    </template>

    <div class="py-12 bg-gray-50" dir="ltr">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div
            class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between"
          >
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Total Appointments
              </p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">
                {{ stats.total_appointments }}
              </h3>
            </div>
            <span class="text-2xl bg-purple-50 p-3 rounded-lg text-purple-600">📅</span>
          </div>

          <div
            class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between"
          >
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Pending Visits
              </p>
              <h3 class="text-2xl font-bold text-amber-600 mt-1">
                {{ stats.pending_appointments }}
              </h3>
            </div>
            <span class="text-2xl bg-amber-50 p-3 rounded-lg text-amber-600">⏳</span>
          </div>

          <div
            class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between"
          >
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Treatments Done
              </p>
              <h3 class="text-2xl font-bold text-blue-600 mt-1">
                {{ stats.total_treatments }}
              </h3>
            </div>
            <span class="text-2xl bg-blue-50 p-3 rounded-lg text-blue-600">🦷</span>
          </div>

          <div
            class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between"
          >
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Remaining Balance
              </p>
              <h3 class="text-2xl font-bold text-rose-600 mt-1">
                {{ formatCurrency(stats.remaining_balance, "ILS") }}
              </h3>
            </div>
            <span class="text-2xl bg-rose-50 p-3 rounded-lg text-rose-600">💳</span>
          </div>
        </div>

        <div
          class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border border-gray-100"
        >
          <h1 class="font-bold text-lg text-gray-900 mb-6">
            Welcome to your medical portal. Here you can manage your healthcare history
            seamlessly.
          </h1>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Link
              :href="route('patient.appointment.create')"
              class="p-6 bg-purple-50 border-l-4 border-purple-500 rounded-xl hover:bg-purple-100/70 transition block group"
            >
              <h3 class="text-purple-700 font-bold group-hover:underline">
                Create Appointment ➜
              </h3>
              <p class="text-sm text-gray-600 mt-1">
                Book a new operational or check-up session with our expert dentists.
              </p>
            </Link>

            <a
              href="#medical-history-section"
              class="p-6 bg-yellow-50 border-l-4 border-yellow-500 rounded-xl hover:bg-yellow-100/70 transition block group"
            >
              <h3 class="text-yellow-700 font-bold group-hover:underline">
                Jump to Medical History ↓
              </h3>
              <p class="text-sm text-gray-600 mt-1">
                Review your verified teeth procedures, clinical operations, and notes.
              </p>
            </a>
          </div>
        </div>

        <div
          class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden"
        >
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">My Appointments Schedule</h3>
            <p class="text-xs text-gray-500 mt-0.5">
              Your past logs and upcoming physical clinic sessions.
            </p>
          </div>

          <div
            v-if="!patient?.appointments || patient.appointments.length === 0"
            class="p-8 text-center text-gray-400 text-sm"
          >
            No dynamic appointments found on your record.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase border-b border-gray-100"
                >
                  <th class="py-4 px-6">Doctor</th>
                  <th class="py-4 px-6">Date</th>
                  <th class="py-4 px-6">Time Window</th>
                  <th class="py-4 px-6">Reason</th>
                  <th class="py-4 px-6">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                <tr
                  v-for="app in patient.appointments"
                  :key="app.id"
                  class="hover:bg-gray-50/50 transition"
                >
                  <td class="py-4 px-6 font-medium text-gray-900">
                    Dr. {{ app.doctor?.user?.first_name }}
                    {{ app.doctor?.user?.last_name }}
                  </td>
                  <td class="py-4 px-6 text-gray-600">{{ app.appointment_date }}</td>
                  <td class="py-4 px-6 font-mono text-xs text-gray-500">
                    {{ app.start_time }} - {{ app.end_time }}
                  </td>
                  <td class="py-4 px-6 text-gray-600 max-w-xs truncate">
                    {{ app.reason_for_visit }}
                  </td>
                  <td class="py-4 px-6">
                    <span
                      class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                      :class="getStatusBadge(app.status)"
                    >
                      {{ app.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div
          id="medical-history-section"
          class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden"
        >
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Verified Medical History</h3>
            <p class="text-xs text-gray-500 mt-0.5">
              Clinical logs pushed directly from your treating practitioner's checkups.
            </p>
          </div>

          <div
            v-if="!patient?.dental_records || patient.dental_records.length === 0"
            class="p-8 text-center text-gray-400 text-sm"
          >
            No dental surgery or treatments recorded in your profile yet.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase border-b border-gray-100"
                >
                  <th class="py-4 px-6">Tooth Target</th>
                  <th class="py-4 px-6">Condition / Diagnostic</th>
                  <th class="py-4 px-6">Operational Description</th>
                  <th class="py-4 px-6">Attending Doctor</th>
                  <th class="py-4 px-6">X-Ray Attachment</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                <tr
                  v-for="record in patient.dental_records"
                  :key="record.id"
                  class="hover:bg-gray-50/50 transition"
                >
                  <td class="py-4 px-6 font-bold text-indigo-600">
                    Tooth #{{ record.tooth_number }}
                  </td>
                  <td class="py-4 px-6">
                    <span
                      class="px-2 py-0.5 bg-gray-100 border rounded text-xs font-medium text-gray-800"
                    >
                      {{ record.condition_type }}
                    </span>
                  </td>
                  <td class="py-4 px-6 text-gray-600 max-w-sm">
                    {{ record.description }}
                  </td>
                  <td class="py-4 px-6 font-medium text-gray-800">
                    Dr. {{ record.doctor?.user?.first_name }}
                    {{ record.doctor?.user?.last_name }}
                  </td>
                  <td class="py-4 px-6">
                    <a
                      v-if="record.xray_image_path"
                      :href="route('dental-records.xray', record.id)"
                      target="_blank"
                      class="text-xs font-bold text-purple-600 hover:underline inline-flex items-center gap-1"
                    >
                      View Scan 🩻
                    </a>
                    <span v-else class="text-gray-400 text-xs italic">No Scans</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div
          class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden"
        >
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Billing Ledger & Statements</h3>
            <p class="text-xs text-gray-500 mt-0.5">
              Track your clinical ledger invoices and process micro-payments securely.
            </p>
          </div>

          <div v-if="invoices.length === 0" class="p-8 text-center text-gray-400 text-sm">
            No billing statements found in your account ledger.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase tracking-wider border-b border-gray-100"
                >
                  <th class="py-4 px-6">Invoice ID</th>
                  <th class="py-4 px-6">Practitioner</th>
                  <th class="py-4 px-6">Total Amount</th>
                  <th class="py-4 px-6">Outstanding Balance</th>
                  <th class="py-4 px-6">Status</th>
                  <th class="py-4 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                <tr
                  v-for="invoice in invoices"
                  :key="invoice.id"
                  class="hover:bg-gray-50/50 transition"
                >
                  <td class="py-4 px-6 font-bold text-gray-900">#INV-{{ invoice.id }}</td>
                  <td class="py-4 px-6">
                    <span class="font-medium text-gray-800" v-if="invoice.doctor?.user">
                      Dr. {{ invoice.doctor.user.first_name }}
                      {{ invoice.doctor.user.last_name }}
                    </span>
                    <span v-else class="text-gray-400">N/A</span>
                  </td>
                  <td class="py-4 px-6 font-semibold">
                    {{ formatCurrency(invoice.total_amount, "ILS") }}
                  </td>
                  <td
                    class="py-4 px-6 font-bold"
                    :class="
                      invoice.balance_amount > 0 ? 'text-indigo-600' : 'text-gray-400'
                    "
                  >
                    {{ formatCurrency(invoice.balance_amount, "ILS") }}
                  </td>
                  <td class="py-4 px-6">
                    <span
                      class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                      :class="getInvoiceStatusBadge(invoice.status)"
                    >
                      {{ invoice.status.replace("_", " ") }}
                    </span>
                  </td>
                  <td class="py-4 px-6 text-right">
                    <Link
                      v-if="invoice.balance_amount > 0"
                      :href="route('patient.invoices.checkout', invoice.id)"
                      class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-sm transition"
                    >
                      Pay Now 💳
                    </Link>
                    <span
                      v-else
                      class="text-emerald-600 font-bold text-xs inline-flex items-center gap-1"
                    >
                      ✓ Settled
                    </span>
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
