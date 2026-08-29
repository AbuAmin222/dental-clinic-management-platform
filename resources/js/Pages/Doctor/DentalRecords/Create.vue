<script setup>
import { useNotifications, useFileHandle } from "@/Composables";
import { useForm, Head, Link } from "@inertiajs/vue3";

const props = defineProps({
  appointment: Object,
});

const { notify } = useNotifications();

const form = useForm({
  tooth_number: "",
  condition_type: "",
  description: "",
  xray_image: null,
});

const fileLogic = useFileHandle(form, notify);

const {
  isDragging,
  uploadStatus,
  uploadProgress,
  xrayPreview,
  handleFileUpload,
  handleDrop,
  removeFile,
} = fileLogic;

const submit = () => {
  form.post(route("doctor.dentalRecords.store", props.appointment.id), {
    forceFormData: true,
    onSuccess: () => {
      notify("success", "Dental record saved successfully!");
      removeFile("xray_image");
    },
  });
};
</script>

<template>
  <Head title="Patient Dental Examination" />

  <div class="py-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 tracking-tight">
    <div
      class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-6 border-b border-slate-100"
    >
      <div class="space-y-1">
        <div
          class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 tracking-wider uppercase"
        >
          <span>Clinical Desk</span>
          <span class="text-slate-300">/</span>
          <span>Session Entry</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight sm:text-3xl">
          Dental Examination Record
        </h1>
      </div>
      <div class="mt-4 sm:mt-0">
        <Link
          :href="route('doctor.dashboard')"
          class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors duration-150"
        >
          <svg
            class="w-4 h-4 me-1.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
            />
          </svg>
          Return to Dashboard
        </Link>
      </div>
    </div>

    <div
      class="bg-gradient-to-br from-slate-50 to-slate-100/60 border border-slate-200/60 p-5 rounded-2xl shadow-sm mb-8"
    >
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
        <div class="space-y-1">
          <span class="text-xs font-medium uppercase tracking-wider text-slate-400 block"
            >Patient Name</span
          >
          <span class="font-semibold text-slate-800 text-base">
            {{ appointment.patient.user.first_name }}
            {{ appointment.patient.user.last_name }}
          </span>
        </div>
        <div class="space-y-1">
          <span class="text-xs font-medium uppercase tracking-wider text-slate-400 block"
            >Reason for Visit</span
          >
          <span class="text-slate-700 font-medium">{{
            appointment.reason_for_visit
          }}</span>
        </div>
        <div class="space-y-1">
          <span class="text-xs font-medium uppercase tracking-wider text-slate-400 block"
            >Scheduled Window</span
          >
          <span class="inline-flex items-center text-slate-700 font-medium">
            <svg
              class="w-4 h-4 me-1.5 text-slate-400"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ appointment.start_time.substring(0, 5) }}
          </span>
        </div>
      </div>
    </div>

    <form
      @submit.prevent="submit"
      class="bg-white border border-slate-100 p-6 sm:p-8 rounded-2xl shadow-sm shadow-slate-100/40 space-y-8"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-2">
            Tooth Number / Region
            <span class="text-xs font-normal text-slate-400 lowercase italic pl-1"
              >(optional)</span
            >
          </label>
          <input
            v-model="form.tooth_number"
            type="text"
            placeholder="e.g., 14, 26, Upper Right"
            class="w-full border-slate-200 rounded-xl shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-400 py-2.5 transition-colors duration-150"
          />
          <div
            v-if="form.errors.tooth_number"
            class="text-rose-600 text-xs font-medium mt-1.5 flex items-center"
          >
            {{ form.errors.tooth_number }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-2">
            Condition Type <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <select
              v-model="form.condition_type"
              required
              class="w-full border-slate-200 rounded-xl shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 transition-colors duration-150 text-slate-700"
            >
              <option value="">Select Condition</option>
              <option value="Caries">Caries (تسوس)</option>
              <option value="Root Canal">Root Canal (سحب عصب)</option>
              <option value="Extraction">Extraction (خلع سن)</option>
              <option value="Dental Implant">Dental Implant (زراعة)</option>
              <option value="Scaling &amp; Cleaning">
                Scaling & Cleaning (تنظيف وفحص)
              </option>
            </select>
          </div>
          <div
            v-if="form.errors.condition_type"
            class="text-rose-600 text-xs font-medium mt-1.5"
          >
            {{ form.errors.condition_type }}
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-800 mb-2">
          Clinical Notes & Treatment Description <span class="text-rose-500">*</span>
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          required
          placeholder="Document diagnostic findings, materials utilized, and structural treatment protocols here..."
          class="w-full border-slate-200 rounded-xl shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-400 transition-colors duration-150"
        ></textarea>
        <div
          v-if="form.errors.description"
          class="text-rose-600 text-xs font-medium mt-1.5"
        >
          {{ form.errors.description }}
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-800 mb-2">
          Upload X-Ray Radiograph
          <span class="text-xs font-normal text-slate-400 lowercase italic pr-1"
            >(optional)</span
          >
        </label>

        <div
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop($event, 'xray_image')"
          :class="[
            isDragging
              ? 'border-indigo-500 bg-indigo-50/40 scale-[1.005]'
              : 'border-slate-200 bg-white hover:border-slate-300',
          ]"
          class="mt-1 flex flex-col items-center justify-center min-h-[200px] px-6 py-6 border-2 border-dashed rounded-2xl transition-all duration-200 group relative overflow-hidden"
        >
          <div v-if="!xrayPreview" class="space-y-2 text-center pointer-events-none">
            <svg
              class="mx-auto h-10 w-10 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"
              />
            </svg>
            <div class="flex text-sm text-slate-600 justify-center pointer-events-auto">
              <label
                class="relative cursor-pointer bg-transparent rounded-md font-semibold text-indigo-600 hover:text-indigo-700"
              >
                <span>Upload a file</span>
                <input
                  type="file"
                  class="sr-only"
                  @change="handleFileUpload($event, 'xray_image')"
                  accept="image/*"
                />
              </label>
              <p class="pr-1 text-slate-500">No file chosen or drag and drop</p>
            </div>
            <p class="text-xs text-slate-400">PNG, JPG formats up to 4MB</p>
          </div>

          <div
            v-else-if="uploadStatus === 'reading'"
            class="w-full max-w-xs space-y-4 text-center p-4"
          >
            <div class="relative pt-1">
              <div class="flex mb-2 items-center justify-between">
                <div>
                  <span
                    class="text-xs font-bold inline-block py-1 px-2.5 uppercase rounded-full text-indigo-600 bg-indigo-50"
                  >
                    Uploading
                  </span>
                </div>
                <div class="text-left">
                  <span class="text-xs font-bold inline-block text-indigo-600">
                    {{ uploadProgress }}%
                  </span>
                </div>
              </div>
              <div
                class="overflow-hidden h-1.5 mb-4 text-xs flex rounded-full bg-slate-100"
              >
                <div
                  :style="{ width: uploadProgress + '%' }"
                  class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-300 rounded-full"
                ></div>
              </div>
            </div>
            <p class="text-xs text-slate-400 animate-pulse">
              الرجاء الانتظار، يتم رفع الصورة...
            </p>
          </div>

          <div
            v-else-if="uploadStatus === 'done'"
            class="w-full flex flex-col items-center justify-center p-2 relative"
          >
            <div
              class="relative group/preview rounded-xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm max-w-sm"
            >
              <img
                :src="xrayPreview"
                alt="X-Ray Radiograph"
                class="max-h-64 object-contain mx-auto"
              />

              <div
                class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover/preview:opacity-100 transition-opacity duration-150 flex items-center justify-center"
              >
                <button
                  type="button"
                  @click="removeFile('xray_image')"
                  class="bg-white/90 text-rose-600 hover:bg-rose-600 hover:text-white p-2.5 rounded-full shadow-md transition-all duration-150 transform scale-90 group-hover/preview:scale-100"
                  title="Delete Image"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <div
              class="mt-3 inline-flex items-center text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-md px-2.5 py-1"
            >
              <svg
                class="w-3.5 h-3.5 ml-1.5 text-emerald-500"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
              </svg>
              تم الرفع: {{ form.xray_image.name }}
            </div>
          </div>
        </div>
        <div
          v-if="form.errors.xray_image"
          class="text-rose-600 text-xs font-medium mt-1.5"
        >
          {{ form.errors.xray_image }}
        </div>
      </div>

      <div class="flex justify-end pt-5 border-t border-slate-100">
        <button
          type="submit"
          :disabled="form.processing"
          class="px-5 py-2.5 bg-indigo-600 text-white font-semibold text-sm rounded-xl shadow-md shadow-indigo-100 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-40 transition-all duration-150 tracking-wide"
        >
          <span v-if="form.processing" class="flex items-center space-x-2">
            <svg
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            Saving Session Record...
          </span>
          <span v-else>Complete Session & Save</span>
        </button>
      </div>
    </form>
  </div>
</template>
