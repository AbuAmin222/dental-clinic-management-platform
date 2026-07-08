<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const updatePassword = () => {
  form.put(route("user-password.update"), {
    errorBag: "updatePassword",
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset("password", "password_confirmation");
        passwordInput.value.focus();
      }
      if (form.errors.current_password) {
        form.reset("current_password");
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>

<template>
  <FormSection @submitted="updatePassword">
    <template #title>
      <span class="text-slate-900 font-bold text-lg tracking-tight">Update Password</span>
    </template>
    <template #description>
      <span class="text-slate-500 text-sm leading-relaxed">
        Ensure your account is using a long, random password to stay secure.
      </span>
    </template>

    <template #form>
      <div class="col-span-6 md:col-span-4">
        <InputLabel
          for="current_password"
          value="Current Password"
          class="text-slate-700 font-semibold mb-1"
        />
        <TextInput
          id="current_password"
          ref="currentPasswordInput"
          v-model="form.current_password"
          type="password"
          class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500"
        />
        <InputError :message="form.errors.current_password" class="mt-2 text-xs" />
      </div>

      <div class="col-span-6 md:col-span-4">
        <InputLabel
          for="password"
          value="New Password"
          class="text-slate-700 font-semibold mb-1"
        />
        <TextInput
          id="password"
          ref="passwordInput"
          v-model="form.password"
          type="password"
          class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500"
        />
        <InputError :message="form.errors.password" class="mt-2 text-xs" />
      </div>

      <div class="col-span-6 md:col-span-4">
        <InputLabel
          for="password_confirmation"
          value="Confirm Password"
          class="text-slate-700 font-semibold mb-1"
        />
        <TextInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="mt-1 block w-full !rounded-xl border-slate-200 focus:border-indigo-500"
        />
        <InputError :message="form.errors.password_confirmation" class="mt-2 text-xs" />
      </div>
    </template>

    <template #actions>
      <ActionMessage
        :on="form.recentlySuccessful"
        class="me-3 text-emerald-600 font-medium"
      >
        Saved successfully.
      </ActionMessage>
      <PrimaryButton
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        class="!rounded-xl shadow-md !px-5 !py-2.5 bg-indigo-600 hover:bg-indigo-700 transition"
      >
        Save Password
      </PrimaryButton>
    </template>
  </FormSection>
</template>
