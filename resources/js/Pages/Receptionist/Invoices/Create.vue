<template>
  <AppLayout title="Reception Dashboard">
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            {{ isEditMode ? "Manage Financial Invoice" : "Generate Financial Invoice" }}
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Create or modify official billing statements linked to patient medical
            sessions.
          </p>
        </div>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-2xl mx-auto">
        <!-- Patient Summary Card -->
        <div
          class="bg-indigo-900 text-white rounded-3xl p-6 shadow-sm mb-6 grid grid-cols-2 gap-4"
        >
          <div>
            <span class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
              >Patient Name</span
            >
            <span class="text-lg font-semibold">
              {{ appointment.patient.user.first_name }}
              {{ appointment.patient.user.last_name }}
            </span>
          </div>

          <div>
            <span class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
              >Treating Dentist</span
            >
            <span class="text-lg font-semibold">
              Dr. {{ appointment.doctor.user.first_name }}
              {{ appointment.doctor.user.last_name }}
            </span>
          </div>

          <div class="mt-2">
            <span class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
              >Session Date</span
            >
            <span class="text-sm font-medium">{{ appointment.appointment_date }}</span>
          </div>

          <div class="mt-2">
            <span class="text-xs text-indigo-200 block uppercase font-bold tracking-wider"
              >Reason for Visit</span
            >
            <span class="text-sm font-medium truncate block max-w-[200px]">
              {{ appointment.reason_for_visit || "General Treatment" }}
            </span>
          </div>
        </div>

        <!-- Invoice Form Core -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Service / Rate Catalog Picker -->
            <div
              class="col-span-2 bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-4"
            >
              <label class="block text-sm font-bold text-slate-700 mb-2 text-left">
                🦷 Medical Procedure / Service Provided
              </label>
              <select
                v-model="selectedPricingId"
                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all"
              >
                <option value="" disabled>
                  -- Choose a procedure to load the official rate --
                </option>
                <option v-for="price in pricings" :key="price.id" :value="price.id">
                  {{ price.service_name }} - [ Official Rate:
                  {{ formatCurrency(price.amount, "ILS") }} ]
                </option>
              </select>
            </div>

            <!-- Total Amount -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >Total Treatment Cost (ILS)</label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="form.total_amount"
                @input="syncPaymentMetrics"
                placeholder="0.00"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-lg font-bold text-gray-900 outline-none focus:border-indigo-500 focus:bg-white transition-all"
                readonly
              />
              <p
                v-if="form.errors.total_amount"
                class="text-xs text-red-500 mt-1 font-medium"
              >
                {{ form.errors.total_amount }}
              </p>
            </div>

            <!-- Paid Amount -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >Amount Paid by Patient (ILS)</label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="form.paid_amount"
                @input="syncPaymentMetrics"
                placeholder="0.00"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-lg font-bold text-emerald-600 outline-none focus:border-indigo-500 focus:bg-white transition-all"
              />
              <p
                v-if="form.errors.paid_amount"
                class="text-xs text-red-500 mt-1 font-medium"
              >
                {{ form.errors.paid_amount }}
              </p>
            </div>

            <!-- Calculated Remaining Balance -->
            <div
              class="p-4 bg-amber-50/60 rounded-xl border border-amber-100/70 flex justify-between items-center"
            >
              <span class="text-sm font-semibold text-amber-800"
                >Remaining Balance Due:</span
              >
              <span
                class="text-xl font-black"
                :class="remainingBalance > 0 ? 'text-amber-600' : 'text-emerald-600'"
              >
                {{ formatCurrency(remainingBalance, "ILS") }}
              </span>
            </div>

            <!-- Payment Status (Controlled Dynamic Select Box) -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >Invoice Payment Status</label
              >
              <select
                v-model="form.status"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold outline-none focus:border-indigo-500 focus:bg-white transition-all"
              >
                <option value="unpaid">Unpaid</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="paid">Fully Paid</option>
              </select>
              <p v-if="form.errors.status" class="text-xs text-red-500 mt-1 font-medium">
                {{ form.errors.status }}
              </p>
            </div>

            <!-- Due Date & Time Component -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >Due Date & Time</label
              >
              <input
                type="datetime-local"
                v-model="form.due_date"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
              />
              <p
                v-if="form.errors.due_date"
                class="text-xs text-red-500 mt-1 font-medium"
              >
                {{ form.errors.due_date }}
              </p>
            </div>

            <!-- Form Action Footer Options -->
            <div
              class="flex items-center justify-center gap-4 pt-4 border-t border-gray-50"
            >
              <Link
                :href="route('receptionist.appointments.index')"
                class="px-5 py-2.5 bg-gray-600 hover:bg-white hover:text-gray-600 text-white text-sm font-semibold rounded-xl shadow-sm text-center transition-all"
                style="width: 33%"
              >
                Back to Schedule
              </Link>

              <button
                type="submit"
                :disabled="form.processing"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-white hover:text-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
                style="width: 33%"
              >
                {{
                  form.processing
                    ? "Processing..."
                    : isEditMode
                    ? "Update Invoice"
                    : "Issue Invoice"
                }}
              </button>

              <button
                v-if="isEditMode"
                type="button"
                @click="deleteInvoice"
                class="px-5 py-2.5 bg-red-600 hover:bg-white hover:text-red-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all"
                style="width: 33%"
              >
                Delete Invoice
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";
import { formatCurrency } from "@/Utils";

