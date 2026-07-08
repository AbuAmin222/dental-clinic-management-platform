<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
  status: String,
});

const form = useForm({
  email: "",
});

const submit = () => {
  form.post(route("password.email"));
};
</script>

<template>
  <Head title="Forgot Password" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-center items-center p-6 selection:bg-indigo-500 selection:text-white"
  >
    <div class="w-full max-w-md">
      <div class="mb-6 flex justify-center lg:justify-start">
        <Link
          :href="route('login')"
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
          Return to Authentication
        </Link>
      </div>

      <div
        class="bg-white border border-slate-100 shadow-xl shadow-slate-100/40 rounded-2xl p-8"
      >
        <div class="flex flex-col items-center text-center mb-6">
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
                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"
              />
            </svg>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
            Reset Password Request
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            Provide your corporate email address and we will dispatch a workspace
            decryption link.
          </p>
        </div>

        <div
          v-if="status"
          class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-700 shadow-sm"
        >
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <InputLabel
              for="email"
              value="Registered Email Address"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
              required
              autofocus
              autocomplete="username"
              placeholder="name@company.com"
            />
            <InputError class="mt-2 text-xs text-red-500" :message="form.errors.email" />
          </div>

          <div class="pt-2">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="w-full justify-center !rounded-xl shadow-lg shadow-indigo-100 !py-3 bg-indigo-600 hover:bg-indigo-700 text-sm font-bold tracking-wide transition duration-200"
            >
              Email Password Reset Link
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
