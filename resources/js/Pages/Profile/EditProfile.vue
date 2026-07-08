<script setup>
import SettingsLayout from "@/Components/SettingsLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import UpdateRoleInformationForm from "./Partials/UpdateRoleInformationForm.vue";
</script>

<template>
  <AppLayout title="Profile Settings">
    <SettingsLayout>
      <div class="space-y-6">
        <!-- قسم البيانات الشخصية -->
        <UpdateProfileInformationForm :user="$page.props.auth.user" />

        <hr class="border-slate-100" />

        <!-- قسم بيانات الرول الوظيفي القياسي (الموجود في صورتك) -->
        <UpdateRoleInformationForm :role-data="$page.props.roleData" />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
