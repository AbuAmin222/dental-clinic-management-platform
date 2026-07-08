<template>
  <AppLayout title="Reception Dashboard">
    <!-- Header of Page -->
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <!-- Title -->
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Appointments Schedule
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage and monitor all patient sessions and dentist timelines.
          </p>
          <p class="text-sm text-gray-500 mt-1">
            Advanced management, live status tracking, and patient check-in workflows.
          </p>
        </div>

        <!-- Book Appointment Button-->
        <Link
          :href="route('receptionist.appointments.create')"
          class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
          Book Appointment
        </Link>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-7xl mx-auto">
        <!-- Filters and Search -->
        <div
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-center"
        >
          <!-- Search Filter -->
          <div class="relative md:col-span-2">
            <span
              class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </span>
            <input
              type="text"
              v-model="search"
              placeholder="Search by patient name or identity number..."
              class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
            />
          </div>
          <div>
            <!-- Search Filter by Appointment Status Value -->
            <div>
              <select
                v-model="statusFilter"
                class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
              >
                <option value="all">All Statuses</option>
                <option value="scheduled">Scheduled</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="no_show">No Show</option>
              </select>
            </div>

            <!-- Search Filter by Invoice Status Value -->
            <div>
              <select
                v-model="invoiceStatusFilter"
                class="w-full px-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all mt-3"
              >
                <option value="all">All Status Finantial</option>
                <option value="paid">Fully Paid</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="unpaid">Unpaid</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div
          class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden"
        >
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-gray-50/70 border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-gray-500"
                >
                  <th class="py-4 px-6">Patient</th>
                  <th class="py-4 px-6">Assigned Doctor</th>
                  <th class="py-4 px-6">Date & Time</th>
                  <th class="py-4 px-6">Reason for Visit</th>
                  <th class="py-4 px-6 text-center">Status</th>
                  <th class="py-4 px-6 text-center">Financial Status</th>
                  <th class="py-4 px-6 text-center">Lifecycle Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 text-sm">
                <tr
                  v-for="appt in appointments.data"
                  :key="appt.id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <!-- Patient Name, ID -->
                  <td class="py-4 px-6">
                    <div class="font-semibold text-gray-900">
                      {{ appt.patient?.user?.first_name }}
                      {{ appt.patient?.user?.last_name }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                      ID: {{ appt.patient?.user?.identity_number }}
                    </div>
                  </td>

                  <!-- Doctor Name -->
                  <td class="py-4 px-6 font-medium text-gray-700">
                    Dr. {{ appt.doctor?.user?.first_name }}
                    {{ appt.doctor?.user?.last_name }}
                  </td>

                  <!-- Appointment Date -->
                  <td class="py-4 px-6">
                    <div class="font-medium text-gray-900">
                      {{ appt.appointment_date }}
                    </div>
                    <div
                      class="text-xs text-indigo-600 font-semibold mt-0.5 tracking-wide"
                    >
                      {{ formatTime(appt.start_time) }} - {{ formatTime(appt.end_time) }}
                    </div>
                  </td>

                  <!-- Reason for Visit -->
                  <td class="py-4 px-6 max-w-xs truncate text-gray-500">
                    {{ appt.reason_for_visit || "General Checkup" }}
                  </td>

                  <!-- Status -->
                  <td class="py-4 px-6 text-center">
                    <span
                      :class="getStatusClass(appt.status)"
                      class="px-2.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase"
                    >
                      {{ appt.status.replace("_", " ") }}
                    </span>
                  </td>

                  <!-- Invoice | Financial -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                    <div v-if="appt.invoices && appt.invoices.length > 0">
                      <span
                        :class="getInvoiceBadgeClass(appt.invoices[0].status)"
                        class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                      >
                        <Link :href="route('receptionist.invoices.create', appt.id)">
                          {{ getInvoiceStatusLabel(appt.invoices[0].status) }}
                        </Link>
                      </span>

                      <div v-if="appt.invoices[0].status === 'partially_paid'">
                        <div class="text-xs text-gray-400 mt-1.5 font-medium">
                          💰 Paid:
                          {{ formatCurrency(appt.invoices[0].paid_amount, "ILS") }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1.5 font-medium">
                          Residual:
                          {{ formatCurrency(appt.invoices[0].balance_amount, "ILS") }}
                        </div>
                      </div>
                    </div>

                    <div v-else>
                      <Link
                        v-if="
                          appt.status === 'completed' ||
                          appt.status === 'confirmed' ||
                          appt.status === 'scheduled'
                        "
                        :href="route('receptionist.invoices.create', appt.id)"
                        class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-xl transition-all duration-200"
                      >
                        <svg
                          class="w-3.5 h-3.5 mr-1 text-indigo-500"
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
                        Generate Invoice
                      </Link>
                      <span
                        v-else
                        class="inline-flex items-center text-xs font-semibold text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-xl transition-all duration-200"
                      >
                        🔒 Settled
                      </span>
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="py-4 px-6 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                      <!-- (scheduled, pending) =>Shown=> (confirmed, cancelled) -->
                      <template v-if="appt.status === 'pending'">
                        <button
                          @click="changeStatus(appt.id, 'confirmed')"
                          class="px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition-colors"
                        >
                          Confirm
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'no_show')"
                          class="px-2.5 py-1 text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg transition-colors"
                        >
                          No Show
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'cancelled')"
                          class="px-2.5 py-1 text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        >
                          Cancel
                        </button>
                      </template>

                      <!-- (confirmed) =>Shown=> (completed, pending, no_show) -->
                      <template v-if="appt.status === 'confirmed'">
                        <button
                          @click="changeStatus(appt.id, 'completed')"
                          class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors"
                        >
                          Complete Session
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'scheduled')"
                          class="px-2.5 py-1 text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg transition-colors"
                        >
                          Schedule
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'no_show')"
                          class="px-2.5 py-1 text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg transition-colors"
                        >
                          No Show
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'cancelled')"
                          class="px-2.5 py-1 text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        >
                          Cancel
                        </button>
                      </template>

                      <template v-if="appt.status === 'scheduled'">
                        <button
                          @click="changeStatus(appt.id, 'completed')"
                          class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors"
                        >
                          Complete Session
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'no_show')"
                          class="px-2.5 py-1 text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg transition-colors"
                        >
                          No Show
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'cancelled')"
                          class="px-2.5 py-1 text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        >
                          Cancel
                        </button>
                      </template>

                      <template v-if="appt.status === 'no_show'">
                        <button
                          @click="changeStatus(appt.id, 'pending')"
                          class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors"
                        >
                          Wait
                        </button>
                        <button
                          @click="changeStatus(appt.id, 'cancelled')"
                          class="px-2.5 py-1 text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        >
                          Cancel
                        </button>
                      </template>

                      <!-- (completed, cancelled) =>Shown=> (Archived Session) -->
                      <span
                        v-if="['completed', 'cancelled'].includes(appt.status)"
                        class="text-xs text-gray-400 italic bg-gray-50 px-2 py-1 rounded-md"
                      >
                        Archived Session
                      </span>
                    </div>
                  </td>
                </tr>

                <tr v-if="appointments.data.length === 0">
                  <td
                    colspan="6"
                    class="text-center py-12 text-gray-400 font-medium bg-gray-50/20"
                  >
                    No appointments match your search criteria.
                    <button
                      @click="resetFilters"
                      class="text-indigo-600 ml-1 underline hover:text-indigo-700"
                    >
                      Clear filters
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            v-if="appointments.links.length > 3"
            class="p-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-center gap-1"
          >
            <Link
              v-for="(link, index) in appointments.links"
              :key="index"
              :href="link.url || '#'"
              v-html="link.label"
              :class="[
                'px-3 py-1 text-xs rounded-lg font-medium transition-all',
                link.active
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'text-gray-600 hover:bg-gray-100',
                !link.url ? 'opacity-40 pointer-events-none' : '',
              ]"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { formatCurrency } from "@/Utils";

