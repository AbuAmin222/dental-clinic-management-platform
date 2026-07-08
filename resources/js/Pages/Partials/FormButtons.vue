<script setup>
defineProps({
  step: Number,
  isFinalStep: Boolean,
  processing: Boolean,
});

defineEmits(["next", "prev"]);
</script>

<template>
  <div class="flex items-center justify-between mt-8 border-t pt-6">
    <button
      v-if="step > 1"
      type="button"
      @click="$emit('prev')"
      class="text-sm text-gray-600 hover:text-gray-900 font-medium px-4 py-2 transition"
    >
      ← Back
    </button>
    <div v-else></div>

    <button
      v-if="!isFinalStep"
      type="button"
      @click="$emit('next')"
      class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md"
    >
      Continue
    </button>

    <button
      v-else
      type="submit"
      :disabled="processing"
      class="bg-green-600 text-white px-8 py-2 rounded-lg font-bold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center shadow-md"
    >
      <svg
        v-if="processing"
        class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
      {{ processing ? "Processing..." : "Complete Registration ✓" }}
    </button>
  </div>
</template>
