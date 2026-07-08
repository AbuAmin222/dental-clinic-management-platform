<script setup>
import { nextTick, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const recovery = ref(false);

const form = useForm({
  code: "",
  recovery_code: "",
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
  recovery.value ^= true;
  await nextTick();

  if (recovery.value) {
    recoveryCodeInput.value.focus();
    form.code = "";
  } else {
    codeInput.value.focus();
    form.recovery_code = "";
  }
};

const submit = () => {
  form.post(route("two-factor.login"));
};
</script>

<template>
  <Head title="Two-factor Confirmation" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-center items-center p-6 selection:bg-indigo-500 selection:text-white"
  >
    <div class="w-full max-w-md">
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
                d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75A2.25 2.25 0 0 0 15.75 1.5H13.5m-3 0V3h3V1.5m-3 0h3m-6 15h10.5M8.25 19.5h7.5"
              />
            </svg>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
            2FA Gatekeeper
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            <template v-if="!recovery"
              >Confirm credentials utilizing verification parameters generated via
              application token sync.</template
            >
            <template v-else
              >Provide one of your emergency recovery matrix arrays to bypass device
              configuration filters.</template
            >
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div v-if="!recovery">
            <InputLabel
              for="code"
              value="Synchronized Authenticator Code"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="code"
              ref="codeInput"
              v-model="form.code"
              type="text"
              inputmode="numeric"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800 tracking-[0.5em] text-center font-mono"
              autofocus
              autocomplete="one-time-code"
              placeholder="000000"
            />
            <InputError class="mt-2 text-xs text-red-500" :message="form.errors.code" />
          </div>

          <div v-else>
            <InputLabel
              for="recovery_code"
              value="Emergency Matrix Recovery Code"
              class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
            />
            <TextInput
              id="recovery_code"
              ref="recoveryCodeInput"
              v-model="form.recovery_code"
              type="text"
              class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800 font-mono"
              autocomplete="one-time-code"
              placeholder="abcde-12345"
            />
            <InputError
              class="mt-2 text-xs text-red-500"
              :message="form.errors.recovery_code"
            />
          </div>

          <div class="flex items-center justify-center pt-2">
            <button
              type="button"
              class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline cursor-pointer transition"
              @click.prevent="toggleRecovery"
            >
              <template v-if="!recovery">Use backup hardware recovery matrix</template>
              <template v-else>Use application synchronized token</template>
            </button>
          </div>

          <div>
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="w-full justify-center !rounded-xl shadow-lg shadow-indigo-100 !py-3 bg-indigo-600 hover:bg-indigo-700 text-sm font-bold tracking-wide transition duration-200"
            >
              Validate Session Credentials
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