const props = defineProps({
  appointments: Object,
  filters: Object,
});

const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "all");
const invoiceStatusFilter = ref(props.filters.invoice_status || "all");

const debounce = (callback, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      callback(...args);
    }, delay);
  };
};

const applyFilters = debounce(() => {
  router.get(
    route("receptionist.appointments.index"),
    {
      search: search.value,
      status: statusFilter.value,
      invoice_status: invoiceStatusFilter.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
}, 300);

watch([search, statusFilter, invoiceStatusFilter], () => {
  applyFilters();
});

const resetFilters = () => {
  search.value = "";
  statusFilter.value = "all";
  invoiceStatusFilter.value = "all";
  applyFilters();
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
      return "bg-green-50 text-green-700 border border-green-100";
    case "cancelled":
      return "bg-red-50 text-red-700 border border-red-100";
    case "no_show":
      return "bg-amber-50 text-amber-700 border border-amber-100";
    default:
      return "bg-gray-50 text-gray-700";
  }
};

const formatTime = (time) => {
  if (!time) return "";
  return time.substring(0, 5);
};

const changeStatus = (id, newStatus) => {
  if (confirm(`Update appointment status to: "${newStatus.replace("_", " ")}"?`)) {
    router.patch(route("receptionist.appointments.updateStatus", id), {
      status: newStatus,
    });
  }
};

const getInvoiceBadgeClass = (status) => {
  switch (status) {
    case "paid":
      return "bg-emerald-50 text-emerald-700 border-emerald-200";
    case "partially_paid":
      return "bg-amber-50 text-amber-700 border-amber-200";
    case "unpaid":
      return "bg-rose-50 text-rose-700 border-rose-200";
    default:
      return "bg-gray-50 text-gray-600 border-gray-200";
  }
};

const getInvoiceStatusLabel = (status) => {
  switch (status) {
    case "paid":
      return "Fully Paid";
    case "partially_paid":
      return "Partially Paid";
    case "unpaid":
      return "Unpaid";
    default:
      return "No Invoice Issued";
  }
};
</script>
