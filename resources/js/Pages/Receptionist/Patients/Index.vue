<template>
  <AppLayout title="Insert new patient">
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Patients Management
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            View, search, and manage clinic patients records.
          </p>
        </div>
        <div>
          <div style="margin-bottom: 10px">
            <Link
              :href="route('receptionist.patients.create')"
              class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200"
            >
              <svg
                class="w-8 h-5 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              Add New Patient
            </Link>
          </div>
        </div>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-7xl mx-auto">
        <div
          v-if="$page.props.flash?.success"
          class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl flex items-center justify-between shadow-sm"
        >
          <div class="flex items-center">
            <svg
              class="w-5 h-5 text-emerald-500 mr-3"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span class="text-sm font-medium text-emerald-800">{{
              $page.props.flash.success
            }}</span>
          </div>
        </div>

        <div
          class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex items-center"
        >
          <div class="relative w-full max-w-md">
            <span
              class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
            >
              <svg
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, ID number, or phone..."
              class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 focus:border-indigo-500 focus:bg-white rounded-xl text-sm focus:ring-1 focus:ring-indigo-500 outline-none transition-all duration-200"
            />
          </div>
        </div>

        <div
          class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
        >
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100">
                  <th
                    class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Patient Name
                  </th>
                  <th
                    class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Identity Number
                  </th>
                  <th
                    class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Phone
                  </th>
                  <th
                    class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Blood Group
                  </th>
                  <th
                    class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center"
                  >
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr
                  v-for="patient in patients.data"
                  :key="patient.id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm"
                      >
                        {{ patient.user.first_name[0] }}{{ patient.user.last_name[0] }}
                      </div>
                      <div>
                        <span class="font-semibold text-gray-900 block text-sm">
                          {{ patient.user.first_name }} {{ patient.user.middle_name }}
                          {{ patient.user.last_name }}
                        </span>
                        <span class="text-xs text-gray-400 block">{{
                          patient.user.email
                        }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="p-4 text-sm text-gray-600 font-medium">
                    {{ patient.user.identity_number }}
                  </td>
                  <td class="p-4 text-sm text-gray-600">{{ patient.user.phone }}</td>
                  <td class="p-4">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-red-50 text-red-700"
                    >
                      {{ patient.blood_group }}
                    </span>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center justify-center gap-2">
                      <Link
                        :href="
                          route('receptionist.appointments.create', {
                            patient_id: patient.id,
                          })
                        "
                        class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium text-xs rounded-lg transition-colors"
                      >
                        Book Appointmet
                      </Link>
                      <Link
                        :href="route('receptionist.patients.show', patient.id)"
                        class="px-3 py-1.5 bg-gray-50 text-gray-700 hover:bg-gray-100 font-medium text-xs rounded-lg transition-colors"
                      >
                        Profile
                      </Link>
                    </div>
                  </td>
                </tr>

                <tr v-if="patients.data.length === 0">
                  <td
                    colspan="5"
                    class="p-8 text-center text-sm text-gray-400 font-medium"
                  >
                    No patients records found matching your criteria.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            class="p-4 border-t border-gray-50 flex items-center justify-between bg-gray-50/30"
          >
            <p class="text-xs text-gray-500 font-medium">
              Showing {{ patients.from || 0 }} to {{ patients.to || 0 }} of
              {{ patients.total }} patients
            </p>
            <div class="flex items-center gap-1">
              <template v-for="(link, index) in patients.links" :key="index">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-all duration-150"
                  :class="
                    link.active
                      ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                  "
                />
                <span
                  v-else
                  v-html="link.label"
                  class="px-3 py-1.5 text-xs text-gray-300 border border-gray-100 rounded-lg cursor-not-allowed bg-white"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
  patients: Object,
  filters: Object,
});

const searchQuery = ref(props.filters.search || "");

watch(searchQuery, (value) => {
  router.get(
    route("receptionist.patients.index"),
    { search: value },
    {
      preserveState: true,
      replace: true,
    }
  );
});
</script>
