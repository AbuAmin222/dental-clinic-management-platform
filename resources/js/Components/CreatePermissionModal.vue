<template>
  <Modal :show="show" max-width="md" @close="$emit('close')">
    <div class="p-6">
      <h2 class="text-lg font-bold text-gray-900">New Permission</h2>
      <p class="text-sm text-gray-500 mt-1">
        Creating a permission only adds it to the catalog — it grants it to nobody
        automatically. Grant it to a role or a specific user afterwards.
      </p>

      <div class="mt-5 space-y-4">
        <div>
          <InputLabel for="perm-name" value="Slug (e.g. invoices.approve)" />
          <TextInput
            id="perm-name"
            v-model="form.name"
            type="text"
            placeholder="invoices.approve"
            class="mt-1 block w-full"
            @keyup.enter="submit"
          />
          <InputError :message="errors.name" class="mt-1" />
        </div>

        <div>
          <InputLabel for="perm-display" value="Display name" />
          <TextInput
            id="perm-display"
            v-model="form.display_name"
            type="text"
            placeholder="Approve invoices"
            class="mt-1 block w-full"
            @keyup.enter="submit"
          />
          <InputError :message="errors.display_name" class="mt-1" />
        </div>

        <div>
          <InputLabel for="perm-group" value="Group" />
          <TextInput
            id="perm-group"
            v-model="form.group"
            type="text"
            list="perm-group-suggestions"
            placeholder="invoices"
            class="mt-1 block w-full"
            @keyup.enter="submit"
          />
          <datalist id="perm-group-suggestions">
            <option v-for="g in groups" :key="g" :value="g" />
          </datalist>
          <InputError :message="errors.group" class="mt-1" />
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button
          type="button"
          class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          type="button"
          :disabled="submitting"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
          @click="submit"
        >
          {{ submitting ? "Creating..." : "Create permission" }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { reactive, ref } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

defineProps({
  show: { type: Boolean, default: false },
  groups: { type: Array, default: () => [] },
});

const emit = defineEmits(["close", "created"]);

const form = reactive({ name: "", display_name: "", group: "" });
const errors = reactive({ name: null, display_name: null, group: null });
const submitting = ref(false);

const resetErrors = () => {
  errors.name = null;
  errors.display_name = null;
  errors.group = null;
};

const submit = () => {
  submitting.value = true;
  resetErrors();

  window.axios
    .post("/api/admin/permissions", { ...form })
    .then((response) => {
      emit("created", response.data.data);
      form.name = "";
      form.display_name = "";
      form.group = "";
    })
    .catch((error) => {
      if (error.response?.status === 422) {
        Object.assign(errors, error.response.data.errors ?? {});
        // Laravel validation errors arrive as arrays of messages per field.
        for (const key of Object.keys(errors)) {
          if (Array.isArray(errors[key])) errors[key] = errors[key][0];
        }
      }
    })
    .finally(() => {
      submitting.value = false;
    });
};
</script>
