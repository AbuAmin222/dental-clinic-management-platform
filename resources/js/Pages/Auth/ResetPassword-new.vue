<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

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

const submit = () => {
  form.post(route("password.update"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <Head title="Reset Password" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-center items-center p-6 selection:bg-indigo-500 selection:text-white"
  >
    <div class="w-full max-w-md">
      <div
        class="bg-white border border-slate-100 shadow-xl shadow-slate-100/40 rounded-2xl p-8"
      >
        <div class="flex flex-col items-center text-center mb-8">
          <div
            class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-teal-100 mb-4"
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
            Update Password
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            Define your new hardware cryptographic safety keys below.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <InputLabel
              for="email"
              value="Account Email Address"
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
            />
            <InputError class="mt-2 text-xs text-red-500" :message="form.errors.email" />
          </div>

          <div>
            <InputLabel
              for="password"
              value="New Security Key"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="password"
              v-model="form.password"
              type="password"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
              required
              autocomplete="new-password"
              placeholder="••••••••••••"
            />
            <InputError
              class="mt-2 text-xs text-red-500"
              :message="form.errors.password"
            />
          </div>

          <div>
            <InputLabel
              for="password_confirmation"
              value="Confirm Security Key"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
              required
              autocomplete="new-password"
              placeholder="••••••••••••"
            />
            <InputError
              class="mt-2 text-xs text-red-500"
              :message="form.errors.password_confirmation"
            />
          </div>

          <div class="pt-2">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="w-full justify-center !rounded-xl shadow-lg shadow-indigo-100 !py-3 bg-indigo-600 hover:bg-indigo-700 text-sm font-bold tracking-wide transition duration-200"
            >
              Reset Password & Lock
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
