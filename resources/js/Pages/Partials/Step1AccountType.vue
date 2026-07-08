<script setup>
import InputError from "@/Components/InputError.vue";

defineProps({
  form: Object,
});
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">
      Choose Your Account Type
    </h3>

    <div class="grid grid-cols-3 gap-4 sm:grid-cols-1">
      <label
        :class="{
          'border-indigo-600 ring-2 ring-indigo-600': form.role === 'patient',
          'border-gray-200': form.role !== 'patient',
        }"
        class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-300 transition"
      >
        <input
          type="radio"
          v-model="form.role"
          value="patient"
          class="sr-only"
          selected
        />
        <span class="flex flex-1">
          <span class="flex flex-col">
            <span class="block text-sm font-medium text-gray-900">Patient</span>
            <span class="mt-1 flex items-center text-sm text-gray-500"
              >Book appointments and track your health.</span
            >
          </span>
        </span>
        <svg
          v-if="form.role === 'patient'"
          class="h-5 w-5 text-indigo-600"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
      </label>

      <label
        :class="{
          'border-indigo-600 ring-2 ring-indigo-600': form.role === 'doctor',
          'border-gray-200': form.role !== 'doctor',
        }"
        class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-300 transition"
      >
        <input type="radio" v-model="form.role" value="doctor" class="sr-only" />
        <span class="flex flex-1">
          <span class="flex flex-col">
            <span class="block text-sm font-medium text-gray-900">Doctor</span>
            <span class="mt-1 flex items-center text-sm text-gray-500"
              >Manage your schedule and patient records.</span
            >
          </span>
        </span>
        <svg
          v-if="form.role === 'doctor'"
          class="h-5 w-5 text-indigo-600"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
      </label>

      <label
        :class="{
          'border-indigo-600 ring-2 ring-indigo-600': form.role === 'receptionist',
          'border-gray-200': form.role !== 'receptionist',
        }"
        class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-indigo-300 transition"
      >
        <input type="radio" v-model="form.role" value="receptionist" class="sr-only" />
        <span class="flex flex-1">
          <span class="flex flex-col">
            <span class="block text-sm font-medium text-gray-900">Receptionist</span>
            <span class="mt-1 flex items-center text-sm text-gray-500">
              Handle appointments, patient check-ins, and clinic logistics.
            </span>
          </span>
        </span>
        <svg
          v-if="form.role === 'receptionist'"
          class="h-5 w-5 text-indigo-600"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
      </label>
    </div>

    <InputError class="mt-2" :message="form.errors.role" />
  </div>
</template>
