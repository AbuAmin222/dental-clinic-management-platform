<template>
  <AppLayout title="Local Payment Methods">
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Local Payment Methods
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage the bank, Jawwal Pay, PalPay, and card details patients can pay to.
          </p>
        </div>
        <button
          v-if="can('payment_methods.manage')"
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
          Add Payment Method
        </button>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto">
        <div
          class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
        >
          <table class="w-full text-left border-collapse hidden sm:table">
            <thead>
              <tr
                class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
              >
                <th class="py-4 px-6">Title</th>
                <th class="py-4 px-6">Contact / Reference</th>
                <th class="py-4 px-6">Visible to Patient</th>
                <th class="py-4 px-6">Active</th>
                <th class="py-4 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
              <tr
                v-for="method in methods"
                :key="method.id"
                class="hover:bg-gray-50/50 transition-colors"
              >
                <td class="py-4 px-6 font-semibold text-gray-900">{{ method.title }}</td>
                <td class="py-4 px-6 text-gray-600 space-y-0.5">
                  <p v-if="method.bank_phone_number">📞 {{ method.bank_phone_number }}</p>
                  <p v-if="method.masked_visa_number">
                    💳 {{ method.masked_visa_number }}
                  </p>
                  <p v-if="method.account_number">🏦 {{ method.account_number }}</p>
                  <p v-if="method.iban">IBAN: {{ method.iban }}</p>
                  <p
                    v-if="
                      !method.bank_phone_number &&
                      !method.masked_visa_number &&
                      !method.account_number &&
                      !method.iban
                    "
                    class="text-gray-300"
                  >
                    —
                  </p>
                </td>
                <td class="py-4 px-6">
                  <button
                    @click="toggleField(method, 'is_visible_to_patient')"
                    :disabled="!can('payment_methods.manage')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="
                      method.is_visible_to_patient ? 'bg-emerald-500' : 'bg-gray-200'
                    "
                  >
                    <span
                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                      :class="
                        method.is_visible_to_patient ? 'translate-x-6' : 'translate-x-1'
                      "
                    />
                  </button>
                </td>
                <td class="py-4 px-6">
                  <button
                    @click="toggleField(method, 'is_active')"
                    :disabled="!can('payment_methods.manage')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="method.is_active ? 'bg-emerald-500' : 'bg-gray-200'"
                  >
                    <span
                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                      :class="method.is_active ? 'translate-x-6' : 'translate-x-1'"
                    />
                  </button>
                </td>
                <td class="py-4 px-6 text-right space-x-2">
                  <template v-if="can('payment_methods.manage')">
                    <button
                      @click="openEditModal(method)"
                      class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors text-xs px-2.5 py-1.5 rounded-lg hover:bg-indigo-50"
                    >
                      Edit
                    </button>
                    <button
                      @click="handleDelete(method)"
                      class="text-red-600 hover:text-red-900 font-medium transition-colors text-xs px-2.5 py-1.5 rounded-lg hover:bg-red-50"
                    >
                      Delete
                    </button>
                  </template>
                  <span v-else class="text-xs text-gray-300 italic">No access</span>
                </td>
              </tr>
              <tr v-if="methods.length === 0">
                <td colspan="5" class="text-center py-16 text-gray-400 font-medium">
                  No payment methods added yet. Add one so patients can pay locally.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile stacked cards -->
          <div class="sm:hidden divide-y divide-gray-50">
            <div
              v-if="methods.length === 0"
              class="py-16 text-center text-gray-400 font-medium px-6"
            >
              No payment methods added yet. Add one so patients can pay locally.
            </div>
            <div v-for="method in methods" :key="method.id" class="p-5 space-y-3">
              <div class="flex justify-between items-start">
                <p class="font-semibold text-gray-900">{{ method.title }}</p>
                <div v-if="can('payment_methods.manage')" class="flex gap-2">
                  <button
                    @click="openEditModal(method)"
                    class="text-indigo-600 text-xs font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="handleDelete(method)"
                    class="text-red-600 text-xs font-medium"
                  >
                    Delete
                  </button>
                </div>
              </div>
              <div class="text-xs text-gray-500 space-y-0.5">
                <p v-if="method.bank_phone_number">📞 {{ method.bank_phone_number }}</p>
                <p v-if="method.masked_visa_number">💳 {{ method.masked_visa_number }}</p>
                <p v-if="method.account_number">🏦 {{ method.account_number }}</p>
                <p v-if="method.iban">IBAN: {{ method.iban }}</p>
              </div>
              <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                  <button
                    @click="toggleField(method, 'is_visible_to_patient')"
                    :disabled="!can('payment_methods.manage')"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="
                      method.is_visible_to_patient ? 'bg-emerald-500' : 'bg-gray-200'
                    "
                  >
                    <span
                      class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"
                      :class="
                        method.is_visible_to_patient ? 'translate-x-5' : 'translate-x-1'
                      "
                    />
                  </button>
                  Visible
                </label>
                <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                  <button
                    @click="toggleField(method, 'is_active')"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                    :class="method.is_active ? 'bg-emerald-500' : 'bg-gray-200'"
                  >
                    <span
                      class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"
                      :class="method.is_active ? 'translate-x-5' : 'translate-x-1'"
                    />
                  </button>
                  Active
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all"
    >
      <div
        class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-gray-100 p-6 relative"
      >
        <h3 class="text-xl font-bold text-gray-900 mb-2">
          {{ isEditMode ? "Edit Payment Method" : "Add Payment Method" }}
        </h3>
        <p class="text-xs text-gray-400 mb-6">
          Provide at least one contact field (phone, card, account, IBAN, or QR code).
        </p>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <InputLabel for="payment-method-title" value="Title" />
            <TextInput
              id="payment-method-title"
              v-model="form.title"
              type="text"
              class="mt-1 block w-full rounded-xl"
              placeholder="e.g. Bank of Palestine — Main Branch"
              required
            />
            <InputError :message="form.errors.title" class="mt-1" />
          </div>

          <div>
            <InputLabel
              for="payment-method-phone"
              value="Bank / Jawwal Pay / PalPay Phone"
            />
            <TextInput
              id="payment-method-phone"
              v-model="form.bank_phone_number"
              type="text"
              class="mt-1 block w-full rounded-xl"
              placeholder="059XXXXXXX"
            />
            <InputError :message="form.errors.bank_phone_number" class="mt-1" />
          </div>

          <div>
            <InputLabel for="payment-method-visa" value="Visa Card Number" />
            <TextInput
              id="payment-method-visa"
              v-model="form.visa_card_number"
              type="text"
              class="mt-1 block w-full rounded-xl"
              placeholder="Only used to derive a masked display number"
            />
            <InputError :message="form.errors.visa_card_number" class="mt-1" />
          </div>

          <div>
            <InputLabel for="payment-method-account" value="Account Number" />
            <TextInput
              id="payment-method-account"
              v-model="form.account_number"
              type="text"
              class="mt-1 block w-full rounded-xl"
            />
            <InputError :message="form.errors.account_number" class="mt-1" />
          </div>

          <div>
            <InputLabel for="payment-method-iban" value="IBAN" />
            <TextInput
              id="payment-method-iban"
              v-model="form.iban"
              type="text"
              class="mt-1 block w-full rounded-xl"
            />
            <InputError :message="form.errors.iban" class="mt-1" />
          </div>

          <div class="flex items-center gap-6 pt-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <Checkbox v-model:checked="form.is_visible_to_patient" />
              Visible to patient
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <Checkbox v-model:checked="form.is_active" />
              Active
            </label>
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
                form.processing ? "Saving..." : isEditMode ? "Save Changes" : "Add Method"
              }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Swal from "sweetalert2";
