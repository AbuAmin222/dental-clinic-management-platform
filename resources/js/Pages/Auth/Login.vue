<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
  canResetPassword: {
    type: Boolean,
    default: true,
  },
  status: {
    type: String,
    default: "",
  },
});

const form = useForm({
  username: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};
</script>

<template>
  <Head title="Secure Authentication" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-center items-center p-6 selection:bg-indigo-500 selection:text-white"
  >
    <div class="w-full max-w-md">
      <!-- Back to Portal Navigation Anchor -->
      <div class="mb-6 flex justify-center lg:justify-start">
        <Link
          :href="'/'"
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-indigo-600 transition duration-200 group"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2.5"
            stroke="currentColor"
            class="w-4 h-4 transition-transform group-hover:-translate-x-0.5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
            />
          </svg>
          Back to Welcome Portal
        </Link>
      </div>

      <!-- Core Auth Card Layer -->
      <div
        class="bg-white border border-slate-100 shadow-xl shadow-slate-100/40 rounded-2xl p-8 transition duration-300"
      >
        <!-- Branding Identity & Subtext -->
        <div class="flex flex-col items-center text-center mb-8">
          <div
            class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-100 mb-4"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2.5"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
              />
            </svg>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
            Account Sign In
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            Provide your verified professional credentials to access workspace modules.
          </p>
        </div>

        <!-- Optional Session Status Notifications -->
        <div
          v-if="status"
          class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-700 shadow-sm"
        >
          {{ status }}
        </div>

        <!-- Submission Form Handler Form Layer -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Identity Input: Username Parameter -->
          <div>
            <InputLabel
              for="username"
              value="Professional Username Address"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="username"
              v-model="form.username"
              type="username"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
              placeholder="name@company.com"
              required
              autofocus
              autocomplete="username"
            />
            <InputError
              class="mt-2 text-xs text-red-500"
              :message="form.errors.username"
            />
          </div>

          <!-- Secret Key Input: Password Parameter -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <InputLabel
                for="password"
                value="Security Password Key"
                class="text-slate-700 font-semibold text-xs uppercase tracking-wider"
              />

              <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline transition"
              >
                Forgot Password?
              </Link>
            </div>

            <TextInput
              id="password"
              v-model="form.password"
              type="password"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
              placeholder="••••••••••••"
              required
              autocomplete="current-password"
            />
            <InputError
              class="mt-2 text-xs text-red-500"
              :message="form.errors.password"
            />
          </div>

          <!-- Remember Me Context Toggle Trigger -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <Checkbox
                name="remember"
                v-model:checked="form.remember"
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 h-4 w-4 transition"
              />
              <span
                class="text-xs font-bold text-slate-500 hover:text-slate-700 transition"
                >Remember this workstation session</span
              >
            </label>
          </div>

          <!-- Auth Control Button Action Row -->
          <div class="pt-2">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="w-full justify-center !rounded-xl shadow-lg shadow-indigo-100 !py-3 bg-indigo-600 hover:bg-indigo-700 text-sm font-bold tracking-wide transition duration-200"
            >
              <span v-if="form.processing" class="inline-flex items-center gap-2">
                <svg
                  class="animate-spin h-4 w-4 text-white"
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
                Decrypting Workspace Session...
              </span>
              <span v-else>Authenticate & Open Session</span>
            </PrimaryButton>
          </div>
        </form>

        <!-- Structured Account Creation Transition Splitter -->
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
          <p class="text-xs text-slate-400 font-semibold">
            New to the operations interface?
            <Link
              :href="route('register')"
              class="text-indigo-600 hover:text-indigo-700 font-bold ml-1 hover:underline transition"
            >
              Create Operator Account
            </Link>
          </p>
        </div>
      </div>

      <!-- Legal Platform Context Footer -->
      <p class="text-center text-[11px] text-slate-400 font-medium mt-6 tracking-wide">
        Protected by hardware session isolation filters. Unauthorized modification
        attempts logged under security audit protocols.
      </p>
    </div>
  </div>
</template>
