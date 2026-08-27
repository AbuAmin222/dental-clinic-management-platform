<template>
  <AppLayout title="Staff Salary Payments">
    <template #header>
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
      >
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
            Staff Salary Payments
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Record and manage the full payroll lifecycle for every staff member.
          </p>
        </div>
        <button
          v-if="can('salary_payments.record')"
          @click="openCreateModal"
          class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200"
        >
          <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
          Record Salary Payment
        </button>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto space-y-4">
        <div
          class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
        >
          <table class="w-full text-left border-collapse hidden sm:table">
            <thead>
              <tr
                class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
              >
                <th class="py-4 px-6">Employee</th>
                <th class="py-4 px-6">Period</th>
                <th class="py-4 px-6">Net Amount</th>
                <th class="py-4 px-6">Status</th>
                <th class="py-4 px-6">Processed By</th>
                <th class="py-4 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
              <tr
                v-for="payment in payments.data"
                :key="payment.id"
                class="hover:bg-gray-50/50 transition-colors"
              >
                <td class="py-4 px-6 font-semibold text-gray-900">
                  {{ payment.user?.first_name }} {{ payment.user?.last_name }}
                </td>
                <td class="py-4 px-6 text-gray-600">
                  {{ payment.pay_period_start }} → {{ payment.pay_period_end }}
                </td>
                <td class="py-4 px-6 font-bold text-gray-900">
                  {{ formatCurrency(payment.amount, "ILS") }}
                </td>
                <td class="py-4 px-6">
                  <span
                    class="inline-flex px-3 py-1 rounded-full text-xs font-bold"
                    :class="statusStyles[payment.status] ?? 'bg-gray-100 text-gray-600'"
                  >
                    {{ statusLabels[payment.status] ?? payment.status }}
                  </span>
                </td>
                <td class="py-4 px-6 text-gray-500 text-xs">
                  {{ payment.processed_by?.user?.first_name ?? "—" }}
                </td>
                <td class="py-4 px-6 text-right">
                  <div class="flex justify-end gap-2 flex-wrap">
                    <button
                      v-if="
                        payment.status === 'pending' && can('salary_payments.approve')
                      "
                      @click="approve(payment)"
                      class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100"
                    >
                      Approve
                    </button>
                    <button
                      v-if="
                        (payment.status === 'pending' || payment.status === 'approved') &&
                        can('salary_payments.hold')
                      "
                      @click="hold(payment)"
                      class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100"
                    >
                      Hold
                    </button>
                    <button
                      v-if="
                        (payment.status === 'approved' || payment.status === 'held') &&
                        can('salary_payments.mark_paid')
                      "
                      @click="markPaid(payment)"
                      class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100"
                    >
                      Mark Paid
                    </button>
                    <button
                      v-if="payment.status === 'held' && can('salary_payments.reject')"
                      @click="reject(payment)"
                      class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100"
                    >
                      Reject
                    </button>
                    <button
                      v-if="
                        ['pending', 'approved', 'held'].includes(payment.status) &&
                        can('salary_payments.cancel')
                      "
                      @click="cancel(payment)"
                      class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200"
                    >
                      Cancel
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="payments.data.length === 0">
                <td colspan="6" class="text-center py-16 text-gray-400 font-medium">
                  No salary payments have been recorded yet.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile stacked cards -->
          <div class="sm:hidden divide-y divide-gray-50">
            <div
              v-if="payments.data.length === 0"
              class="py-16 text-center text-gray-400 font-medium px-6"
            >
              No salary payments have been recorded yet.
            </div>
            <div v-for="payment in payments.data" :key="payment.id" class="p-5 space-y-2">
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-semibold text-gray-900">
                    {{ payment.user?.first_name }} {{ payment.user?.last_name }}
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ payment.pay_period_start }} → {{ payment.pay_period_end }}
                  </p>
                </div>
                <span class="font-bold text-gray-900">{{
                  formatCurrency(payment.amount, "ILS")
                }}</span>
              </div>
              <span
                class="inline-flex px-3 py-1 rounded-full text-xs font-bold"
                :class="statusStyles[payment.status] ?? 'bg-gray-100 text-gray-600'"
              >
                {{ statusLabels[payment.status] ?? payment.status }}
              </span>
              <div class="flex flex-wrap gap-2 pt-2">
                <button
                  v-if="payment.status === 'pending' && can('salary_payments.approve')"
                  @click="approve(payment)"
                  class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700"
                >
                  Approve
                </button>
                <button
                  v-if="
                    (payment.status === 'pending' || payment.status === 'approved') &&
                    can('salary_payments.hold')
                  "
                  @click="hold(payment)"
                  class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700"
                >
                  Hold
                </button>
                <button
                  v-if="
                    (payment.status === 'approved' || payment.status === 'held') &&
                    can('salary_payments.mark_paid')
                  "
                  @click="markPaid(payment)"
                  class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700"
                >
                  Mark Paid
                </button>
                <button
                  v-if="payment.status === 'held' && can('salary_payments.reject')"
                  @click="reject(payment)"
                  class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700"
                >
                  Reject
                </button>
                <button
                  v-if="
                    ['pending', 'approved', 'held'].includes(payment.status) &&
                    can('salary_payments.cancel')
                  "
                  @click="cancel(payment)"
                  class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <Pagination :links="payments.links" />
      </div>
    </div>

    <!-- Create Modal -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-gray-100 p-6 relative max-h-[90vh] overflow-y-auto"
      >
        <h3 class="text-xl font-bold text-gray-900 mb-2">Record Salary Payment</h3>
        <p class="text-xs text-amber-600 bg-amber-50 rounded-xl p-3 mb-4 leading-relaxed">
          Financial\SalaryPaymentController::index() does not currently pass a staff/user
          list prop to this page, so the employee is selected by numeric User ID below
          instead of a name dropdown. Ask a developer to add that prop for a proper
          picker.
        </p>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <InputLabel for="salary-user-id" value="Employee User ID" />
            <TextInput
              id="salary-user-id"
              v-model="form.user_id"
              type="number"
              min="1"
              class="mt-1 block w-full rounded-xl"
              required
            />
            <InputError :message="form.errors.user_id" class="mt-1" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <InputLabel for="salary-period-start" value="Period Start" />
              <TextInput
                id="salary-period-start"
                v-model="form.pay_period_start"
                type="date"
                class="mt-1 block w-full rounded-xl"
                required
              />
              <InputError :message="form.errors.pay_period_start" class="mt-1" />
            </div>
            <div>
              <InputLabel for="salary-period-end" value="Period End" />
              <TextInput
                id="salary-period-end"
                v-model="form.pay_period_end"
                type="date"
                class="mt-1 block w-full rounded-xl"
                required
              />
              <InputError :message="form.errors.pay_period_end" class="mt-1" />
            </div>
          </div>

          <div>
            <InputLabel for="salary-base-amount" value="Base Amount (ILS, optional)" />
            <TextInput
              id="salary-base-amount"
              v-model="form.base_amount"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-xl"
            />
            <InputError :message="form.errors.base_amount" class="mt-1" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <InputLabel for="salary-deduction" value="Deduction (ILS)" />
              <TextInput
                id="salary-deduction"
                v-model="form.deduction_amount"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full rounded-xl"
              />
              <InputError :message="form.errors.deduction_amount" class="mt-1" />
            </div>
            <div>
              <InputLabel for="salary-bonus" value="Bonus (ILS)" />
              <TextInput
                id="salary-bonus"
                v-model="form.bonus_amount"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full rounded-xl"
              />
              <InputError :message="form.errors.bonus_amount" class="mt-1" />
            </div>
          </div>

          <div
            class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 flex justify-between items-center"
          >
            <span class="text-sm font-semibold text-indigo-800"
              >Net Amount (preview):</span
            >
            <span class="text-xl font-black text-indigo-700">{{
              formatCurrency(previewNet, "ILS")
            }}</span>
          </div>

          <div>
            <InputLabel for="salary-notes" value="Notes (optional)" />
            <textarea
              id="salary-notes"
              v-model="form.notes"
              rows="2"
              class="mt-1 block w-full rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-indigo-500 focus:bg-white transition-all p-3"
            />
            <InputError :message="form.errors.notes" class="mt-1" />
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
              {{ form.processing ? "Saving..." : "Record Payment" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";
import { useAbilities } from "@/Composables/UI/useAbilities";
import { formatCurrency } from "@/Utils";

defineProps({
  payments: Object,
});

const { confirmAction, toast } = useNotifications();
const { can } = useAbilities();

const statusLabels = {
  pending: "Pending",
  approved: "Approved",
  held: "On Hold",
  paid: "Paid",
  rejected: "Rejected",
  cancelled: "Cancelled",
};

const statusStyles = {
  pending: "bg-amber-100 text-amber-700",
  approved: "bg-blue-100 text-blue-700",
  held: "bg-orange-100 text-orange-700",
  paid: "bg-emerald-100 text-emerald-700",
  rejected: "bg-red-100 text-red-700",
  cancelled: "bg-red-100 text-red-700",
};

const isModalOpen = ref(false);

const form = useForm({
  user_id: "",
  base_amount: "",
  deduction_amount: "",
  bonus_amount: "",
  pay_period_start: "",
  pay_period_end: "",
  notes: "",
});

const previewNet = computed(() => {
  const base = parseFloat(form.base_amount) || 0;
  const deduction = parseFloat(form.deduction_amount) || 0;
  const bonus = parseFloat(form.bonus_amount) || 0;
  return Math.max(0, base - deduction + bonus);
});

const openCreateModal = () => {
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const submitForm = () => {
  form.post(route("financial.salaryPayments.store"), {
    onSuccess: () => {
      closeModal();
      toast("Salary payment recorded.", "success");
    },
  });
};

const approve = (payment) => {
  confirmAction(
    () =>
      router.patch(
        route("financial.salaryPayments.approve", payment.id),
        {},
        {
          onSuccess: () => toast("Salary payment approved.", "success"),
        }
      ),
    "Approve this payment?",
    "The payment will move to Approved status."
  );
};

const markPaid = (payment) => {
  confirmAction(
    () =>
      router.patch(
        route("financial.salaryPayments.markPaid", payment.id),
        {},
        {
          onSuccess: () => toast("Salary payment marked as paid.", "success"),
        }
      ),
    "Mark as paid?",
    "This confirms the funds have actually been disbursed."
  );
};

const promptReason = (title) => {
  // Lightweight reason capture using the browser prompt, since hold/cancel/reject
  // require a required `reason` string and no dedicated reason-input component
  // exists in resources/js/Components.
  return window.prompt(title, "");
};

const hold = (payment) => {
  const reason = promptReason("Reason for placing this payment on hold:");
  if (!reason) return;
  router.patch(
    route("financial.salaryPayments.hold", payment.id),
    { reason },
    {
      onSuccess: () => toast("Salary payment placed on hold.", "success"),
    }
  );
};

const cancel = (payment) => {
  const reason = promptReason("Reason for cancelling this payment:");
  if (!reason) return;
  confirmAction(
    () =>
      router.patch(
        route("financial.salaryPayments.cancel", payment.id),
        { reason },
        {
          onSuccess: () => toast("Salary payment cancelled.", "success"),
        }
      ),
    "Cancel this payment?",
    "This cannot be undone."
  );
};

const reject = (payment) => {
  const reason = promptReason("Reason for rejecting this payment:");
  if (!reason) return;
  confirmAction(
    () =>
      router.patch(
        route("financial.salaryPayments.reject", payment.id),
        { reason },
        {
          onSuccess: () => toast("Salary payment rejected.", "success"),
        }
      ),
    "Reject this payment?",
    "This cannot be undone."
  );
};
</script>
