<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
  form: Object,
  today: String,
  calculateAge: Function,
  isValidDate: Function,
  isDateOverride: Function,
  patterns: Object,
});
</script>

<template>
  <div class="space-y-4">
    <div class="text-center">
      <h3 class="text-xl font-bold text-gray-800">Personal Information</h3>
      <p class="text-sm text-gray-500 mt-1">Enter your personal data.</p>
    </div>

    <!-- First Name -->
    <div class="m-4">
      <InputLabel for="first_name" value="First Name" />
      <TextInput
        id="first_name"
        v-model="form.first_name"
        type="text"
        class="mt-1 block w-full"
        placeholder="Enter your First Name"
        required
      />

      <InputError class="mt-2" :message="form.errors.first_name" />
    </div>

    <!-- Middle Name -->
    <div class="m-4">
      <InputLabel for="middle_name" value="Middle Name" />
      <TextInput
        id="middle_name"
        v-model="form.middle_name"
        type="text"
        class="mt-1 block w-full"
        placeholder="Enter your Middle Name(Father)"
        required
      />

      <InputError class="mt-2" :message="form.errors.middle_name" />
    </div>

    <!-- Last Name -->
    <div class="m-4">
      <InputLabel for="last_name" value="Last Name" />
      <TextInput
        id="last_name"
        v-model="form.last_name"
        type="text"
        class="mt-1 block w-full"
        placeholder="Enter your Last Name(Family)"
        required
      />

      <InputError class="mt-2" :message="form.errors.last_name" />
    </div>

    <!-- Date of Birth -->
    <div class="m-4">
      <InputLabel for="date_of_birth" value="Date of Birth" />
      <input
        type="date"
        v-model="form.date_of_birth"
        :max="today"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500"
        :class="{
          'border-green-500 ring-green-500': isValidDate(form.date_of_birth),
          'border-red-500 ring-red-500':
            form.date_of_birth &&
            (!isValidDate(form.date_of_birth) || isDateOverride(form.date_of_birth)),
        }"
      />
      <div
        v-if="
          form.date_of_birth &&
          isValidDate(form.date_of_birth) &&
          !isDateOverride(form.date_of_birth)
        "
        class="mt-2 inline-flex items-center px-2 py-1 rounded bg-green-50 text-green-700 text-xs font-bold"
      >
        <svg class="mr-1.5 h-2.5 w-2.5 text-indigo-500" fill="green" viewBox="0 0 8 8">
          <circle cx="4" cy="4" r="3" />
        </svg>

        Age: {{ calculateAge(form.date_of_birth) }} Years
      </div>
      <div
        v-if="form.date_of_birth && isDateOverride(form.date_of_birth)"
        class="mt-2 inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 text-xs font-bold"
      >
        <svg class="mr-1.5 h-2.5 w-2.5 text-indigo-500" fill="red" viewBox="0 0 8 8">
          <circle cx="4" cy="4" r="3" />
        </svg>

        Age: {{ calculateAge(form.date_of_birth) }} Years, This date is override
      </div>

      <div
        v-if="form.date_of_birth && isValidDate(form.date_of_birth) === false"
        class="mt-2 inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 text-xs font-bold"
      >
        <svg class="mr-1.5 h-2.5 w-2.5 text-indigo-500" fill="red" viewBox="0 0 8 8">
          <circle cx="4" cy="4" r="3" />
        </svg>

        Date of birth cannot be in the future.
      </div>

      <InputError class="mt-2" :message="form.errors.date_of_birth" />
    </div>

    <!-- Identity Number -->
    <!-- Identity Number -->
    <div class="m-4">
      <InputLabel
        for="identity_number"
        value="Identity Number"
        class="text-slate-700 font-semibold mb-2 text-xs uppercase tracking-wider"
      />

      <div class="relative mt-1">
        <TextInput
          id="identity_number"
          v-model="form.identity_number"
          type="text"
          maxlength="9"
          @input="form.identity_number = form.identity_number.replace(/\D/g, '')"
          class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 transition shadow-sm text-sm py-2.5 px-3 text-slate-800 font-mono tracking-wide"
          :class="{
            'border-emerald-500 focus:ring-emerald-500':
              form.identity_number && form.identity_number.length === 9,
            'border-red-500 focus:ring-red-500':
              form.identity_number && form.identity_number.length < 9,
          }"
          placeholder="9 Digits"
          required
        />

        <!-- أيقونة الحالة البصرية (تظهر فقط عند اكتمال الـ 9 أرقام) -->
        <div
          class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
        >
          <svg
            v-if="form.identity_number && form.identity_number.length === 9"
            class="h-5 w-5 text-emerald-500"
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
        </div>
      </div>

      <!-- شريط المساعدة السفلي والعداد الديناميكي -->
      <div class="flex justify-between items-center mt-1.5">
        <p
          v-if="form.identity_number && form.identity_number.length < 9"
          class="text-xs font-semibold text-red-500"
        >
          ✕ Identity number must be exactly 9 digits.
        </p>
        <p
          v-else-if="form.identity_number && form.identity_number.length === 9"
          class="text-xs font-semibold text-emerald-600"
        >
          ✓ Valid identity number format.
        </p>
        <p v-else class="text-[11px] font-medium text-slate-400">
          Enter your official 9-digit national identification number.
        </p>

        <!-- العداد الرقمي المساعد -->
        <span
          class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded tracking-wider"
        >
          {{ form.identity_number ? form.identity_number.length : 0 }}/9
        </span>
      </div>

      <InputError class="mt-2" :message="form.errors.identity_number" />
    </div>

    <!-- Gender -->
    <div class="m-4">
      <InputLabel id="gender-group-label" value="Gender" class="mb-2" />
      <div
        class="grid grid-cols-2 gap-4"
        role="radiogroup"
        aria-labelledby="gender-group-label"
      >
        <button
          type="button"
          role="radio"
          :aria-checked="form.gender === 'Male'"
          @click="form.gender = 'Male'"
          :class="{
            'border-blue-500 bg-blue-50 ring-2 ring-blue-200': form.gender === 'Male',
            'border-gray-200 bg-white': form.gender !== 'Male',
          }"
          class="flex flex-col items-center justify-center p-4 border rounded-xl transition-all hover:border-blue-300"
        >
          <svg
            class="w-8 h-8 text-blue-500 mb-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
          <span class="text-sm font-semibold text-gray-700">Male</span>
        </button>

        <button
          type="button"
          role="radio"
          :aria-checked="form.gender === 'Female'"
          @click="form.gender = 'Female'"
          :class="{
            'border-pink-500 bg-pink-50 ring-2 ring-pink-200': form.gender === 'Female',
            'border-gray-200 bg-white': form.gender !== 'Female',
          }"
          class="flex flex-col items-center justify-center p-4 border rounded-xl transition-all hover:border-pink-300"
        >
          <svg
            class="w-8 h-8 text-pink-500 mb-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
          <span class="text-sm font-semibold text-gray-700">Female</span>
        </button>
      </div>
      <InputError class="mt-2" :message="form.errors.gender" />
    </div>
  </div>
</template>
