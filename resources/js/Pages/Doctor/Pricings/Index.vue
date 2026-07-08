<template>
  <AppLayout title="My Medical Services">
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Medical Services & Fees
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Configure and manage your personalized diagnostic and treatment catalog.
          </p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
          Add New Service
        </button>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto">
        <div
          class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm mb-6 flex items-center justify-between"
        >
          <div class="relative w-full max-w-md">
            <span
              class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              placeholder="Search services by name..."
              class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
            />
          </div>
          <span class="text-xs text-gray-400 font-medium"
            >{{ pricings.length }} Total Services Registered</span
          >
        </div>

        <div
          class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
        >
          <table class="w-full text-left border-collapse">
            <thead>
              <tr
                class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
              >
                <th class="py-4 px-6">Service Nomenclature</th>
                <th class="py-4 px-6">Standard Cost</th>
                <th class="py-4 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
              <tr
                v-for="service in pricings"
                :key="service.id"
                class="hover:bg-gray-50/50 transition-colors"
              >
                <td class="py-4 px-6 font-semibold text-gray-900">
                  {{ service.service_name }}
                </td>
                <td class="py-4 px-6 font-medium text-emerald-600">
                  {{ formatCurrency(service.amount, "ILS") }}
                </td>
                <td class="py-4 px-6 text-right space-x-2">
                  <button
                    @click="openEditModal(service)"
                    class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors text-xs px-2.5 py-1.5 rounded-lg hover:bg-indigo-50"
                  >
                    Edit
                  </button>
                  <button
                    @click="handleDelete(service.id)"
                    class="text-red-600 hover:text-red-900 font-medium transition-colors text-xs px-2.5 py-1.5 rounded-lg hover:bg-red-50"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="pricings.length === 0">
                <td colspan="3" class="text-center py-12 text-gray-400 font-medium">
                  No services found. Start detailing your therapeutic offerings.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all animate-fade-in"
    >
      <div
        class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-gray-100 p-6 relative"
      >
        <h3 class="text-xl font-bold text-gray-900 mb-2">
          {{ isEditMode ? "Modify Operational Service" : "Incorporate New Service" }}
        </h3>
        <p class="text-xs text-gray-400 mb-6">
          Ensure the pricing schema correlates effectively with general insurance and
          clinic processing regulations.
        </p>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label
              class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2"
              >Service Name</label
            >
            <input
              v-model="form.service_name"
              type="text"
              required
              placeholder="e.g., Composite Resin Restoration"
              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
            />
            <p
              v-if="form.errors.service_name"
              class="text-xs text-red-500 mt-1 font-medium"
            >
              {{ form.errors.service_name }}
            </p>
          </div>

          <div>
            <label
              class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2"
              >Cost (ILS)</label
            >
            <input
              v-model="form.amount"
              type="number"
              step="0.01"
              required
              placeholder="0.00"
              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all"
            />
            <p v-if="form.errors.amount" class="text-xs text-red-500 mt-1 font-medium">
              {{ form.errors.amount }}
            </p>
          </div>

          <div
            class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50 mt-6"
          >
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
            >
              {{
                form.processing
                  ? "Processing..."
                  : isEditMode
                  ? "Save Changes"
                  : "Create Service"
              }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { formatCurrency, debounce } from "@/Utils";
import { useNotifications } from "@/Composables";

const props = defineProps({
  pricings: Array,
  filters: Object,
});

const { notify, confirmAction, toast } = useNotifications();

// Search Filter Handling with clean debouncing
const searchQuery = ref(props.filters.search || "");
const triggerSearch = debounce((value) => {
  router.get(
    route("doctor.pricings.index"),
    { search: value },
    { preserveState: true, replace: true }
  );
}, 350);

watch(searchQuery, (newValue) => {
  triggerSearch(newValue);
});

// Modal Logic state control matrix
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedServiceId = ref(null);

const form = useForm({
  service_name: "",
  amount: "",
});

const openCreateModal = () => {
  isEditMode.value = false;
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
};

const openEditModal = (service) => {
  isEditMode.value = true;
  selectedServiceId.value = service.id;
  form.service_name = service.service_name;
  form.amount = service.amount;
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

// Form Actions Handling execution pipeline
const submitForm = () => {
  if (isEditMode.value) {
    form.put(route("doctor.pricings.update", selectedServiceId.value), {
      onSuccess: () => {
        closeModal();
        toast("Service scheme upgraded cleanly.", "success");
      },
    });
  } else {
    form.post(route("doctor.pricings.store"), {
      onSuccess: () => {
        closeModal();
        toast("New diagnostic entry compiled.", "success");
      },
    });
  }
};

const handleDelete = (id) => {
  confirmAction(
    // 1. Action Callback must be the first parameter
    () => {
      router.delete(route("doctor.pricings.destroy", id), {
        onSuccess: () => {
          // Matches notify(title, text) signature from useNotifications.js
          notify("Success", "Entity removed successfully.");
        },
      });
    },
    // 2. Title goes second
    "Remove Service?",
    // 3. Description text goes third
    "This diagnostic entity will vanish from billing calculation menus."
  );
};
</script>
