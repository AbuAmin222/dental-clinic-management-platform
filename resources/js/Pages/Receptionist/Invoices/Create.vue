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
            Build the invoice from the doctor's service catalog — one line per procedure.
          </p>
        </div>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-3xl mx-auto">
        <!-- Patient Summary Card (unchanged) -->
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

        <p
          v-if="isEditMode && !hadPreloadedItems"
          class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-2xl p-3 mb-4 leading-relaxed"
        >
          This invoice already exists, but Receptionist\InvoiceController::create() does
          not eager-load the <code>items</code> relation on the <code>invoice</code> prop
          it passes to this page — so previously saved line items cannot be pre-filled
          here. The line-item list below starts empty; submitting will replace the
          invoice's items with whatever you add here. Ask a developer to add
          <code>->load('items')</code> (or eager-load it in the Controller query) to
          enable proper editing.
        </p>

        <!-- Invoice Form Core -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Line Items -->
            <div>
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-bold text-gray-700"
                  >🦷 Treatment Items</label
                >
                <button
                  type="button"
                  @click="openPicker"
                  class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-xl transition-all"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4"
                    />
                  </svg>
                  Add Item
                </button>
              </div>

              <div
                v-if="form.items.length === 0"
                class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-sm text-gray-400 font-medium"
              >
                No items added yet. Click "Add Item" to choose a procedure from the
                catalog.
              </div>

              <div v-else class="space-y-2">
                <div
                  v-for="(item, index) in form.items"
                  :key="index"
                  class="flex items-center gap-3 bg-slate-50 rounded-2xl border border-slate-100 p-3"
                >
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">
                      {{ item.service_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatCurrency(item.unit_price, "ILS") }} each
                    </p>
                  </div>
                  <input
                    type="number"
                    min="1"
                    v-model.number="item.quantity"
                    class="w-16 px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-center outline-none focus:border-indigo-500"
                  />
                  <span class="w-24 text-right text-sm font-bold text-gray-900">
                    {{ formatCurrency(item.unit_price * item.quantity, "ILS") }}
                  </span>
                  <button
                    type="button"
                    @click="removeItem(index)"
                    class="text-gray-400 hover:text-red-600 transition-colors p-1"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
              </div>
              <p v-if="form.errors.items" class="text-xs text-red-500 mt-2 font-medium">
                {{ form.errors.items }}
              </p>
            </div>

            <!-- Client-side computed total (display only; server recomputes authoritatively) -->
            <div
              class="p-4 bg-amber-50/60 rounded-xl border border-amber-100/70 flex justify-between items-center"
            >
              <span class="text-sm font-semibold text-amber-800">Estimated Total:</span>
              <span class="text-xl font-black text-amber-600">
                {{ formatCurrency(estimatedTotal, "ILS") }}
              </span>
            </div>

            <!-- Tax / Discount -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"
                  >Tax Amount (ILS)</label
                >
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="form.tax_amount"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
                />
                <p
                  v-if="form.errors.tax_amount"
                  class="text-xs text-red-500 mt-1 font-medium"
                >
                  {{ form.errors.tax_amount }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"
                  >Discount Amount (ILS)</label
                >
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="form.discount_amount"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
                />
                <p
                  v-if="form.errors.discount_amount"
                  class="text-xs text-red-500 mt-1 font-medium"
                >
                  {{ form.errors.discount_amount }}
                </p>
              </div>
            </div>

            <!-- Due Date & Time -->
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

            <!-- Form Actions -->
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
                :disabled="form.processing || form.items.length === 0"
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

    <!-- Service Picker Modal -->
    <div
      v-if="isPickerOpen"
      class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-gray-100 p-6 relative max-h-[80vh] flex flex-col"
      >
        <h3 class="text-lg font-bold text-gray-900 mb-4">Choose a Procedure</h3>
        <div class="overflow-y-auto space-y-2 flex-1">
          <button
            v-for="price in pricings"
            :key="price.id"
            type="button"
            @click="addItem(price)"
            class="w-full text-left px-4 py-3 bg-slate-50 hover:bg-indigo-50 rounded-xl transition-colors flex items-center justify-between"
          >
            <span class="text-sm font-semibold text-gray-900">{{
              price.service_name
            }}</span>
            <span class="text-sm font-bold text-emerald-600">{{
              formatCurrency(price.amount, "ILS")
            }}</span>
          </button>
          <p v-if="pricings.length === 0" class="text-center text-sm text-gray-400 py-8">
            This doctor has no priced services in the catalog yet.
          </p>
        </div>
        <button
          type="button"
          @click="isPickerOpen = false"
          class="mt-4 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors"
        >
          Close
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";
import { formatCurrency } from "@/Utils";

const props = defineProps({
  appointment: Object,
  pricings: Array,
  invoice: Object, // Backend delivers an object or null
});

const { confirmAction } = useNotifications();

const isEditMode = computed(() => !!(props.invoice && props.invoice.id));

// See the banner above the form: Receptionist\InvoiceController::create() does not
// eager-load `items` on the invoice it passes in, so this will normally be empty/absent
// even in edit mode. Handled defensively rather than assumed either way.
const hadPreloadedItems = computed(
  () => Array.isArray(props.invoice?.items) && props.invoice.items.length > 0
);

const formatToLocalDatetime = (dateTimeString) => {
  if (!dateTimeString) return "";
  return dateTimeString.replace(" ", "T").substring(0, 16);
};

const initialItems = hadPreloadedItems.value
  ? props.invoice.items.map((item) => {
      const pricing = props.pricings.find((p) => p.id === item.pricing_id);
      return {
        pricing_id: item.pricing_id,
        service_name: pricing?.service_name ?? "Service",
        unit_price: parseFloat(item.unit_price ?? pricing?.amount ?? 0),
        quantity: item.quantity,
      };
    })
  : [];

const form = useForm({
  items: initialItems,
  tax_amount: isEditMode.value ? parseFloat(props.invoice.tax_amount ?? 0) : 0,
  discount_amount: isEditMode.value ? parseFloat(props.invoice.discount_amount ?? 0) : 0,
  due_date: isEditMode.value ? formatToLocalDatetime(props.invoice.due_date) : "",
});

const isPickerOpen = ref(false);
const openPicker = () => {
  isPickerOpen.value = true;
};

const addItem = (pricing) => {
  const existing = form.items.find((i) => i.pricing_id === pricing.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    form.items.push({
      pricing_id: pricing.id,
      service_name: pricing.service_name,
      unit_price: parseFloat(pricing.amount),
      quantity: 1,
    });
  }
  isPickerOpen.value = false;
};

const removeItem = (index) => {
  form.items.splice(index, 1);
};

// Client-side display only — the server (InvoiceController::store → InvoiceService)
// always recomputes unit_price from the doctor's live pricing catalog and owns the
// authoritative totals via Invoice::recalculateTotals().
const estimatedTotal = computed(() => {
  const itemsTotal = form.items.reduce(
    (sum, item) => sum + item.unit_price * item.quantity,
    0
  );
  const tax = parseFloat(form.tax_amount) || 0;
  const discount = parseFloat(form.discount_amount) || 0;
  return Math.max(0, itemsTotal + tax - discount);
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

const submit = () => {
  const payload = {
    items: form.items.map((item) => ({
      pricing_id: item.pricing_id,
      quantity: item.quantity,
    })),
    tax_amount: form.tax_amount,
    discount_amount: form.discount_amount,
    due_date: form.due_date,
  };
  form
    .transform(() => payload)
    .post(route("receptionist.invoices.store", props.appointment.id));
};
</script>
