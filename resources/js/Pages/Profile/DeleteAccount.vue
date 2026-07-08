<script setup>
import SettingsLayout from "@/Components/SettingsLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
</script>

<template>
  <AppLayout title="Delete Account">
    <SettingsLayout>
      <DeleteUserForm />
    </SettingsLayout>
  </AppLayout>
</template>
