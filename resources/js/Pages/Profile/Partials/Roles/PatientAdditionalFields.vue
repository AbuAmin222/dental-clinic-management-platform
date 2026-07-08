<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
  form: Object,
});
</script>

<template>
  <div
    class="grid grid-cols-6 gap-6 bg-slate-50/60 p-5 rounded-2xl border border-slate-100"
  >
    <!-- Blood Group Dropdown Selection -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="blood_group"
        value="Blood Group"
        class="text-slate-700 font-semibold mb-1"
      />
      <select
        id="blood_group"
        v-model="form.blood_group"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm bg-white p-2.5 outline-none"
      >
        <option value="">Select Blood Group</option>
        <option value="A+">A+</option>
        <option value="A-">A-</option>
        <option value="B+">B+</option>
        <option value="B-">B-</option>
        <option value="AB+">AB+</option>
        <option value="AB-">AB-</option>
        <option value="O+">O+</option>
        <option value="O-">O-</option>
      </select>
      <InputError
        :message="form.errors.blood_group"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>

    <!-- Emergency Contact Name -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="emergency_contact_name"
        value="Emergency Contact Name"
        class="text-slate-700 font-semibold mb-1"
      />
      <TextInput
        id="emergency_contact_name"
        v-model="form.emergency_contact_name"
        type="text"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm"
        placeholder="e.g. John Doe"
      />
      <InputError
        :message="form.errors.emergency_contact_name"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>

    <!-- Emergency Contact Phone -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="emergency_contact_phone"
        value="Emergency Contact Phone"
        class="text-slate-700 font-semibold mb-1"
      />
      <TextInput
        id="emergency_contact_phone"
        v-model="form.emergency_contact_phone"
        type="tel"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-sm"
        placeholder="+1 (555) 000-0000"
      />
      <InputError
        :message="form.errors.emergency_contact_phone"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>

    <!-- Known Medical Allergies -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel
        for="allergies"
        value="Allergies & Reactions"
        class="text-slate-700 font-semibold mb-1"
      />
      <textarea
        id="allergies"
        v-model="form.allergies"
        rows="3"
        class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl shadow-sm text-sm p-3 transition resize-none outline-none bg-white"
        placeholder="List any known drug, food, or environmental allergies..."
      ></textarea>
      <InputError :message="form.errors.allergies" class="mt-1.5 text-xs text-red-500" />
    </div>

    <!-- Chronic Diseases History -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel
        for="chronic_diseases"
        value="Chronic Conditions / Diseases"
        class="text-slate-700 font-semibold mb-1"
      />
      <textarea
        id="chronic_diseases"
        v-model="form.chronic_diseases"
        rows="3"
        class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl shadow-sm text-sm p-3 transition resize-none outline-none bg-white"
        placeholder="Specify ongoing chronic medical conditions (e.g. Diabetes, Hypertension)..."
      ></textarea>
      <InputError
        :message="form.errors.chronic_diseases"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>

    <!-- General Medical Progress Notes -->
    <div class="col-span-6">
      <InputLabel
        for="medical_notes"
        value="Additional Clinical & Medical Notes"
        class="text-slate-700 font-semibold mb-1"
      />
      <textarea
        id="medical_notes"
        v-model="form.medical_notes"
        rows="3"
        class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 rounded-xl shadow-sm text-sm p-3 transition resize-none outline-none bg-white"
        placeholder="Enter relevant clinical information, previous surgeries, or specific health parameters..."
      ></textarea>
      <InputError
        :message="form.errors.medical_notes"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>
  </div>
</template>
