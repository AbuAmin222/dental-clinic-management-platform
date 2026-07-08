<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
// إذا كنت تفضل استيراد دالة التحقق مباشرة من ملفك المشترك:
// import { validatePassword } from '@/Utils/validationRules';

const props = defineProps({
  email: String,
  token: String,
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: "",
  password_confirmation: "",
});

// نظام الـ Frontend Validation المخصص الذي اعتمدناه في صفحة الـ Register
const clientErrors = ref({
  password: "",
  password_confirmation: "",
});

const validateFields = () => {
  let isValid = true;

  // 1. فحص قوة كلمة المرور (على معايير الأمن الطبي لبيانات المرضى)
  if (!form.password) {
    clientErrors.value.password = "Password is required.";
    isValid = false;
  } else if (form.password.length < 8) {
    clientErrors.value.password = "Password must be at least 8 characters long.";
    isValid = false;
  } else if (!/[A-Z]/.test(form.password) || !/[0-9]/.test(form.password)) {
    clientErrors.value.password =
      "Password must contain at least one uppercase letter and one number.";
    isValid = false;
  } else {
    clientErrors.value.password = "";
  }

  // 2. فحص تطابق كلمتي المرور
  if (form.password !== form.password_confirmation) {
    clientErrors.value.password_confirmation =
      "The password confirmation does not match.";
    isValid = false;
  } else {
    clientErrors.value.password_confirmation = "";
  }

  return isValid;
};

// مراقبة المدخلات حياً (Real-time Validation Feed) لمنح المستخدم تجربة سلسة
watch(
  () => form.password,
  () => {
    if (form.password) validateFields();
  }
);
watch(
  () => form.password_confirmation,
  () => {
    if (form.password_confirmation) validateFields();
  }
);

const submit = () => {
  // تشغيل الفحص الأمامي أولاً قبل إرهاق السيرفر بالـ Requests
  if (!validateFields()) return;

  form.post(route("password.update"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <Head title="Reset Password" />

  <div
    class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50/50"
  >
    <!-- الكارد الرئيسي بتصميم ناعم ومريح -->
    <div
      class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden sm:rounded-2xl border border-slate-100"
    >
      <!-- عنوان الصفحة المطور -->
      <div class="mb-8 text-center">
        <h2 class="text-xl font-bold text-slate-800 tracking-tight">
          Set Your New Password
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Ensure your new password complies with clinical data security standards.
        </p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <input type="hidden" v-model="form.token" />

        <!-- حقل البريد الإلكتروني المحمي (Locked Mode View) -->
        <div>
          <label
            class="block font-semibold text-[11px] uppercase tracking-wider text-slate-500 mb-2"
            >Email Address</label
          >
          <div class="relative rounded-lg shadow-sm">
            <input
              type="email"
              v-model="form.email"
              class="w-full bg-slate-50 border border-slate-200 text-slate-400 font-medium rounded-lg p-3 text-sm cursor-not-allowed transition duration-200 focus:outline-none"
              required
              readonly
            />
            <span class="absolute inset-y-0 right-0 flex items-center pr-3">
              <!-- أيقونة القفل للإشارة البصرية السريعة أنه غير قابل للتعديل -->
              <svg
                class="h-4 w-4 text-slate-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
              </svg>
            </span>
          </div>
        </div>

        <!-- حقل كلمة المرور الجديدة -->
        <div>
          <label
            class="block font-semibold text-[11px] uppercase tracking-wider text-slate-700 mb-2"
            >New Password</label
          >
          <input
            type="password"
            v-model="form.password"
            class="w-full border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-lg shadow-sm p-3 text-sm transition duration-150 outline-none"
            :class="{
              'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                clientErrors.password || form.errors.password,
            }"
            required
            autocomplete="new-password"
            placeholder="••••••••"
          />
          <!-- عرض أخطاء الـ Frontend والـ Backend بالتوالي -->
          <div
            v-if="clientErrors.password"
            class="text-xs font-medium text-red-500 mt-1.5 flex items-center gap-1"
          >
            ⚠ {{ clientErrors.password }}
          </div>
          <div
            v-else-if="form.errors.password"
            class="text-xs font-medium text-red-500 mt-1.5 flex items-center gap-1"
          >
            ⚠ {{ form.errors.password }}
          </div>
        </div>

        <!-- حقل تأكيد كلمة المرور -->
        <div>
          <label
            class="block font-semibold text-[11px] uppercase tracking-wider text-slate-700 mb-2"
            >Confirm New Password</label
          >
          <input
            type="password"
            v-model="form.password_confirmation"
            class="w-full border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-lg shadow-sm p-3 text-sm transition duration-150 outline-none"
            :class="{
              'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                clientErrors.password_confirmation,
            }"
            required
            autocomplete="new-password"
            placeholder="••••••••"
          />
          <div
            v-if="clientErrors.password_confirmation"
            class="text-xs font-medium text-red-500 mt-1.5 flex items-center gap-1"
          >
            ⚠ {{ clientErrors.password_confirmation }}
          </div>
        </div>

        <!-- زر الإرسال المطور مع تأثيرات التحميل -->
        <div class="pt-2">
          <button
            type="submit"
            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
            :disabled="form.processing"
            class="w-full justify-center inline-flex items-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-indigo-500/20 transition ease-in-out duration-150 shadow-sm shadow-indigo-200"
          >
            <svg
              v-if="form.processing"
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
            {{ form.processing ? "Updating Secure Credentials..." : "Reset Password" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
