<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { computed } from "vue";

const props = defineProps({
  form: Object,
  patterns: Object,
  isPasswordSecure: Boolean,
  isPasswordMatched: Boolean,
  isPassword: Boolean,
  checkEmailRealTime: Function,
  emailStatus: Object,
});

// --- لوحة التحقق الفورية لكلمة المرور (Real-time Password Criteria Validation) ---
const passwordCriteria = computed(() => {
  const pwd = props.form.password || "";
  return [
    { id: "length", label: "At least 8 characters long", met: pwd.length >= 8 },
    { id: "upper", label: "Contains an uppercase letter (A-Z)", met: /[A-Z]/.test(pwd) },
    { id: "lower", label: "Contains a lowercase letter (a-z)", met: /[a-z]/.test(pwd) },
    { id: "number", label: "Contains at least one number (0-9)", met: /\d/.test(pwd) },
    {
      id: "special",
      label: "Contains a special character (@, $, !, %, *, etc.)",
      met: /[@$!%*?&]/.test(pwd),
    },
  ];
});

// احتساب النسبة المئوية لقوة كلمة المرور ديناميكياً
const passwordStrengthPercentage = computed(() => {
  const passedRules = passwordCriteria.value.filter((c) => c.met).length;
  return (passedRules / passwordCriteria.value.length) * 100;
});
</script>