import { useNotifications } from "@/Composables/UI/useNotifications";
import { useAbilities } from "@/Composables/UI/useAbilities";

defineProps({
  methods: Array,
});

const { confirmAction, toast } = useNotifications();
const { can } = useAbilities();

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMethodId = ref(null);

const form = useForm({
  title: "",
  bank_phone_number: "",
  visa_card_number: "",
  account_number: "",
  iban: "",
  is_visible_to_patient: true,
  is_active: true,
});

const openCreateModal = () => {
  isEditMode.value = false;
  form.reset();
  form.is_visible_to_patient = true;
  form.is_active = true;
  form.clearErrors();
  isModalOpen.value = true;
};

const openEditModal = (method) => {
  isEditMode.value = true;
  selectedMethodId.value = method.id;
  form.title = method.title;
  form.bank_phone_number = method.bank_phone_number ?? "";
  // The card number is stored encrypted and never sent back from the server
  // (LocalPaymentMethod::$hidden hides visa_card_number; only masked_visa_number
  // is exposed). Leaving this blank on edit means "keep the existing card on file"
  // unless the officer explicitly types a new one.
  form.visa_card_number = "";
  form.account_number = method.account_number ?? "";
  form.iban = method.iban ?? "";
  form.is_visible_to_patient = !!method.is_visible_to_patient;
  form.is_active = !!method.is_active;
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const submitForm = () => {
  if (isEditMode.value) {
    form.patch(route("financial.paymentMethods.update", selectedMethodId.value), {
      onSuccess: () => {
        closeModal();
        toast("Payment method updated.", "success");
      },
    });
  } else {
    form.post(route("financial.paymentMethods.store"), {
      onSuccess: () => {
        closeModal();
        toast("Payment method added.", "success");
      },
    });
  }
};

const toggleField = (method, field) => {
  router.patch(
    route("financial.paymentMethods.update", method.id),
    { ...method, [field]: !method[field] },
    { preserveScroll: true }
  );
};

const handleDelete = (method) => {
  confirmAction(
    () => {
      router.delete(route("financial.paymentMethods.destroy", method.id), {
        onSuccess: () => toast("Payment method removed.", "success"),
      });
    },
    "Remove this payment method?",
    "Patients will no longer see it as a payment option. This cannot be undone."
  );
};
</script>
