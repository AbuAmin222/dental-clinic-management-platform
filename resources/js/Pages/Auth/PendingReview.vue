<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";

const page = usePage();
const firstName = page.props.auth.user?.first_name ?? "";
</script>

<template>
  <Head title="Pending Review" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="text-center">
      <h2 class="text-lg font-semibold text-gray-900">Welcome, {{ firstName }}</h2>

      <p class="mt-4 text-sm text-gray-600 leading-relaxed">
        Your account has been created successfully and is now
        <span class="font-medium text-amber-600">pending administrative review</span>. Our
        clinic staff will verify your details shortly. You will receive an email
        notification once your account is approved and ready to use.
      </p>

      <div class="mt-6">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition"
        >
          Log Out
        </Link>
      </div>
    </div>
  </AuthenticationCard>
</template>
