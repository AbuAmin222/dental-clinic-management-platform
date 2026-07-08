<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
  password: "",
});

const passwordInput = ref(null);

const submit = () => {
  form.post(route("password.confirm"), {
    onFinish: () => {
      form.reset();
      passwordInput.value.focus();
    },
  });
};
</script>

<template>
  <Head title="Secure Area" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-center items-center p-6 selection:bg-indigo-500 selection:text-white"
  >
    <div class="w-full max-w-md">
      <div
        class="bg-white border border-slate-100 shadow-xl shadow-slate-100/40 rounded-2xl p-8"
      >
        <div class="flex flex-col items-center text-center mb-6">
          <div
            class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-100 mb-4"
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
                d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"
              />
            </svg>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
            Confirm Password
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            This is a secure area of the application. Please confirm your password before
            continuing.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <InputLabel
              for="password"
              value="Password Key"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="password"
              ref="passwordInput"
              v-model="form.password"
              type="password"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800"
              required
              autocomplete="current-password"
              autofocus
              placeholder="••••••••••••"
            />
            <InputError
              class="mt-2 text-xs text-red-500"
              :message="form.errors.password"
            />
          </div>

          <div class="pt-2">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="w-full justify-center !rounded-xl shadow-lg shadow-indigo-100 !py-3 bg-indigo-600 hover:bg-indigo-700 text-sm font-bold tracking-wide transition duration-200"
            >
              Confirm Access Key
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
