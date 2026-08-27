<template>
  <Head title="Complete Your Profile" />

  <div
    class="min-h-screen bg-gray-50 flex flex-col sm:justify-center items-center pt-10 sm:pt-0 px-4"
  >
    <div class="w-full sm:max-w-lg">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
          Complete Your Financial Officer Profile
        </h1>
        <p class="text-sm text-gray-500 mt-2">
          Just a few details before you can access the Financial workspace. Your base
          salary is set exclusively by Admin and is not collected here.
        </p>
      </div>

      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <InputLabel for="onboarding-employee-number" value="Employee Number" />
            <TextInput
              id="onboarding-employee-number"
              v-model="form.employee_number"
              type="text"
              class="mt-1 block w-full rounded-xl"
              placeholder="e.g. FIN-0142"
              required
            />
            <InputError :message="form.errors.employee_number" class="mt-1" />
          </div>

          <div>
            <InputLabel for="onboarding-hiring-date" value="Hiring Date" />
            <TextInput
              id="onboarding-hiring-date"
              v-model="form.hiring_date"
              type="date"
              class="mt-1 block w-full rounded-xl"
              required
            />
            <InputError :message="form.errors.hiring_date" class="mt-1" />
          </div>

          <div>
            <InputLabel for="onboarding-years-experience" value="Years of Experience" />
            <TextInput
              id="onboarding-years-experience"
              v-model="form.years_experience"
              type="number"
              min="0"
              max="60"
              class="mt-1 block w-full rounded-xl"
              required
            />
            <InputError :message="form.errors.years_experience" class="mt-1" />
          </div>

          <div>
            <InputLabel
              for="onboarding-specialization"
              value="Specialization (optional)"
            />
            <TextInput
              id="onboarding-specialization"
              v-model="form.specialization"
              type="text"
              class="mt-1 block w-full rounded-xl"
              placeholder="e.g. Payroll & Insurance Billing"
            />
            <InputError :message="form.errors.specialization" class="mt-1" />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
          >
            {{ form.processing ? "Saving..." : "Complete Profile & Continue" }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
  financial: Object,
});

const form = useForm({
  employee_number: props.financial?.employee_number ?? "",
  hiring_date: props.financial?.hiring_date ?? "",
  years_experience: props.financial?.years_experience ?? "",
  specialization: props.financial?.specialization ?? "",
});

const submit = () => {
  form.post(route("financial.onboarding.store"));
};
</script>
