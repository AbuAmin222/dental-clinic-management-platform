<script setup>
import { ref } from "vue";

const props = defineProps({
  gatewayName: String,
  gateway: String,
  amount: [String, Number],
  tx: String,
});

const isSubmitting = ref(false);

// محاكاة نجاح عملية الدفع والتوجه إلى الـ Callback الخاص بالباك إند
const handleSuccessPayment = () => {
  isSubmitting.value = true;

  // بناء رابط الـ Callback الديناميكي الموجه إلى سيرفر لارافيل لتأكيد المعاملة
  const callbackUrl = `/patient/payment/callback/${props.gateway}/${props.tx}`;

  // الانتقال المباشر لمحاكاة سلوك بوابات الدفع الحقيقية عند العودة للموقع
  window.location.href = callbackUrl;
};

// محاكاة إلغاء العملية والعودة للوحة تحكم المريض
const handleCancelPayment = () => {
  window.location.href = "/patient/dashboard";
};
</script>

<template>
  <Head title="Secure Sandbox Gateway" />

  <div
    class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 font-sans text-slate-800"
  >
    <!-- كرت بوابة الدفع المحاكية -->
    <div
      class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transition-all duration-300"
    >
      <!-- رأس الصفحة الهيدر المالي -->
      <div
        class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-8 text-white text-center relative"
      >
        <div
          class="absolute top-3 right-4 bg-yellow-400/20 text-yellow-300 text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider border border-yellow-400/30"
        >
          Test Environment
        </div>
        <h2 class="text-xl font-bold tracking-wide mb-1">💸 Sandbox Payment</h2>
        <p class="text-indigo-100 text-sm">Dental Clinic Secure Payment Simulator</p>
      </div>

      <!-- تفاصيل الفاتورة والمعاملة -->
      <div class="p-6 space-y-6">
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
          <div
            class="flex justify-between items-center text-sm border-b border-slate-200/60 pb-2"
          >
            <span class="text-slate-500 font-medium">Selected Gateway:</span>
            <span class="font-semibold text-indigo-600">{{ gatewayName }}</span>
          </div>
          <div
            class="flex justify-between items-center text-sm border-b border-slate-200/60 pb-2"
          >
            <span class="text-slate-500 font-medium">Transaction ID:</span>
            <span
              class="font-mono text-xs text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200"
              >{{ tx }}</span
            >
          </div>
          <div class="flex justify-between items-center pt-1">
            <span class="text-slate-700 font-semibold text-base">Total Amount:</span>
            <span class="text-2xl font-black text-emerald-600"
              >{{ amount }} <span class="text-sm font-bold">ILS</span></span
            >
          </div>
        </div>

        <!-- رسالة تنبيه للمطور -->
        <div
          class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700 leading-relaxed"
        >
          <span class="text-base">ℹ️</span>
          <div>
            You are currently inside the sandbox gateway. Clicking
            <strong>"Confirm Simulation"</strong> will update the invoice status via
            database transactions and clear the balance securely.
          </div>
        </div>

        <!-- أزرار التحكم والاتصال بالباك إند -->
        <div class="space-y-3 pt-2">
          <button
            @click="handleSuccessPayment"
            :disabled="isSubmitting"
            class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-emerald-600/10 transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span
              v-if="isSubmitting"
              class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"
            ></span>
            <span v-else>🟢 Confirm Payment (Simulate Success)</span>
          </button>

          <button
            @click="handleCancelPayment"
            :disabled="isSubmitting"
            class="w-full bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-500 hover:text-slate-700 font-medium py-2.5 px-4 rounded-xl border border-slate-200 transition-all duration-150 text-center text-sm disabled:opacity-50"
          >
            ❌ Cancel & Return
          </button>
        </div>
      </div>

      <!-- تذييل الصفحة الحماية الأمنية -->
      <div
        class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-center text-xs text-slate-400 flex items-center justify-center gap-1.5"
      >
        🔒 Secured End-to-End Simulation Environment
      </div>
    </div>
  </div>
</template>
