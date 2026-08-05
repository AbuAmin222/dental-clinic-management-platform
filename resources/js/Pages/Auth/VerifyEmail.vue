<!-- VerifyEmail.vue -->
<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";

const props = defineProps({
  status: String,
});

const form = useForm({});
const isCooldown = ref(false);
const cooldownTime = ref(60);

const submit = () => {
  if (isCooldown.value) return;

  form.post(route("verification.send"), {
    onSuccess: () => {
      startCooldown();
    },
  });
};

const startCooldown = () => {
  isCooldown.value = true;
  const timer = setInterval(() => {
    cooldownTime.value--;
    if (cooldownTime.value <= 0) {
      clearInterval(timer);
      isCooldown.value = false;
      cooldownTime.value = 60;
    }
  }, 1000);
};

const verificationStatusMessage = computed(() => {
  return props.status === "verification-link-sent";
});
</script>

<template>
  <Head title="Email Verification" />

  <div
    class="font-sans text-slate-900 antialiased bg-gradient-to-br from-slate-50 via-gray-100 to-slate-100 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full flex flex-col items-center">
      <div class="transform hover:scale-105 transition-transform duration-300 mb-8">
        <AuthenticationCardLogo />
      </div>

      <div
        class="w-full bg-white shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-100 overflow-hidden backdrop-blur-sm"
      >
        <div class="bg-gradient-to-r from-slate-800 to-indigo-900 p-6 text-center">
          <div
            class="w-12 h-12 rounded-full bg-indigo-500/10 flex items-center justify-center mx-auto mb-3"
          >
            <svg
              class="h-6 w-6 text-indigo-400 animate-pulse"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0l-7.5-4.615m19.5 0v3A2.25 2.25 0 0119.5 12h-15A2.25 2.25 0 012.25 9.75v-3"
              />
            </svg>
          </div>
          <h2 class="text-xl font-bold text-white tracking-tight">Verify Your Email</h2>
          <p class="text-slate-300 text-xs mt-1 font-medium">
            Security Clearance Required
          </p>
        </div>

        <div class="p-6 space-y-4">
          <p class="text-sm text-slate-600 leading-relaxed text-center">
            Thanks for signing up! Before getting started, could you verify your email
            address by clicking on the link we just emailed to you? If you didn't receive
            it, we will gladly send you another.
          </p>

          <div
            v-if="verificationStatusMessage"
            class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3"
          >
            <svg
              class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5"
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
            <p class="text-xs font-semibold text-emerald-700">
              A new verification link has been successfully dispatched to the email
              address you provided during registration.
            </p>
          </div>

          <form
            @submit.prevent="submit"
            class="mt-6 flex items-center justify-between gap-4"
          >
            <button
              type="submit"
              class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-indigo-100"
              :disabled="form.processing || isCooldown"
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
              {{
                isCooldown ? `Resend in (${cooldownTime}s)` : "Resend Verification Email"
              }}
            </button>

            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="text-xs font-semibold text-slate-400 hover:text-slate-600 underline transition-colors"
            >
              Log Out
            </Link>
          </form>
        </div>

        <div
          class="bg-slate-50/70 p-5 flex items-center justify-between border-t border-slate-50"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center shadow-md shadow-indigo-200"
            >
              <span class="text-white font-black text-xs tracking-wider font-mono"
                >DD</span
              >
            </div>
            <div>
              <h4 class="text-xs font-bold text-slate-800 tracking-wide">
                Engineered by <span class="text-indigo-600 font-extrabold">Dev.DIA</span>
              </h4>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <a
              href="https://www.linkedin.com/in/diael-dinhabib/"
              target="_blank"
              rel="noopener noreferrer"
              class="w-6 h-6 rounded-md bg-white border border-slate-200 flex items-center justify-center text-[10px] text-slate-500 hover:text-blue-600 transition-colors shadow-sm"
            >
              in
            </a>
            <a
              href="https://www.freelancer.com/u/DevDiaeldin"
              target="_blank"
              rel="noopener noreferrer"
              class="w-6 h-6 rounded-md bg-white border border-slate-200 flex items-center justify-center text-[10px] text-slate-500 hover:text-cyan-600 transition-colors shadow-sm"
            >
              f
            </a>
            <a
              href="https://europa.eu/europass/eportfolio/screen/share/74f94f0c-da5b-40d4-b8cd-4129f9e8dd01?lang=en"
              target="_blank"
              rel="noopener noreferrer"
              class="w-6 h-6 rounded-md bg-white border border-slate-200 flex items-center justify-center text-[10px] text-slate-500 hover:text-amber-500 transition-colors shadow-sm"
            >
              ★
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
