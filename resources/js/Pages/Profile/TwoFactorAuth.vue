<script setup>
import SettingsLayout from "@/Components/SettingsLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
</script>

<template>
  <AppLayout title="Two-Factor Authentication">
    <SettingsLayout>
      <TwoFactorAuthenticationForm
        :requires-confirmation="$page.props.auth.requiresConfirmation"
      />
    </SettingsLayout>
  </AppLayout>
</template>
