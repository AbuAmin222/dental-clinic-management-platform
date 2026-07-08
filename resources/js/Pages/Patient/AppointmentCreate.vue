<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import { today } from "@/Utils";
import { useNotifications } from "@/Composables";

// Receive available doctors from backend
defineProps({
  doctors: Array,
});

const { toast, notify } = useNotifications();

// Initialize Inertia Form object
const form = useForm({
  doctor_id: "",
  appointment_date: today(), // Default to today's date using your helper
  start_time: "",
  reason_for_visit: "",
});

// Static available operational time slots for clean choice matrix
const timeSlots = [
  "09:00",
  "09:30",
  "10:00",
  "10:30",
  "11:00",
  "11:30",
  "13:00",
  "13:30",
  "14:00",
  "14:30",
  "15:00",
  "15:30",
];

// Form Submission Procedure
const submitForm = () => {
  // Client side validation check before sending
  if (!form.doctor_id || !form.start_time || form.reason_for_visit.length < 10) {
    notify(
      "Validation Error",
      "Please fill all required fields and ensure reason is detailed.",
      "error"
    );
    return;
  }

  form.post(route("patient.appointment.store"), {
    onSuccess: () => {
      toast("Appointment requested successfully! Pending clinic approval.", "success");
    },
    onError: (errors) => {
      if (errors.start_time) {
        // Trigger alert if overlap collision is caught by backend architecture
        notify("Schedule Conflict", errors.start_time, "error");
      } else {
        notify("Submission Failed", "Please check form entries and try again.", "error");
      }
    },
  });
};
</script>

<template>
  <AppLayout title="Book Appointment">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Book a New Medical Session
        </h2>
        <Link
          :href="route('patient.dashboard')"
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
          Back to Dashboard
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
              Select your preferred medical specialist and time block. System will analyze
              overlaps in real-time.
            </p>
          </div>

          <form @submit.prevent="submitForm" class="space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >1. Select Medical Specialist *</label
              >
              <select
                v-model="form.doctor_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
                :class="{
                  'border-rose-400 focus:border-rose-500 focus:ring-rose-500':
                    form.errors.doctor_id,
                }"
              >
                <option value="" disabled>-- Choose a Doctor from the Catalog --</option>
                <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                  {{ doc.name }} ({{ doc.specialization }})
                </option>
              </select>
              <p
                v-if="form.errors.doctor_id"
                class="text-xs text-rose-600 mt-1.5 font-medium"
              >
                {{ form.errors.doctor_id }}
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"
                  >2. Preferred Date *</label
                >
                <input
                  type="date"
                  v-model="form.appointment_date"
                  :min="today()"
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
                  >3. Available Session Time Block *</label
                >
                <select
                  v-model="form.start_time"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
                  :class="{
                    'border-rose-400 focus:border-rose-500 focus:ring-rose-500':
                      form.errors.start_time,
                  }"
                >
                  <option value="" disabled>-- Select Time Window --</option>
                  <option v-for="slot in timeSlots" :key="slot" :value="slot">
                    {{ slot }} (30 Mins Block)
                  </option>
                </select>
                <p
                  v-if="form.errors.start_time"
                  class="text-xs text-rose-600 mt-1.5 font-medium"
                >
                  {{ form.errors.start_time }}
                </p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >4. Medical Reason / Symptoms Description *</label
              >
              <textarea
                v-model="form.reason_for_visit"
                rows="4"
                placeholder="Please explicitly provide details about your dental symptoms, pain levels, or necessary routine check-ups (minimum 10 characters)..."
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition"
                :class="{
                  'border-rose-400 focus:border-rose-500 focus:ring-rose-500':
                    form.errors.reason_for_visit,
                }"
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
                :href="route('patient.dashboard')"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                Secure Appointment Request
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
