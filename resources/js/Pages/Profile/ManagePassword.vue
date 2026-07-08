<script setup>
import SettingsLayout from "@/Components/SettingsLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
</script>

<template>
  <AppLayout title="Security Settings">
    <SettingsLayout>
      <UpdatePasswordForm />
    </SettingsLayout>
  </AppLayout>
</template>
