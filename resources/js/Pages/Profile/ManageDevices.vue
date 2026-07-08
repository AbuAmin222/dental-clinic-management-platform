<script setup>
import SettingsLayout from "@/Components/SettingsLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";

defineProps({
  sessions: Array,
});
</script>

<template>
  <AppLayout title="Active Sessions">
    <SettingsLayout>
      <LogoutOtherBrowserSessionsForm :sessions="sessions" />
    </SettingsLayout>
  </AppLayout>
</template>
