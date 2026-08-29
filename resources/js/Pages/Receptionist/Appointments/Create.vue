<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import { useNotifications } from "@/Composables";

const props = defineProps({
  doctors: Array,
  patients: Array,
  selected_patient_id: {
    type: Number,
    default: null,
  },
});

const { toast, notify } = useNotifications();

// receptionist.appointments.store expects ONE combined appointment_date
// (it splits it server-side via Carbon::parse into date + start_time),
// unlike the patient-facing form which sends date and time separately.
const form = useForm({
  patient_id: props.selected_patient_id ?? "",
  doctor_id: "",
  appointment_date: "",
  reason_for_visit: "",
});

// datetime-local needs "YYYY-MM-DDTHH:mm" with no seconds/timezone suffix
const minDateTime = new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
  .toISOString()
  .slice(0, 16);

const submitForm = () => {
  if (form.processing) {
    return;
  }

  if (!form.patient_id || !form.doctor_id || !form.appointment_date) {
    notify(
      "Validation Error",
      "Please select a patient, a doctor, and a date & time.",
      "error"
    );
    return;
  }

  form.post(route("receptionist.appointments.store"), {
    onSuccess: () => {
      toast("Appointment booked and confirmed successfully!", "success");
    },
    onError: () => {
      notify(
        "Submission Failed",
        "Please check the form entries and try again.",
        "error"
      );
    },
  });
};
</script>

<template>
  <AppLayout title="Book Appointment">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Book Appointment for Patient
        </h2>
        <Link
          :href="route('receptionist.appointments.index')"
          class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
          Back to Schedule
        </Link>
      </div>
    </template>

    <div class="py-12 bg-gray-50" dir="ltr">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div
          class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-8"
        >
          <div class="mb-8 border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-900">Appointment Ledger Setup</h3>
            <p class="text-sm text-gray-500 mt-1">
              Select the patient, the treating doctor, and the session date & time.
            </p>
          </div>

          <form @submit.prevent="submitForm" class="space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >1. Select Patient *</label
              >
              <select
                v-model="form.patient_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
                :class="{ 'border-rose-400': form.errors.patient_id }"
              >
                <option value="" disabled>-- Choose a Patient --</option>
                <option v-for="p in patients" :key="p.id" :value="p.id">
                  {{ p.name }}
                </option>
              </select>
              <p
                v-if="form.errors.patient_id"
                class="text-xs text-rose-600 mt-1.5 font-medium"
              >
                {{ form.errors.patient_id }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >2. Select Medical Specialist *</label
              >
              <select
                v-model="form.doctor_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
                :class="{ 'border-rose-400': form.errors.doctor_id }"
              >
                <option value="" disabled>-- Choose a Doctor --</option>
                <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                  {{ doc.name }} {{ doc.spec }}
                </option>
              </select>
              <p
                v-if="form.errors.doctor_id"
                class="text-xs text-rose-600 mt-1.5 font-medium"
              >
                {{ form.errors.doctor_id }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >3. Session Date & Time *</label
              >
              <input
                type="datetime-local"
                v-model="form.appointment_date"
                :min="minDateTime"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
                :class="{ 'border-rose-400': form.errors.appointment_date }"
              />
              <p
                v-if="form.errors.appointment_date"
                class="text-xs text-rose-600 mt-1.5 font-medium"
              >
                {{ form.errors.appointment_date }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >4. Reason for Visit</label
              >
              <textarea
                v-model="form.reason_for_visit"
                rows="3"
                placeholder="Optional - reason for this visit..."
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
              ></textarea>
              <p
                v-if="form.errors.reason_for_visit"
                class="text-xs text-rose-600 mt-1.5 font-medium"
              >
                {{ form.errors.reason_for_visit }}
              </p>
            </div>

            <div
              class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3"
            >
              <Link
                :href="route('receptionist.appointments.index')"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
              >
                {{ form.processing ? "Booking..." : "Confirm Appointment" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
