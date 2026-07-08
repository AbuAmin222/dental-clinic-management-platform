<script setup>
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
  form: Object,
  aiStatus: String,
  scanProgress: Number,
  scanMessage: String,
  identityPreview: String,
  profilePreview: String,
  isDragging: Boolean,
  handleFileUpload: Function,
  handleDrop: Function,
  removeFile: Function,
});

const identityInput = ref(null);
const profileInput = ref(null);
</script>

<template>
  <div class="space-y-4">
    <div class="text-center">
      <h3 class="text-xl font-bold text-gray-800">Identity Verification</h3>
      <p class="text-sm text-gray-500 mt-1">
        Please provide clear photos for account verification.
      </p>
    </div>

    <!-- National ID Card Section -->
    <div class="flex flex-col items-center">
      <div class="flex items-center justify-between w-full mb-4">
        <label class="text-sm font-semibold text-gray-700">National ID Card</label>
        <span
          v-if="aiStatus === 'success'"
          class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase tracking-widest"
        >
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd"
            />
          </svg>
          AI Verified
        </span>
      </div>

      <div class="relative w-full">
        <div
          @click="aiStatus !== 'scanning' ? identityInput.click() : null"
          @dragover.prevent="$emit('update:isDragging', true)"
          @dragleave.prevent="$emit('update:isDragging', false)"
          @drop.prevent="handleDrop($event, 'identity_photo')"
          :class="[
            'w-full h-48 border-2 border-dashed rounded-2xl flex items-center justify-center cursor-pointer transition-all duration-300 overflow-hidden relative',
            isDragging
              ? 'border-indigo-500 bg-indigo-50 scale-[1.01]'
              : 'border-gray-300 hover:border-indigo-400 hover:bg-indigo-50',
            identityPreview ? 'border-indigo-500 shadow-inner' : '',
            aiStatus === 'success' ? 'border-green-500 ring-4 ring-green-50' : '',
          ]"
        >
          <div v-if="identityPreview" class="relative w-full h-full bg-gray-900">
            <img :src="identityPreview" class="w-full h-full object-contain opacity-90" />

            <div
              v-if="aiStatus === 'scanning'"
              class="absolute inset-0 bg-indigo-900 bg-opacity-40"
            >
              <div
                class="absolute left-0 w-full h-0.5 bg-cyan-400 shadow-[0_0_15px_3px_rgba(34,211,238,0.8)]"
                :style="{ top: `${scanProgress}%` }"
              ></div>
              <div
                class="absolute inset-0 flex flex-col items-center justify-center text-center p-4"
              >
                <span class="text-cyan-300 text-xs font-mono font-bold">{{
                  scanMessage
                }}</span>
                <span class="text-white font-mono text-sm mt-1">{{ scanProgress }}%</span>
              </div>
            </div>
          </div>

          <div v-else class="text-center pointer-events-none">
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
              />
            </svg>
            <span class="mt-3 block text-sm font-medium text-gray-600"
              >Scan of ID Front</span
            >
            <span class="mt-1 block text-xs text-gray-400">Click or Drag & Drop</span>
          </div>
        </div>

        <button
          v-if="identityPreview && aiStatus !== 'scanning'"
          @click.stop="removeFile('identity_photo')"
          class="absolute -top-3 -right-3 bg-white text-red-500 border p-1.5 rounded-full shadow-lg z-20"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <path d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <input
        type="file"
        ref="identityInput"
        class="hidden"
        @change="handleFileUpload($event, 'identity_photo')"
        accept="image/jpeg, image/png"
      />
      <InputError class="mt-2" :message="form.errors.identity_photo" />
    </div>

    <!-- Profile Image Section -->
    <div class="flex flex-col items-center">
      <label class="text-sm font-semibold text-gray-700 mb-4">Profile Image</label>
      <div class="relative group">
        <div
          @click="profileInput.click()"
          class="w-40 h-40 border-2 border-dashed rounded-full flex items-center justify-center cursor-pointer overflow-hidden border-gray-300 hover:border-indigo-400"
        >
          <img
            v-if="profilePreview"
            :src="profilePreview"
            class="w-full h-full object-cover"
          />
          <div v-else class="text-center p-4">
            <span class="text-xs text-gray-500">Upload Photo</span>
          </div>
        </div>
        <button
          v-if="profilePreview"
          @click.stop="removeFile('profile_photo')"
          class="absolute top-0 right-0 bg-red-500 text-white p-1.5 rounded-full shadow-lg"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <input
        type="file"
        ref="profileInput"
        class="hidden"
        @change="handleFileUpload($event, 'profile_photo')"
        accept="image/*"
      />
      <InputError class="mt-2" :message="form.errors.profile_photo" />
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 p-4 rounded-lg flex items-start space-x-3">
      <p class="text-xs text-blue-700 leading-relaxed">
        Your documents are encrypted and used for verification purposes only. Max 2MB.
      </p>
    </div>
  </div>
</template>
