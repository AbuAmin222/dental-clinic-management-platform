<script setup>
import { onMounted, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import { useRegisterForm } from "@/Composables";
import axios from "axios";

import Step2PersonalInfo from "@/Pages/Partials/Step2PersonalInfo.vue";
import Step3ContactInfo from "@/Pages/Partials/Step3ContactInfo.vue";
import RoleSpecificFields from "@/Pages/Partials/RoleSpecificFields.vue";

const bloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
const today = new Date().toISOString().split("T")[0];

const {
  form,
  step,
  patterns,
  filterNumbers,
  isValidDate,
  isDateOverride,
  calculateAge,
  submit,
} = useRegisterForm();

const isPassword = false;

onMounted(() => {
  form.role = "patient";
  step.value = 2;
});

const passwordMaker = () => {
  form.password = form.identity_number;
  form.password_confirmation = form.password;
};

// 1. تعريف حالة اسم المستخدم الافتراضية
const usernameStatus = ref({ loading: false, valid: null, message: "" });
let usernameTimeout = null;

// 2. دالة الفحص الذكية المعتمدة على الـ Debounce
const checkUsernameRealTime = (username) => {
  // إلغاء الفحص القديم إذا استمر المستخدم بالكتابة بسرعة
  clearTimeout(usernameTimeout);

  if (username.length < 3) {
    usernameStatus.value = { loading: false, valid: null, message: "" };
    return;
  }

  usernameStatus.value.loading = true;

  // الانتظار لمدة 500 مللي ثانية بعد توقف المستخدم عن الكتابة قبل إرسال الطلب للسيرفر
  usernameTimeout = setTimeout(async () => {
    try {
      const response = await axios.post(route("check-username"), {
        username: username,
      });
      usernameStatus.value = {
        loading: false,
        valid: response.data.valid,
        message: response.data.message,
      };
    } catch (error) {
      usernameStatus.value = {
        loading: false,
        valid: false,
        message: "Error checking username.",
      };
    }
  }, 500);
};

const handleStore = () => {
  passwordMaker();
  submit("receptionist.patients.store", false);
};
</script>

<template>
  <AppLayout title="Insert New Patient">
    <template #header>
      <div class="flex items-center justify-between py-1">
        <div class="space-y-1">
          <div
            class="flex items-center space-x-2 text-xs font-medium text-slate-400 tracking-wider uppercase"
          >
            <span>Reception Desk</span>
            <span class="text-slate-300">/</span>
            <span class="text-indigo-600 font-semibold">Intake Portal</span>
          </div>
          <h2 class="text-2xl font-bold text-slate-950 tracking-tight sm:text-3xl">
            Patient Intake Registration
          </h2>
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="handleStore">
        <template #title>
          <div class="text-slate-900 font-bold text-lg tracking-tight">
            Clinical Records Initiation
          </div>
        </template>

        <template #description>
          <div class="text-sm text-slate-500 leading-relaxed space-y-4 mt-2">
            <p>
              Initialize a secure, permanent electronic health record (EHR) by entering
              authenticated personal, contact, and clinical metrics.
            </p>

            <div
              class="relative overflow-hidden rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50/40 to-slate-50 p-4 shadow-sm"
            >
              <div class="flex items-start space-x-3">
                <svg
                  class="h-5 w-5 text-indigo-600 mt-0.5 flex-shrink-0"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                </svg>
                <div class="space-y-1">
                  <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Identity Verification Required
                  </h5>
                  <p class="text-xs text-slate-500 leading-normal">
                    Cross-reference official state identification documents to confirm the
                    accuracy of both the
                    <span class="font-semibold text-slate-800">Identity Number</span> and
                    <span class="font-semibold text-slate-800">Date of Birth</span> before
                    finalizing execution.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 mb-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div class="flex items-center space-x-2.5">
                <span class="inline-block w-1 h-5 rounded-full bg-indigo-600"></span>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                  Primary Identification
                </h4>
              </div>
              <span
                class="text-xs font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100"
                >Step 1 of 3</span
              >
            </div>
          </div>

          <div
            class="col-span-6 bg-white border border-slate-100 p-5 rounded-2xl shadow-sm shadow-slate-100/40 mb-6"
          >
            <Step2PersonalInfo
              :form="form"
              :patterns="patterns"
              :today="today"
              :calculateAge="calculateAge"
              :isValidDate="isValidDate"
              :isDateOverride="isDateOverride"
            />
          </div>

          <div class="col-span-6 mb-2 pt-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div class="flex items-center space-x-2.5">
                <span class="inline-block w-1 h-5 rounded-full bg-indigo-600"></span>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                  Communications & Demographics
                </h4>
              </div>
              <span
                class="text-xs font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100"
                >Step 2 of 3</span
              >
            </div>
          </div>

          <div
            class="col-span-6 bg-white border border-slate-100 p-5 rounded-2xl shadow-sm shadow-slate-100/40 mb-6"
          >
            <Step3ContactInfo
              :form="form"
              :patterns="patterns"
              :isPassword="isPassword"
              :username-status="usernameStatus"
              :check-username-real-time="checkUsernameRealTime"
            />
          </div>

          <div class="col-span-6 mb-2 pt-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div class="flex items-center space-x-2.5">
                <span class="inline-block w-1 h-5 rounded-full bg-indigo-600"></span>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800">
                  Clinical Specifications
                </h4>
              </div>
              <span
                class="text-xs font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100"
                >Step 3 of 3</span
              >
            </div>
          </div>

          <div
            class="col-span-6 bg-white border border-slate-100 p-5 rounded-2xl shadow-sm shadow-slate-100/40"
          >
            <RoleSpecificFields
              :form="form"
              :patterns="patterns"
              :filterNumbers="filterNumbers"
              :bloodGroups="bloodGroups"
            />
          </div>
        </template>

        <template #actions>
          <ActionMessage
            :on="form.recentlySuccessful"
            class="me-4 text-emerald-600 font-semibold flex items-center text-sm"
          >
            <svg
              class="w-5 h-5 me-2 text-emerald-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
            Record successfully committed.
          </ActionMessage>

          <PrimaryButton
            :class="{ 'opacity-40 pointer-events-none': form.processing }"
            :disabled="form.processing"
            class="shadow-md shadow-indigo-100 hover:shadow-lg transition-all duration-200 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold tracking-wide rounded-xl text-white"
          >
            <span v-if="form.processing" class="flex items-center space-x-2">
              <span class="animate-pulse">Processing Record...</span>
            </span>
            <span v-else>Register Profile</span>
          </PrimaryButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
