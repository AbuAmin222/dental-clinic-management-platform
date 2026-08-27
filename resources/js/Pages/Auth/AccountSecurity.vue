<script setup>
import { ref, computed, onUnmounted } from "vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";

const props = defineProps({
  mustChangePassword: {
    type: Boolean,
    default: false,
  },
  phoneVerified: {
    type: Boolean,
    default: false,
  },
});

const page = usePage();
const userEmail = computed(() => page.props.auth?.user?.email || "your email address");

// --- Step 1: Password Form ---
const showPasswords = ref(false);
const passwordForm = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const passwordStrength = computed(() => {
  const p = passwordForm.password;
  if (!p) return 0;
  let score = 0;
  if (p.length >= 8) score += 25;
  if (/[A-Z]/.test(p)) score += 25;
  if (/[0-9]/.test(p)) score += 25;
  if (/[^A-Za-z0-9]/.test(p)) score += 25;
  return score;
});

const submitPassword = () => {
  passwordForm.put(route("account-security.password.update"), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  });
};

// --- Step 2: Email OTP Form ---
const otpDigits = ref(["", "", "", "", "", ""]);
const otpInputs = ref([]);

const codeForm = useForm({
  code: "",
});

const handleOtpInput = (index, event) => {
  const val = event.target.value;
  if (!/^\d*$/.test(val)) {
    otpDigits.value[index] = "";
    return;
  }

  otpDigits.value[index] = val.slice(-1);

  if (otpDigits.value[index] && index < 5) {
    otpInputs.value[index + 1]?.focus();
  }

  codeForm.code = otpDigits.value.join("");
};

const handleOtpKeyDown = (index, event) => {
  if (event.key === "Backspace" && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus();
  }
};

const handleOtpPaste = (event) => {
  event.preventDefault();
  const pasted = event.clipboardData.getData("text").trim();
  if (!/^\d{6}$/.test(pasted)) return;

  pasted.split("").forEach((char, i) => {
    otpDigits.value[i] = char;
  });
  codeForm.code = pasted;
  otpInputs.value[5]?.focus();
};

const submitCode = () => {
  codeForm.code = otpDigits.value.join("");
  codeForm.post(route("account-security.verify-code"), {
    preserveScroll: true,
    onError: () => {
      otpDigits.value = ["", "", "", "", "", ""];
      otpInputs.value[0]?.focus();
    },
  });
};

// --- Resend Rate Limit Timer ---
const cooldownSecs = ref(0);
let timerInterval = null;

const startCooldown = (seconds = 60) => {
  cooldownSecs.value = seconds;
  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    if (cooldownSecs.value > 0) {
      cooldownSecs.value--;
    } else {
      clearInterval(timerInterval);
    }
  }, 1000);
};

// Aggregate code errors from form instance and global Inertia page props
const codeError = computed(() => codeForm.errors.code || page.props.errors?.code);

