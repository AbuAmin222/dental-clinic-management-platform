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
    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="employee_number"
        value="Employee Number"
        class="text-slate-700 font-semibold mb-1"
      />
      <TextInput
        id="employee_number"
        v-model="form.employee_number"
        type="text"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm"
        placeholder="e.g. 53443"
      />
      <InputError
        :message="form.errors.employee_number"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>

    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="department_id"
        value="Department ID"
        class="text-slate-700 font-semibold mb-1"
      />
      <TextInput
        id="department_id"
        v-model="form.department_id"
        type="number"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm"
        placeholder="e.g. 2"
      />
      <InputError
        :message="form.errors.department_id"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>

    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="hiring_date"
        value="Hiring Date"
        class="text-slate-700 font-semibold mb-1"
      />
      <TextInput
        id="hiring_date"
        v-model="form.hiring_date"
        type="date"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm text-slate-800"
      />
      <InputError
        :message="form.errors.hiring_date"
        class="mt-1.5 text-xs text-red-500"
      />
    </div>
  </div>
</template>