const props = defineProps({
  appointment: Object,
  pricings: Array,
  invoice: Object, // Backend now explicitly delivers an object or null
});

const { confirmAction } = useNotifications();

// Deterministic baseline evaluation for existing invoice state
const isEditMode = computed(() => !!(props.invoice && props.invoice.id));

// Helper converts database space separator "YYYY-MM-DD HH:mm:ss" into ISO compliant format "YYYY-MM-DDTHH:mm"
const formatToLocalDatetime = (dateTimeString) => {
  if (!dateTimeString) return "";
  return dateTimeString.replace(" ", "T").substring(0, 16);
};

// Precise selection mapping using safe float casting to bypass string type mismatches
const selectedPricingId = ref(
  isEditMode.value
    ? props.pricings.find(
        (p) => parseFloat(p.amount) === parseFloat(props.invoice.total_amount)
      )?.id ?? ""
    : ""
);

// Form Initialization Block
const form = useForm({
  total_amount: isEditMode.value ? parseFloat(props.invoice.total_amount) : 0,
  paid_amount: isEditMode.value ? parseFloat(props.invoice.paid_amount) : 0,
  status: isEditMode.value ? props.invoice.status : "unpaid",
  due_date: isEditMode.value ? formatToLocalDatetime(props.invoice.due_date) : "",
});

const deleteInvoice = () => {
  confirmAction(
    () => {
      router.delete(route("receptionist.invoices.destroy", props.appointment.id));
    },
    "Delete Invoice?",
    "Are you sure you want to permanently delete this invoice record? This action cannot be reverted."
  );
};

// Keep catalog selection mapped directly to total cost fields
watch(selectedPricingId, (newId) => {
  const catalogItem = props.pricings.find((p) => p.id === parseInt(newId));
  if (catalogItem) {
    form.total_amount = parseFloat(catalogItem.amount);
    syncPaymentMetrics();
  }
});

// Reactive structural calculation of remaining invoice dues
const remainingBalance = computed(() => {
  const total = parseFloat(form.total_amount) || 0;
  const paid = parseFloat(form.paid_amount) || 0;
  return Math.max(0, total - paid);
});

// Dynamic management calculation rules mapping state automatically
const syncPaymentMetrics = () => {
  const total = parseFloat(form.total_amount) || 0;
  const paid = parseFloat(form.paid_amount) || 0;

  if (total === 0) {
    form.status = "unpaid";
  } else if (paid >= total) {
    form.status = "paid";
  } else if (paid > 0 && paid < total) {
    form.status = "partially_paid";
  } else {
    form.status = "unpaid";
  }
};

const submit = () => {
  // Always hit the unified post action handler route safely
  form.post(route("receptionist.invoices.store", props.appointment.id));
};
</script>
