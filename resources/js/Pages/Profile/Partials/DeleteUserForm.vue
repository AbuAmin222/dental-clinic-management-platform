<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ActionSection from "@/Components/ActionSection.vue";
import DangerButton from "@/Components/DangerButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const form = useForm({ password: "" });

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true;
  setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
  form.delete(route("current-user.destroy"), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
  form.reset();
};
</script>

<template>
  <ActionSection>
    <template #title>
      <span class="text-red-600 font-bold text-lg tracking-tight"
        >Deactivate Clinic Account</span
      >
    </template>
    <template #description>
      <span class="text-slate-500 text-sm leading-relaxed">
        Permanently purge core identity parameters, system data, and historical logs.
      </span>
    </template>

    <template #content>
      <div class="max-w-xl text-sm text-slate-500 leading-relaxed">
        Once your account is deleted, all of its resources and data will be permanently
        deleted. Before deleting your account, please download any data or information
        that you wish to retain.
      </div>

      <div class="mt-5">
        <DangerButton
          @click="confirmUserDeletion"
          class="!rounded-xl !px-5 !py-2.5 shadow-md"
        >
          Deactivate Account Instance
        </DangerButton>
      </div>

      <DialogModal :show="confirmingUserDeletion" @close="closeModal">
        <template #title
          ><span class="font-bold text-red-600"
            >Irreversible Deactivation Request</span
          ></template
        >
        <template #content>
          Are you certain you want to purge your operational identity? Provide your master
          verification password key to acknowledge the processing instructions.
          <div class="mt-4">
            <TextInput
              ref="passwordInput"
              v-model="form.password"
              type="password"
              class="mt-1 block w-3/4 !rounded-xl"
              placeholder="Confirm Password Access Key"
              autocomplete="current-password"
              @keyup.enter="deleteUser"
            />
            <InputError :message="form.errors.password" class="mt-2" />
          </div>
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal" class="!rounded-xl"
            >Cancel</SecondaryButton
          >
          <DangerButton
            class="ms-3 !rounded-xl shadow-sm"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteUser"
          >
            Acknowledge & Delete File
          </DangerButton>
        </template>
      </DialogModal>
    </template>
  </ActionSection>
</template>