<template>
  <div class="space-y-6">
    <div class="text-center">
      <h3 class="text-xl font-bold text-slate-800 tracking-tight">Contact Information</h3>
      <p class="text-xs text-slate-500 mt-1">
        Please provide valid credentials for your corporate profile.
      </p>
    </div>

    <!-- High Professional Username Field -->
    <div class="m-4">
      <InputLabel
        for="username"
        value="Username"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />
      <div class="relative mt-1">
        <TextInput
          id="username"
          v-model="form.username"
          type="text"
          maxlength="25"
          @input="form.username = form.username.replace(/[^a-zA-Z0-9_]/g, '')"
          class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800 font-mono tracking-wide"
          :class="{
            'border-emerald-500 focus:ring-emerald-500':
              form.username && form.username.length >= 3,
            'border-red-500 focus:ring-red-500':
              form.username && form.username.length < 3,
          }"
          placeholder="only_letters_numbers_or_underscore"
          required
        />

        <!-- أيقونات الحالة لاسم المستخدم للـ Scalability البصرية -->
        <div
          class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
        >
          <svg
            v-if="form.username && form.username.length >= 3"
            class="h-5 w-5 text-emerald-500"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
              clip-rule="evenodd"
            />
          </svg>
          <svg
            v-if="form.username && form.username.length < 3"
            class="h-5 w-5 text-red-500"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
      </div>

      <!-- شريط معلومات العداد والقيود السفلية للمدخل المعزول -->
      <div class="flex justify-between items-center mt-1.5">
        <p
          v-if="form.username && form.username.length < 3"
          class="text-xs font-semibold text-red-500"
        >
          ✕ Username must be at least 3 characters.
        </p>
        <p v-else class="text-[11px] font-medium text-slate-400">
          Allowed: A-Z, 0-9, and underscore (_). No dashes, slashes, or spaces.
        </p>
        <span
          class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded tracking-wider"
        >
          {{ form.username ? form.username.length : 0 }}/25
        </span>
      </div>
      <InputError class="mt-2" :message="form.errors.username" />
    </div>

    <!-- Corporate Email Address -->
    <div class="m-4">
      <InputLabel
        for="email"
        value="Corporate Email Address"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />
      <div class="relative mt-1">
        <TextInput
          id="email"
          type="email"
          @input="checkEmailRealTime ? checkEmailRealTime($event.target.value) : null"
          v-model="form.email"
          placeholder="name@company.com"
          class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
          :class="{
            'border-emerald-500 focus:ring-emerald-500': patterns.email.test(form.email),
            'border-red-500 focus:ring-red-500':
              form.email && !patterns.email.test(form.email),
          }"
          required
        />

        <div
          class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
        >
          <!-- ⚡ تم تأمين كافة الاستدعاءات بـ Optional Chaining مكرر لمنع الانهيار تماماً -->
          <svg
            v-if="emailStatus?.loading"
            class="animate-spin h-4 w-4 text-indigo-500"
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
          <svg
            v-if="emailStatus?.valid === true && !emailStatus?.loading"
            class="h-5 w-5 text-emerald-500"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
              clip-rule="evenodd"
            />
          </svg>
          <svg
            v-if="emailStatus?.valid === false && !emailStatus?.loading"
            class="h-5 w-5 text-red-500"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
      </div>

      <p
        v-if="emailStatus?.message"
        :class="emailStatus?.valid ? 'text-emerald-600' : 'text-red-500'"
        class="mt-2 text-xs font-semibold tracking-wide"
      >
        {{ emailStatus?.message }}
      </p>
      <InputError class="mt-2" :message="form.errors.email" />
    </div>

    <!-- Enhanced Secure Password Field -->
    <div class="m-4" v-if="isPassword">
      <InputLabel
        for="password"
        value="Password"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />
      <TextInput
        id="password"
        type="password"
        v-model="form.password"
        class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
        :class="{
          'border-emerald-500 focus:ring-emerald-500': isPasswordSecure,
          'border-red-500 focus:ring-red-500': form.password && !isPasswordSecure,
        }"
        required
      />

      <!-- Dynamic Smooth Progress Bar -->
      <div
        class="mt-3 bg-slate-100 rounded-full h-1.5 overflow-hidden w-full transition-all"
      >
        <div
          class="h-full rounded-full transition-all duration-500"
          :style="{ width: `${passwordStrengthPercentage}%` }"
          :class="[
            passwordStrengthPercentage < 40
              ? 'bg-red-500'
              : passwordStrengthPercentage < 80
              ? 'bg-amber-500'
              : 'bg-emerald-500',
          ]"
        ></div>
      </div>

      <!-- High Professional Criteria Checklist Container -->
      <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
        <p class="text-[11px] uppercase tracking-wider font-bold text-slate-500 mb-2">
          Password Security Checklist:
        </p>

        <div
          v-for="criteria in passwordCriteria"
          :key="criteria.id"
          class="flex items-center gap-2.5 text-xs transition-all duration-300"
        >
          <!-- ⚡ حماية طول الكلمة بـ form.password?.length منعاً لـ TypeError أخرى -->
          <div
            class="flex items-center justify-center w-4 h-4 rounded-full border transition-all"
            :class="[
              criteria.met
                ? 'bg-emerald-500 border-emerald-500 text-white'
                : form.password?.length > 0
                ? 'bg-red-50 border-red-200 text-red-500 animate-pulse'
                : 'bg-white border-slate-300 text-slate-400',
            ]"
          >
            <span class="text-[10px] font-bold">{{ criteria.met ? "✓" : "✕" }}</span>
          </div>
          <span
            class="font-medium transition-colors"
            :class="[
              criteria.met
                ? 'text-emerald-600 line-through opacity-70'
                : form.password?.length > 0
                ? 'text-red-600 font-semibold'
                : 'text-slate-500',
            ]"
          >
            {{ criteria.label }}
          </span>
        </div>
      </div>

      <InputError class="mt-2" :message="form.errors.password" />
    </div>

    <!-- Confirmation Password -->
    <div class="m-4" v-if="isPassword">
      <InputLabel
        for="password_confirmation"
        value="Confirm Password"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />
      <TextInput
        id="password_confirmation"
        type="password"
        v-model="form.password_confirmation"
        class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
        :class="{
          'border-emerald-500 focus:ring-emerald-500':
            isPasswordMatched && form.password_confirmation,
          'border-red-500 focus:ring-red-500':
            form.password_confirmation && !isPasswordMatched,
        }"
        required
        autocomplete="new-password"
      />
      <p
        v-if="form.password && form.password_confirmation && !isPasswordMatched"
        class="text-xs font-semibold text-red-500 mt-1.5"
      >
        ✕ Passwords do not match.
      </p>
      <InputError class="mt-2" :message="form.errors.password_confirmation" />
    </div>

    <!-- Phone Number -->
    <div class="m-4">
      <InputLabel
        for="phone"
        value="Phone"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />
      <div class="relative mt-1">
        <TextInput
          id="phone"
          v-model="form.phone"
          maxlength="10"
          class="block w-full pl-12 !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition text-sm py-2.5 text-slate-800"
          :class="{ 'border-emerald-500': patterns.phone.test(form.phone) }"
          placeholder="0590000000"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <span class="text-slate-400 sm:text-sm border-r pr-2">+97</span>
        </div>
      </div>
      <p
        v-if="form.phone && !patterns.phone.test(form.phone)"
        class="text-[11px] text-red-500 mt-1 font-medium"
      >
        Format: 059 or 056 followed by 7 digits.
      </p>
      <p
        v-if="form.phone && !patterns.onlyNumber.test(form.phone)"
        class="text-[11px] text-red-500 mt-1 font-medium"
      >
        Invalid input, must be numbers only.
      </p>
      <InputError class="mt-2" :message="form.errors.phone" />
    </div>

    <!-- Address -->
    <div class="m-4">
      <InputLabel
        for="address"
        value="Full Address"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />
      <textarea
        id="address"
        v-model="form.address"
        class="mt-1 block w-full border-slate-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition-all text-sm p-3 text-slate-800"
        :class="{ 'border-emerald-500': form.address && form.address.length > 5 }"
        rows="2"
        placeholder="City, Street, Building No."
      ></textarea>
      <div class="flex justify-end mt-1">
        <span class="text-[10px] font-semibold text-slate-400 tracking-wide">
          {{ form.address ? form.address.length : 0 }} characters
        </span>
      </div>
      <InputError class="mt-2" :message="form.errors.address" />
    </div>
  </div>
</template>
