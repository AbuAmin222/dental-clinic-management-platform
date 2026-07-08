<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
  status: String,
});

const form = useForm({});

const submit = () => {
  form.post(route("verification.send"));
};

const verificationLinkSent = computed(() => props.status === "verification-link-sent");
</script>

<template>
  <Head title="Email Verification" />

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
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
              />
            </svg>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
            Verify Workspace
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            Before dispatching system metrics, execute activation filtering by selecting
            the cryptographic verification anchor routed to your digital address inbox.
          </p>
        </div>

        <div
          v-if="verificationLinkSent"
          class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-700 shadow-sm"
        >
          A secure verification framework loop has been dispatched back to your profile
          parameters.
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="w-full justify-center !rounded-xl shadow-lg shadow-indigo-100 !py-3 bg-indigo-600 hover:bg-indigo-700 text-sm font-bold tracking-wide transition duration-200"
            >
              Resend Verification Vector
            </PrimaryButton>
          </div>

          <div
            class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs"
          >
            <Link
              :href="route('profile.show')"
              class="font-bold text-slate-400 hover:text-slate-600 transition"
            >
              Edit Operator Profile
            </Link>

            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="font-bold text-red-500 hover:text-red-600 transition underline cursor-pointer bg-transparent border-none p-0"
            >
              Kill Active Session
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
