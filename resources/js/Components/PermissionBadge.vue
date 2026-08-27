<template>
  <span
    class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full text-xs font-semibold border transition-colors"
    :class="colorClasses"
  >
    <span>{{ displayName }}</span>
    <button
      v-if="removable"
      type="button"
      :disabled="removing"
      class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-black/10 disabled:opacity-40 transition-colors"
      :title="`Revoke ${displayName}`"
      @click="$emit('remove')"
    >
      <svg viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
        <path
          fill-rule="evenodd"
          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>
  </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  name: { type: String, required: true },
  displayName: { type: String, default: null },
  source: { type: String, default: "direct" }, // 'direct' | 'role:xxx'
  removable: { type: Boolean, default: false },
  removing: { type: Boolean, default: false },
});

defineEmits(["remove"]);

const displayName = computed(() => props.displayName ?? props.name);

// Direct grants are indigo (this user specifically has it). Role-inherited grants are
// gray and intentionally NOT removable from this badge — they must be revoked from the
// role itself (Admin/Roles/Permissions.vue), never faked away at the user level, or the
// UI would silently lie about what the role actually grants everyone else who holds it.
const colorClasses = computed(() =>
  props.source === "direct"
    ? "bg-indigo-50 border-indigo-200 text-indigo-700"
    : "bg-gray-50 border-gray-200 text-gray-500"
);
</script>
