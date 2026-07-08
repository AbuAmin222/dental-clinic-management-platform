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
    class="grid grid-cols-6 gap-6 bg-slate-50/60 p-6 rounded-2xl border border-slate-100/80"
  >
    <!-- معرّف التخصص الطبي -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel
        for="specialization_id"
        value="Specialization Reference ID"
        class="text-slate-700 font-bold text-xs uppercase tracking-wider mb-1"
      />
      <div class="relative mt-1">
        <TextInput
          id="specialization_id"
          v-model="form.specialization_id"
          type="number"
          min="1"
          class="block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-150/30 transition shadow-sm placeholder-slate-300 text-sm"
          placeholder="Enter numeric ID (e.g. 1)"
        />
      </div>
      <InputError
        :message="form.errors.specialization_id"
        class="mt-1.5 text-xs text-red-500 font-medium"
      />
    </div>

    <!-- رقم الرخصة الطبية المعتمدة -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel
        for="license_number"
        value="Medical License Number"
        class="text-slate-700 font-bold text-xs uppercase tracking-wider mb-1"
      />
      <TextInput
        id="license_number"
        v-model="form.license_number"
        type="text"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-150/30 transition shadow-sm placeholder-slate-300 text-sm font-mono"
        placeholder="Format: LIC-XXXXXX"
      />
      <InputError
        :message="form.errors.license_number"
        class="mt-1.5 text-xs text-red-500 font-medium"
      />
    </div>

    <!-- سنوات الخبرة العملياتية -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel
        for="experience_years"
        value="Years of Experience"
        class="text-slate-700 font-bold text-xs uppercase tracking-wider mb-1"
      />
      <TextInput
        id="experience_years"
        v-model="form.experience_years"
        type="number"
        min="0"
        max="60"
        class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-150/30 transition shadow-sm text-sm"
      />
      <InputError
        :message="form.errors.experience_years"
        class="mt-1.5 text-xs text-red-500 font-medium"
      />
    </div>

    <!-- السيرة المهنية والتعريف الطبي -->
    <div class="col-span-6 sm:col-span-4">
      <InputLabel
        for="bio"
        value="Professional Biography (التعريف الطبي بالدكتور)"
        class="text-slate-700 font-bold text-xs uppercase tracking-wider mb-1"
      />
      <textarea
        id="bio"
        v-model="form.bio"
        rows="3"
        class="mt-1 block w-full border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-150/30 rounded-xl shadow-sm text-sm p-3 transition resize-none outline-none placeholder-slate-300 bg-white"
        placeholder="Provide a comprehensive summary of clinical expertises, operational background, and dental operations specialities..."
      ></textarea>
      <InputError
        :message="form.errors.bio"
        class="mt-1.5 text-xs text-red-500 font-medium"
      />
    </div>
  </div>
</template>
