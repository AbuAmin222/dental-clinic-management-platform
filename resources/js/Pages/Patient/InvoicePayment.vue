<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import axios from "axios";

import { formatCurrency } from "@/Utils";
import { useNotifications } from "@/Composables";

const props = defineProps({
  invoice: Object,
});

const { notify, toast } = useNotifications();
const isProcessing = ref(false);

const form = ref({
  payment_method: "visa", // القيمة الافتراضية الأولى
  amount: props.invoice.balance_amount,
});

const submitPayment = async () => {
  if (form.value.amount <= 0 || form.value.amount > props.invoice.balance_amount) {
    notify(
      "Invalid Amount",
      `Please specify an amount between 1 ILS and ${props.invoice.balance_amount} ILS`,
      "error"
    );
    return;
  }

  isProcessing.value = true;
  toast("Connecting to Gateway Hub...", "info");

  try {
    // إرسال الطلب بشكل آمن، والباك إند سيرد علينا بـ JSON يحتوي على رابط التوجيه الخارجي للبوابة
    const response = await axios.post(
      route("patient.invoices.pay", props.invoice.id),
      form.value
    );

    if (response.data && response.data.redirect_url) {
      toast("Secure channel established. Redirecting...", "success");
      // تحويل فيزيائي حقيقي للمتصفح إلى صفحة البنك أو المحفظة الخارجية بدون مشاكل ووعود مكسورة
      window.location.href = response.data.redirect_url;
    } else {
      throw new Error("Unable to resolve execution payload from gateway.");
    }
  } catch (error) {
    isProcessing.value = false;
    const errorMessage =
      error.response?.data?.message ||
      "The selected payment gateway is currently undergoing maintenance.";
    notify("Gateway Connection Failed", errorMessage, "error");
  }
};
</script>

