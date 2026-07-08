<script setup>
import { computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

import PatientAdditionalFields from "./Roles/PatientAdditionalFields.vue";
import DoctorAdditionalFields from "./Roles/DoctorAdditionalFields.vue";
import ReceptionistAdditionalFields from "./Roles/ReceptionistAdditionalFields.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";

const props = defineProps({
  roleData: Object,
});

const page = usePage();
// تحديد الصلاحية الفعلية الحالية للمستخدم المسجل
const userRole = computed(() => page.props.auth.user?.role);
const { toast } = useNotifications();

// دالة تنظيف التواريخ لضمان توافق المتصفحات القياسي (ISO Standard)
const sanitizeDateOnly = (dateTimeString) => {
  if (!dateTimeString) return "";
  return dateTimeString.split(" ")[0].split("T")[0];
};

/**
 * 🎯 هندسة بناء البيانات الديناميكية (Dynamic Payload Isolation)
 * تفصل هذه الدالة الحقول وتمنع اختلاط البيانات المرسلة للخادم بناءً على نوع الحساب
 */
const compileDynamicPayload = (role) => {
  switch (role) {
    case "doctor":
      return {
        specialization_id: props.roleData?.specialization_id || "",
        license_number: props.roleData?.license_number || "",
        experience_years:
          props.roleData?.experience_years !== undefined
            ? parseInt(props.roleData.experience_years)
            : 0,
        bio: props.roleData?.bio || "",
      };

    case "receptionist": // (المعروف بـ ride station في بيئة العمل الميداني)
      return {
        employee_number: props.roleData?.employee_number || "",
        department_id: props.roleData?.department_id || "",
        hiring_date: sanitizeDateOnly(props.roleData?.hiring_date),
      };

    case "patient":
      return {
        blood_group: props.roleData?.blood_group || "",
        emergency_contact_name: props.roleData?.emergency_contact_name || "",
        emergency_contact_phone: props.roleData?.emergency_contact_phone || "",
        allergies: props.roleData?.allergies || "",
        chronic_diseases: props.roleData?.chronic_diseases || "",
        medical_notes: props.roleData?.medical_notes || "",
      };

    default:
      return {};
  }
};

// توليد النموذج النظيف والخالي تماماً من الحقول الزائدة العشوائية
const form = useForm(compileDynamicPayload(userRole.value));

const updateRoleInformation = () => {
  form.put(route("user-profile-role.update"), {
    preserveScroll: true,
    onSuccess: () => {
      toast("Professional role profiles synchronized successfully.", "success");
    },
  });
};
</script>

<template>
  <FormSection @submitted="updateRoleInformation">
    <template #title>
      <span class="text-slate-900 font-bold text-lg tracking-tight"
        >Professional & Role Credentials</span
      >
    </template>
    <template #description>
      <span class="text-slate-500 text-sm leading-relaxed">
        System privileges, medical certifications, work metrics, and active records linked
        to your role profile.
      </span>
    </template>

    <template #form>
      <div
        class="col-span-6 bg-white border border-slate-100 rounded-2xl p-6 shadow-sm ring-1 ring-slate-50"
      >
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
          <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2"
              stroke="currentColor"
              class="w-5 h-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.25 2.25 0 0 1 10.5 2.25h4.5a2.25 2.25 0 0 1 2.25 2.25m-7.5 0c-.394.01-.784.027-1.173.05a2.25 2.25 0 0 0-1.977 2.192V18.75A2.25 2.25 0 0 0 5.25 21h1.123m11.23a48.47 48.47 0 0 0-11.23 0"
              />
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-slate-800 capitalize">
              {{ userRole }} Workspace Parameters
            </h4>
            <p class="text-xs text-slate-400">
              Isolated context environment optimized strictly for your active
              administrative duties.
            </p>
          </div>
        </div>

        <div class="space-y-4">
          <!-- لن يتم رندرة أو حقن المكونات الأخرى غير المطابقة لحالة الحساب الحالية مطلقاً -->
          <PatientAdditionalFields v-if="userRole === 'patient'" :form="form" />
          <DoctorAdditionalFields v-if="userRole === 'doctor'" :form="form" />
          <ReceptionistAdditionalFields v-if="userRole === 'receptionist'" :form="form" />
        </div>
      </div>
    </template>

    <template #actions>
      <PrimaryButton
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        class="!rounded-xl shadow-md !px-5 !py-2.5 bg-indigo-600 hover:bg-indigo-700 transition"
      >
        Save Role Details
      </PrimaryButton>
    </template>
  </FormSection>
</template>
