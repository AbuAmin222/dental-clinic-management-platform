<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { Head, Link } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import { useRegisterForm } from "@/Composables";
import Step1AccountType from "@/Pages/Partials/Step1AccountType.vue";
import Step2PersonalInfo from "@/Pages/Partials/Step2PersonalInfo.vue";
import Step3ContactInfo from "@/Pages/Partials/Step3ContactInfo.vue";
import Step4IdentityVerification from "@/Pages/Partials/Step4IdentityVerification.vue";
import RoleSpecificFields from "@/Pages/Partials/RoleSpecificFields.vue";
import FormButtons from "@/Pages/Partials/FormButtons.vue";
import axios from "axios";
import { ref } from "vue";

const {
  form,

  step,

  patterns,
  filterNumbers,

  isPasswordSecure,
  isPasswordMatched,

  emailStatus,
  checkEmailRealTime,

  isLicenseValid,
  isExperienceValid,

  isValidDate,
  isDateOverride,
  calculateAge,
  today,

  isDragging,
  uploadStatus,
  uploadProgress,
  identityPreview,
  profilePreview,
  handleFileUpload,
  handleDrop,
  removeFile,

  isFinalStep,
  nextStep,
  prevStep,

  submit,
} = useRegisterForm();

const isPassword = true;

defineProps({
  specializations: Array,
  departments: Array,
  bloodGroups: Array,
});

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

const handleRegister = () => {
  submit("register", true);
};
</script>

<template>
  <Head title="Register" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col justify-center items-center p-6 selection:bg-indigo-500 selection:text-white"
  >
    <div class="w-full max-w-2xl">
      <div
        class="bg-white border border-slate-100 shadow-xl shadow-slate-100/40 rounded-2xl p-8 md:p-10"
      >
        <div class="flex flex-col items-center text-center mb-8">
          <div
            class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-100 mb-4"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2.5"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"
              />
            </svg>
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
            Create Operator Account
          </h2>
          <p class="text-xs text-slate-400 font-medium mt-1.5">
            Complete the structured registration phases to enlist into workspace
            environment.
          </p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Account Type -->
          <Step1AccountType v-if="step === 1" :form="form" />

          <!-- Personal Information -->
          <Step2PersonalInfo
            v-if="step === 2"
            :form="form"
            :today="today"
            :calculateAge="calculateAge"
            :isValidDate="isValidDate"
            :isDateOverride="isDateOverride"
            :patterns="patterns"
          />

          <!-- Contact Information -->
          <Step3ContactInfo
            v-if="step === 3"
            :form="form"
            :patterns="patterns"
            :isPasswordSecure="isPasswordSecure"
            :isPasswordMatched="isPasswordMatched"
            :isPassword="isPassword"
            :checkEmailRealTime="checkEmailRealTime"
            :emailStatus="emailStatus"
            :username-status="usernameStatus"
            :check-username-real-time="checkUsernameRealTime"
          />

          <!-- Identity Verification -->
          <Step4IdentityVerification
            v-if="step === 4"
            :form="form"
            :uploadStatus="uploadStatus"
            :uploadProgress="uploadProgress"
            :identityPreview="identityPreview"
            :profilePreview="profilePreview"
            :isDragging="isDragging"
            @update:isDragging="isDragging = $event"
            :handleFileUpload="handleFileUpload"
            :handleDrop="handleDrop"
            :removeFile="removeFile"
          />

          <!-- Role Specific Fields -->
          <RoleSpecificFields
            v-if="step > 4"
            :form="form"
            :patterns="patterns"
            :filterNumbers="filterNumbers"
            :isLicenseValid="isLicenseValid"
            :isExperienceValid="isExperienceValid"
            :isValidDate="isValidDate"
            :isDateOverride="isDateOverride"
            :calculateAge="calculateAge"
            :today="today"
            :bloodGroups="bloodGroups"
            :departments="departments"
            :specializations="specializations"
          />

          <!-- Terms & Agreement Checkbox Wrapper -->
          <div
            v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
            class="mt-6 pt-4 border-t border-slate-50"
          >
            <InputLabel for="terms">
              <div class="flex items-start gap-3">
                <Checkbox
                  id="terms"
                  v-model:checked="form.terms"
                  name="terms"
                  required
                  class="mt-0.5"
                />
                <div class="text-xs font-bold text-slate-500 leading-normal">
                  I agree to the
                  <a
                    target="_blank"
                    :href="route('terms.show')"
                    class="underline text-indigo-600 hover:text-indigo-700 transition"
                    >Terms of Service</a
                  >
                  and
                  <a
                    target="_blank"
                    :href="route('policy.show')"
                    class="underline text-indigo-600 hover:text-indigo-700 transition"
                    >Privacy Policy</a
                  >
                </div>
              </div>
              <InputError
                class="mt-2 text-xs text-red-500"
                :message="form.errors.terms"
              />
            </InputLabel>
          </div>

          <!-- Footer Actions Block -->
          <div
            class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4"
          >
            <Link
              :href="route('login')"
              class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline transition order-2 sm:order-1"
            >
              Already registered? Sign In
            </Link>

            <FormButtons
              :step="step"
              :isFinalStep="isFinalStep"
              :processing="form.processing"
              @next="nextStep"
              @prev="prevStep"
              class="order-1 sm:order-2 w-full sm:w-auto"
            />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
