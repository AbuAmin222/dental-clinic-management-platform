<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
  form: Object,
  patterns: Object,
  filterNumbers: Function,
  isLicenseValid: Boolean,
  isExperienceValid: Boolean,
  isValidDate: Function,
  isDateOverride: Function,
  calculateAge: Function,
  today: String,
  bloodGroups: Array,
  departments: Array,
  specializations: Array,
});
</script>

<template>
  <div class="space-y-6">
    <!-- Patient Data -->
    <div v-if="form.role === 'patient'">
      <div class="text-center">
        <div class="text-center">
          <h3 class="text-xl font-bold text-gray-800">Medical Information</h3>
          <p class="text-sm text-gray-500 mt-1">Help us provide the best care for you.</p>
        </div>
      </div>

      <!-- Blood Type -->
      <div class="m-4">
        <InputLabel for="blood_group" value="Blood Group" />
        <select
          id="blood_group"
          v-model="form.blood_group"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500"
          required
        >
          <option value="" selected disabled>Select Blood Type</option>
          <option v-for="bloodType in bloodGroups" :key="bloodType" :value="bloodType">
            {{ bloodType }}
          </option>
        </select>

        <InputError class="mt-2" :message="form.errors.blood_group" />
      </div>

      <!-- Allergies -->
      <div class="m-4">
        <InputLabel for="allergies" value="Allergies" />
        <textarea
          id="allergies"
          v-model="form.allergies"
          rows="2"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          placeholder="List any allergies or type 'None'"
        ></textarea>

        <InputError class="mt-2" :message="form.errors.allergies" />
      </div>

      <!-- Chronic Diseases -->
      <div class="m-4">
        <InputLabel for="chronic_diseases" value="Chronic Diseases" />
        <TextInput
          id="chronic_diseases"
          v-model="form.chronic_diseases"
          type="text"
          class="mt-1 block w-full"
          placeholder="Type your Chronic Diseases if you have."
        />
        <InputError class="mt-2" :message="form.errors.chronic_diseases" />
      </div>

      <!-- Emergency Contact Name -->
      <div class="m-4">
        <InputLabel for="emergency_contact_name" value="Emergency Contact Name" />
        <TextInput
          id="emergency_contact_name"
          v-model="form.emergency_contact_name"
          type="text"
          class="mt-1 block w-full"
          placeholder="Type your Emergency Contact Name."
          required
        />

        <InputError class="mt-2" :message="form.errors.emergency_contact_name" />
      </div>

      <!-- Emergency Contact Phone -->
      <div class="m-4">
        <InputLabel for="emergency_contact_phone" value="Emergency Contact Phone" />
        <div class="relative mt-1">
          <TextInput
            id="emergency_contact_phone"
            v-model="form.emergency_contact_phone"
            @input="filterNumbers('phone')"
            maxlength="10"
            class="block w-full pl-12"
            :class="{
              'border-green-500':
                patterns.phone.test(form.emergency_contact_phone) &&
                patterns.onlyNumber.test(form.emergency_contact_phone),
            }"
            placeholder="0590000000"
            required
          />
          <div
            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
          >
            <span class="text-gray-500 sm:text-sm border-r pr-2">+97</span>
          </div>
        </div>
        <p
          v-if="
            form.emergency_contact_phone &&
            !patterns.phone.test(form.emergency_contact_phone)
          "
          class="text-[11px] text-red-500 mt-1"
        >
          Format: 059 or 056 followed by 7 digits.
        </p>
        <p
          v-if="
            form.emergency_contact_phone &&
            !patterns.onlyNumber.test(form.emergency_contact_phone)
          "
          class="text-[11px] text-red-500 mt-1"
        >
          Must bu only number.
        </p>

        <InputError class="mt-2" :message="form.errors.emergency_contact_phone" />
      </div>
    </div>

    <!-- Doctor Data -->
    <div v-if="form.role === 'doctor'">
      <div class="text-center">
        <h3 class="text-xl font-bold text-gray-800">Doctor Data</h3>
        <p class="text-sm text-gray-500 mt-1">Provide your medical credentials.</p>
      </div>

      <!-- Specialization -->
      <div class="m-2">
        <InputLabel for="specialization" value="Specialization" />
        <select
          id="specialization"
          v-model="form.specialization_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        >
          <option value="" selected disabled>Choose Speciality</option>
          <option v-for="spec in specializations" :key="spec.id" :value="spec.id">
            {{ spec.name }}
          </option>
        </select>

        <InputError class="mt-2" :message="form.errors.specialization_id" />
      </div>

      <!-- License Number -->
      <div class="m-4">
        <InputLabel for="license_number" value="Medical License Number" />
        <div class="relative mt-1">
          <TextInput
            id="license_number"
            v-model="form.license_number"
            class="mt-1 block w-full pr-10"
            :class="{
              'border-green-500 focus:ring-green-500': isLicenseValid,
              'border-red-300': form.license_number && !isLicenseValid,
            }"
            placeholder="e.g. MC-123456"
          />

          <div
            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
          >
            <span v-if="form.license_number && !isLicenseValid" class="text-red-500"
              >✖</span
            >
            <span v-if="isLicenseValid" class="text-green-500">✔</span>
          </div>
        </div>
        <p
          v-if="form.license_number && !isLicenseValid"
          class="text-[10px] text-red-500 mt-1"
        >
          Invalid Format. Standard format: XX-000000
        </p>
        <InputError class="mt-2" :message="form.errors.license_number" />
      </div>

      <!-- Experience Years -->
      <div class="m-4">
        <InputLabel for="experience_years" value="Years of Experience" />
        <div class="flex items-center gap-4">
          <TextInput
            id="experience_years"
            v-model="form.experience_years"
            type="number"
            min="0"
            max="60"
            maxlength="2"
            class="block w-full pl-2"
          />
          <span
            v-if="form.experience_years && isExperienceValid"
            class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded"
          >
            {{ form.experience_years > 10 ? "Senior Expert" : "Practitioner" }}
          </span>
          <span
            v-if="form.experience_years && !isExperienceValid"
            class="text-xs font-bold text-white bg-red-600 px-2 py-1 rounded"
          >
            {{ form.experience_years > 60 ? "Override" : "Not Valid" }}
          </span>
        </div>

        <InputError class="mt-2" :message="form.errors.experience_years" />
      </div>

      <!-- Bio -->
      <div class="m-4">
        <InputLabel for="bio" value="Bio" />
        <textarea
          id="bio"
          v-model="form.bio"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 transition-all"
          :class="{ 'border-green-500': form.bio && form.bio.length > 5 }"
          rows="2"
          placeholder="Briefly describe your expertise...."
        ></textarea>

        <InputError class="mt-2" :message="form.errors.bio" />
      </div>
    </div>

    <!-- Reciptionist Data (Department options) -->
    <div v-if="form.role === 'receptionist'">
      <div class="text-center">
        <h3 class="text-xl font-bold text-gray-800">Staff Details</h3>
        <p class="text-sm text-gray-500 mt-1">Internal administration data.</p>
      </div>

      <!-- Department -->
      <div class="m-4">
        <InputLabel for="department" value="Assigned Department" />
        <select
          id="department"
          v-model="form.department_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        >
          <option value="" selected disabled>Select Department</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>

        <InputError class="mt-2" :message="form.errors.department" />
      </div>

      <!-- Employee Number -->
      <div class="m-4">
        <InputLabel for="employee_number" value="Employee ID Number" />
        <TextInput
          id="employee_number"
          v-model="form.employee_number"
          maxlength="10"
          class="block w-full pl-2"
          :class="{
            'border-green-500 focus:ring-green-500': form.employee_number,
          }"
          placeholder="EMP-123456"
        />

        <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">
          Only numeric digits are allowed
        </p>

        <InputError class="mt-2" :message="form.errors.employee_number" />
      </div>

      <!-- Hiring date -->
      <div class="m-4">
        <InputLabel for="hiring_date" value="Hiring date" />
        <input
          type="date"
          v-model="form.hiring_date"
          :max="today"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500"
          :class="{
            'border-green-500 focus:ring-green-500': isValidDate(form.hiring_date),
            'border-red-500 focus:ring-red-500':
              form.hiring_date &&
              (!isValidDate(form.hiring_date) || isDateOverride(form.hiring_date)),
          }"
        />
        <div
          v-if="
            form.hiring_date &&
            isValidDate(form.hiring_date) &&
            !isDateOverride(form.hiring_date)
          "
          class="mt-2 inline-flex items-center px-2 py-1 rounded bg-green-50 text-green-700 text-xs font-bold"
        >
          <svg class="mr-1.5 h-2.5 w-2.5 text-indigo-500" fill="green" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
          </svg>

          Tenure: {{ calculateAge(form.hiring_date) }} Years
        </div>

        <div
          v-if="form.hiring_date && !isValidDate(form.hiring_date)"
          class="mt-2 inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 text-xs font-bold"
        >
          <svg class="mr-1.5 h-2.5 w-2.5 text-indigo-500" fill="red" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
          </svg>
          Hiring date cannot be in the future.
        </div>

        <div
          v-if="form.hiring_date && isDateOverride(form.hiring_date)"
          class="mt-2 inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 text-xs font-bold"
        >
          <svg class="mr-1.5 h-2.5 w-2.5 text-indigo-500" fill="red" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
          </svg>
          Tenure: {{ calculateAge(form.hiring_date) }} Years,| Hiring date very long.
        </div>

        <InputError class="mt-2" :message="form.errors.hiring_date" />
      </div>
    </div>
  </div>
</template>