const resendCode = () => {
  if (cooldownSecs.value > 0) return;

  codeForm.clearErrors();

  router.post(
    route("account-security.resend-code"),
    {},
    {
      preserveScroll: true,
      onSuccess: () => startCooldown(60),
    }
  );
};

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
  <Head title="Account Security Verification" />

  <div
    class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 font-sans selection:bg-indigo-500 selection:text-white"
  >
    <!-- Background Ambient Glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
      <div
        class="absolute -top-40 -left-40 w-[30rem] h-[30rem] bg-indigo-600/10 rounded-full blur-[128px]"
      ></div>
      <div
        class="absolute -bottom-40 -right-40 w-[30rem] h-[30rem] bg-blue-600/10 rounded-full blur-[128px]"
      ></div>
    </div>

    <!-- Main Card Container -->
    <div
      class="w-full max-w-lg bg-slate-900/90 border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-md overflow-hidden"
    >
      <!-- Card Header -->
      <div class="p-6 sm:p-8 border-b border-slate-800/80 text-center">
        <div
          class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 mb-4 shadow-sm"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
            />
          </svg>
        </div>

        <h1 class="text-xl font-bold tracking-tight text-slate-100">
          Security Verification
        </h1>
        <p class="mt-1 text-xs sm:text-sm text-slate-400">
          Complete authentication to continue to your workspace
        </p>

        <!-- Progress Steps -->
        <div class="mt-6 grid grid-cols-2 gap-3">
          <!-- Step 1: Password -->
          <div
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border text-xs font-medium transition-all"
            :class="[
              !mustChangePassword
                ? 'bg-emerald-950/40 border-emerald-500/30 text-emerald-400'
                : 'bg-indigo-950/40 border-indigo-500/40 text-indigo-300 ring-1 ring-indigo-500/20',
            ]"
          >
            <span
              class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0"
              :class="
                !mustChangePassword
                  ? 'bg-emerald-500 text-slate-950'
                  : 'bg-indigo-500 text-white'
              "
            >
              <svg
                v-if="!mustChangePassword"
                class="w-3 h-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="3"
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <span v-else>1</span>
            </span>
            <span class="truncate">Update Password</span>
          </div>

          <!-- Step 2: Email Security -->
          <div
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border text-xs font-medium transition-all"
            :class="[
              phoneVerified
                ? 'bg-emerald-950/40 border-emerald-500/30 text-emerald-400'
                : !mustChangePassword
                ? 'bg-indigo-950/40 border-indigo-500/40 text-indigo-300 ring-1 ring-indigo-500/20'
                : 'bg-slate-900 border-slate-800 text-slate-500',
            ]"
          >
            <span
              class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0"
              :class="
                phoneVerified
                  ? 'bg-emerald-500 text-slate-950'
                  : !mustChangePassword
                  ? 'bg-indigo-500 text-white'
                  : 'bg-slate-800 text-slate-400'
              "
            >
              <svg
                v-if="phoneVerified"
                class="w-3 h-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="3"
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <span v-else>2</span>
            </span>
            <span class="truncate">Email Verification</span>
          </div>
        </div>
      </div>

      <!-- Flash Notification Banner -->
      <div
        v-if="$page.props.flash?.success"
        class="mx-6 mt-6 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2.5"
      >
        <svg
          class="w-4 h-4 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <span>{{ $page.props.flash.success }}</span>
      </div>

      <!-- Content Area -->
      <div class="p-6 sm:p-8 space-y-6">
        <!-- STEP 1: PASSWORD CHANGE -->
        <section v-if="mustChangePassword" class="space-y-4">
          <div>
            <h2 class="text-sm font-semibold text-slate-200">Change Required Password</h2>
            <p class="text-xs text-slate-400 mt-0.5">
              Please update your temporary password before accessing the dashboard.
            </p>
          </div>

          <form @submit.prevent="submitPassword" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-slate-300 mb-1.5"
                >Current Password</label
              >
              <input
                v-model="passwordForm.current_password"
                :type="showPasswords ? 'text' : 'password'"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                placeholder="Enter current password"
                required
              />
              <p
                v-if="passwordForm.errors.current_password"
                class="text-xs text-rose-400 mt-1"
              >
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-300 mb-1.5"
                >New Password</label
              >
              <input
                v-model="passwordForm.password"
                :type="showPasswords ? 'text' : 'password'"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                placeholder="Minimum 8 characters"
                required
              />

              <!-- Strength Bar -->
              <div v-if="passwordForm.password" class="mt-2 space-y-1">
                <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                  <div
                    class="h-full transition-all duration-300"
                    :style="{ width: `${passwordStrength}%` }"
                    :class="{
                      'bg-rose-500': passwordStrength <= 25,
                      'bg-amber-500': passwordStrength === 50 || passwordStrength === 75,
                      'bg-emerald-500': passwordStrength === 100,
                    }"
                  ></div>
                </div>
              </div>
              <p v-if="passwordForm.errors.password" class="text-xs text-rose-400 mt-1">
                {{ passwordForm.errors.password }}
              </p>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-300 mb-1.5"
                >Confirm New Password</label
              >
              <input
                v-model="passwordForm.password_confirmation"
                :type="showPasswords ? 'text' : 'password'"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                placeholder="Repeat new password"
                required
              />
            </div>

            <div class="flex items-center justify-between pt-1">
              <label
                class="flex items-center gap-2 cursor-pointer text-xs text-slate-400 hover:text-slate-200 select-none"
              >
                <input
                  type="checkbox"
                  v-model="showPasswords"
                  class="rounded bg-slate-950 border-slate-800 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                />
                <span>Show passwords</span>
              </label>
            </div>

            <button
              type="submit"
              :disabled="passwordForm.processing"
              class="w-full mt-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-medium text-xs sm:text-sm shadow-lg shadow-indigo-600/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 transition-all flex items-center justify-center gap-2"
            >
              <span
                v-if="passwordForm.processing"
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              ></span>
              <span>Save & Continue</span>
            </button>
          </form>
        </section>

        <!-- STEP 2: EMAIL VERIFICATION -->
        <section v-else-if="!phoneVerified" class="space-y-6">
          <div>
            <h2 class="text-sm font-semibold text-slate-200">Email Verification</h2>
            <p class="text-xs text-slate-400 mt-0.5">
              Enter the 6-digit security code sent to
              <span class="text-slate-200 font-medium">{{ userEmail }}</span
              >.
            </p>
          </div>

          <form @submit.prevent="submitCode" class="space-y-6">
            <div>
              <!-- OTP Inputs Grid -->
              <div class="grid grid-cols-6 gap-2 sm:gap-3" @paste="handleOtpPaste">
                <input
                  v-for="(digit, idx) in otpDigits"
                  :key="idx"
                  :ref="(el) => (otpInputs[idx] = el)"
                  type="text"
                  inputmode="numeric"
                  maxlength="1"
                  v-model="otpDigits[idx]"
                  @input="(e) => handleOtpInput(idx, e)"
                  @keydown="(e) => handleOtpKeyDown(idx, e)"
                  class="w-full h-12 text-center text-lg font-mono font-bold bg-slate-950 border border-slate-800 rounded-xl text-indigo-300 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition-all"
                  :class="{ 'border-rose-500/80': codeError }"
                />
              </div>
              <p v-if="codeError" class="text-xs text-rose-400 mt-2 text-center">
                {{ codeError }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="codeForm.processing || codeForm.code.length !== 6"
              class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-medium text-xs sm:text-sm shadow-lg shadow-indigo-600/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 transition-all flex items-center justify-center gap-2"
            >
              <span
                v-if="codeForm.processing"
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              ></span>
              <span>Verify Verification Code</span>
            </button>
          </form>

          <!-- Resend Action -->
          <div
            class="pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400"
          >
            <span>Didn't receive the email?</span>
            <button
              @click="resendCode"
              :disabled="cooldownSecs > 0"
              class="font-medium text-indigo-400 hover:text-indigo-300 disabled:text-slate-600 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="cooldownSecs > 0">Resend in {{ cooldownSecs }}s</span>
              <span v-else>Resend Code</span>
            </button>
          </div>
        </section>

        <!-- VERIFIED STATE -->
        <section v-else class="text-center py-6 space-y-4">
          <div
            class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
          <h3 class="text-base font-bold text-slate-100">Verification Complete</h3>
          <p class="text-xs text-slate-400 max-w-xs mx-auto">
            Your account security status has been verified.
          </p>
          <button
            @click="router.visit(route('dashboard'))"
            class="py-2.5 px-5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium transition-colors"
          >
            Continue to Dashboard
          </button>
        </section>
      </div>
    </div>
  </div>
</template>