<template>
  <AppLayout title="Secure Checkout">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Secure Electronic Checkout
        </h2>
        <Link
          :href="route('patient.dashboard')"
          class="text-sm font-bold text-indigo-600 hover:underline"
        >
          Back to Dashboard
        </Link>
      </div>
    </template>

    <div class="py-12 bg-gray-50" dir="ltr">
      <div
        class="max-w-5xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8"
      >
        <div
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit space-y-4 md:col-span-1"
        >
          <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">
            Statement Invoice
          </h3>
          <div class="text-sm space-y-2">
            <div class="flex justify-between text-gray-500">
              <span>Invoice Ref:</span>
              <span class="font-bold text-gray-800">#INV-{{ invoice.id }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
              <span>Practitioner:</span>
              <span class="font-medium text-gray-800">
                Dr. {{ invoice.doctor?.user?.first_name }}
                {{ invoice.doctor?.user?.last_name }}
              </span>
            </div>
            <hr class="border-gray-100" />
            <div class="flex justify-between text-gray-500">
              <span>Gross Bill:</span>
              <span class="font-semibold text-gray-800">
                {{ formatCurrency(invoice.total_amount, "ILS") }}
              </span>
            </div>
            <div class="flex justify-between text-gray-500">
              <span>Cleared Cash:</span>
              <span class="text-emerald-600 font-semibold">
                {{ formatCurrency(invoice.paid_amount, "ILS") }}
              </span>
            </div>
            <div
              class="flex justify-between text-gray-900 font-bold bg-indigo-50 p-2.5 rounded-lg mt-3"
            >
              <span>Outstanding:</span>
              <span class="text-indigo-700">
                {{ formatCurrency(invoice.balance_amount, "ILS") }}
              </span>
            </div>
          </div>
        </div>

        <div
          class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 md:col-span-2"
        >
          <h3 class="text-lg font-bold text-gray-900 mb-2">Select Payment Channel</h3>
          <p class="text-xs text-gray-400 mb-6">
            Fully regulated via the Palestine Monetary Authority (PMA) protocols.
          </p>

          <form @submit.prevent="submitPayment" class="space-y-6">
            <!-- استبدل مصفوفة البطاقات بالكامل بهذا الجزء المصلح بدقة -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- 1. Bank of Palestine -->
              <label
                class="border-2 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer transition relative hover:bg-gray-50 select-none"
                :class="
                  form.gateway === 'visa'
                    ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600'
                    : 'border-gray-200'
                "
              >
                <input type="radio" value="visa" v-model="form.gateway" class="hidden" />
                <span class="text-2xl">💳</span>
                <span class="font-bold text-sm text-gray-800">Credit / Visa Card</span>
                <span class="text-[10px] text-gray-400 text-center"
                  >Bank of Palestine Gateway</span
                >
                <span
                  v-if="form.gateway === 'visa'"
                  class="absolute top-2 right-2 text-indigo-600 text-xs"
                  >✓</span
                >
              </label>

              <!-- 2. Jawwal Pay -->
              <label
                class="border-2 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer transition relative hover:bg-gray-50 select-none"
                :class="
                  form.gateway === 'jawwal_pay'
                    ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600'
                    : 'border-gray-200'
                "
              >
                <input
                  type="radio"
                  value="jawwal_pay"
                  v-model="form.gateway"
                  class="hidden"
                />
                <span class="text-2xl">📱</span>
                <span class="font-bold text-sm text-gray-800">Jawwal Pay</span>
                <span class="text-[10px] text-gray-400 text-center"
                  >Local Mobile Wallet</span
                >
                <span
                  v-if="form.gateway === 'jawwal_pay'"
                  class="absolute top-2 right-2 text-indigo-600 text-xs"
                  >✓</span
                >
              </label>

              <!-- 3. PalPay Wallet -->
              <label
                class="border-2 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer transition relative hover:bg-gray-50 select-none"
                :class="
                  form.gateway === 'palpay'
                    ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600'
                    : 'border-gray-200'
                "
              >
                <input
                  type="radio"
                  value="palpay"
                  v-model="form.gateway"
                  class="hidden"
                />
                <span class="text-2xl">⚡</span>
                <span class="font-bold text-sm text-gray-800">PalPay Wallet</span>
                <span class="text-[10px] text-gray-400 text-center"
                  >PalPay Electronic System</span
                >
                <span
                  v-if="form.gateway === 'palpay'"
                  class="absolute top-2 right-2 text-indigo-600 text-xs"
                  >✓</span
                >
              </label>

              <!-- 4. PayPal -->
              <label
                class="border-2 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer transition relative hover:bg-gray-50 select-none"
                :class="
                  form.gateway === 'paypal'
                    ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600'
                    : 'border-gray-200'
                "
              >
                <input
                  type="radio"
                  value="paypal"
                  v-model="form.gateway"
                  class="hidden"
                />
                <span class="text-2xl">🌍</span>
                <span class="font-bold text-sm text-gray-800">PayPal</span>
                <span class="text-[10px] text-gray-400 text-center"
                  >Global Digital Merchant</span
                >
                <span
                  v-if="form.gateway === 'paypal'"
                  class="absolute top-2 right-2 text-indigo-600 text-xs"
                  >✓</span
                >
              </label>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >Specify Settlement Amount (ILS) *</label
              >
              <div class="relative rounded-md shadow-sm max-w-xs">
                <input
                  type="number"
                  step="0.01"
                  v-model="form.amount"
                  :max="invoice.balance_amount"
                  :disabled="isProcessing"
                  class="w-full rounded-lg border-gray-300 pl-4 pr-12 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold disabled:bg-gray-100"
                />
                <div
                  class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                >
                  <span class="text-gray-400 text-xs font-bold">ILS</span>
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end">
              <button
                type="submit"
                :disabled="isProcessing"
                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
              >
                <svg
                  v-if="isProcessing"
                  class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span v-if="isProcessing">Connecting Gateway Hub...</span>
                <span v-else>Proceed to External Secure Portal</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
