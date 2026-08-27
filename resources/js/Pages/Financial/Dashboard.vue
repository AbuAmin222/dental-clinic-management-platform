<template>
  <AppLayout title="Financial Dashboard">
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Financial Overview
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Welcome back{{ officerName ? `, ${officerName}` : "" }}. Manage invoices,
            payment methods, and payroll from here.
          </p>
        </div>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto space-y-6">
        <!-- Officer Profile Summary -->
        <div
          class="bg-indigo-900 text-white rounded-3xl p-6 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-4"
        >
          <div>
            <span
              class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
            >
              Employee Number
            </span>
            <span class="text-lg font-semibold">{{
              financial?.employee_number ?? "—"
            }}</span>
          </div>
          <div>
            <span
              class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
            >
              Hiring Date
            </span>
            <span class="text-lg font-semibold">{{ financial?.hiring_date ?? "—" }}</span>
          </div>
          <div>
            <span
              class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
            >
              Years of Experience
            </span>
            <span class="text-lg font-semibold">{{
              financial?.years_experience ?? "—"
            }}</span>
          </div>
          <div>
            <span
              class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
            >
              Specialization
            </span>
            <span class="text-lg font-semibold">{{
              financial?.specialization || "—"
            }}</span>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <Link
            :href="route('financial.invoices.index')"
            class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-indigo-200 transition-all group"
          >
            <div
              class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center mb-4"
            >
              <svg
                class="w-6 h-6 text-amber-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
            </div>
            <h3
              class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors"
            >
              Review Invoices
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              Review and issue draft invoices submitted by reception.
            </p>
          </Link>

          <Link
            :href="route('financial.paymentMethods.index')"
            class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-indigo-200 transition-all group"
          >
            <div
              class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4"
            >
              <svg
                class="w-6 h-6 text-emerald-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
            </div>
            <h3
              class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors"
            >
              Local Payment Methods
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              Manage bank, Jawwal Pay, PalPay, and card details shown to patients.
            </p>
          </Link>

          <Link
            :href="route('financial.salaryPayments.index')"
            class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-indigo-200 transition-all group"
          >
            <div
              class="w-11 h-11 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4"
            >
              <svg
                class="w-6 h-6 text-indigo-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 9V7a4 4 0 00-8 0v2M5 9h14l1 12H4L5 9z"
                />
              </svg>
            </div>
            <h3
              class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors"
            >
              Staff Salary Payments
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              Record, approve, hold, or mark payroll disbursements as paid.
            </p>
          </Link>
        </div>

        <!--
          NOTE: Financial\DashboardController::index() currently only passes the
          `financial` prop (the officer's own profile). It does not pass any summary
          counters (pending invoices, active payment methods, latest approved salaries).
          Per session rules, no PHP file was modified to invent those props, so this
          dashboard intentionally shows the officer profile + navigation instead of
          fabricated statistics. See the session changelog for details.
        -->
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
  financial: Object,
});

const officerName = computed(() =>
  props.financial?.user
    ? `${props.financial.user.first_name} ${props.financial.user.last_name}`
    : ""
);
</script>
