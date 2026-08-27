<template>
  <AppLayout title="Review Invoices">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
          Review Draft Invoices
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Issue reception-submitted invoice requests so patients can be asked to pay.
        </p>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-6xl mx-auto space-y-4">
        <div
          class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
        >
          <!-- Desktop table -->
          <table class="w-full text-left border-collapse hidden sm:table">
            <thead>
              <tr
                class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
              >
                <th class="py-4 px-6">Patient</th>
                <th class="py-4 px-6">Doctor</th>
                <th class="py-4 px-6">Items</th>
                <th class="py-4 px-6">Total</th>
                <th class="py-4 px-6">Due Date</th>
                <th class="py-4 px-6 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
              <tr
                v-for="invoice in invoices.data"
                :key="invoice.id"
                class="hover:bg-gray-50/50 transition-colors"
              >
                <td class="py-4 px-6 font-semibold text-gray-900">
                  {{ invoice.patient?.user?.first_name }}
                  {{ invoice.patient?.user?.last_name }}
                </td>
                <td class="py-4 px-6 text-gray-600">
                  Dr. {{ invoice.doctor?.user?.first_name }}
                  {{ invoice.doctor?.user?.last_name }}
                </td>
                <td class="py-4 px-6 text-gray-600">
                  {{ invoice.items?.length ?? 0 }} item(s)
                </td>
                <td class="py-4 px-6 font-bold text-gray-900">
                  {{ formatCurrency(invoice.total_amount, "ILS") }}
                </td>
                <td class="py-4 px-6 text-gray-600">
                  {{ formatDate(invoice.due_date) }}
                </td>
                <td class="py-4 px-6 text-right">
                  <button
                    @click="issueInvoice(invoice)"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all"
                  >
                    Issue Invoice
                  </button>
                </td>
              </tr>
              <tr v-if="invoices.data.length === 0">
                <td colspan="6" class="text-center py-16 text-gray-400 font-medium">
                  No draft invoices are waiting for review right now.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile stacked cards -->
          <div class="sm:hidden divide-y divide-gray-50">
            <div
              v-if="invoices.data.length === 0"
              class="py-16 text-center text-gray-400 font-medium px-6"
            >
              No draft invoices are waiting for review right now.
            </div>
            <div v-for="invoice in invoices.data" :key="invoice.id" class="p-5 space-y-2">
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-semibold text-gray-900">
                    {{ invoice.patient?.user?.first_name }}
                    {{ invoice.patient?.user?.last_name }}
                  </p>
                  <p class="text-xs text-gray-500">
                    Dr. {{ invoice.doctor?.user?.first_name }}
                    {{ invoice.doctor?.user?.last_name }}
                  </p>
                </div>
                <span class="font-bold text-gray-900">{{
                  formatCurrency(invoice.total_amount, "ILS")
                }}</span>
              </div>
              <div class="flex justify-between items-center text-xs text-gray-500">
                <span
                  >{{ invoice.items?.length ?? 0 }} item(s) · Due
                  {{ formatDate(invoice.due_date) }}</span
                >
              </div>
              <button
                v-if="can('invoices.approve')"
                @click="issueInvoice(invoice)"
                class="w-full mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all"
              >
                Issue Invoice
              </button>
              <p v-else class="text-xs text-gray-400 italic text-center mt-2">
                No permission to issue
              </p>
            </div>
          </div>
        </div>

        <Pagination :links="invoices.links" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";
import { useAbilities } from "@/Composables/UI/useAbilities";
import { formatCurrency } from "@/Utils";

defineProps({
  invoices: Object,
});

const { can } = useAbilities();

const { confirmAction, toast } = useNotifications();

const formatDate = (value) => {
  if (!value) return "—";
  return String(value).replace("T", " ").substring(0, 10);
};

const issueInvoice = (invoice) => {
  confirmAction(
    () => {
      router.patch(
        route("financial.invoices.issue", invoice.id),
        {},
        {
          onSuccess: () => toast("Invoice issued to patient.", "success"),
        }
      );
    },
    "Issue this invoice?",
    "The patient will be notified and asked to pay once issued. This cannot be undone from here."
  );
};

// Small local empty-state renderer (kept inline: no dedicated EmptyState component
// exists yet in resources/js/Components).
const EmptyState = {
  props: { message: String },
  render() {
    return h("div", { class: "flex flex-col items-center gap-2" }, [
      h(
        "svg",
        {
          class: "w-10 h-10 text-gray-300",
          fill: "none",
          stroke: "currentColor",
          viewBox: "0 0 24 24",
        },
        [
          h("path", {
            "stroke-linecap": "round",
            "stroke-linejoin": "round",
            "stroke-width": "1.5",
            d:
              "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
          }),
        ]
      ),
      h("p", { class: "text-sm font-medium" }, this.message),
    ]);
  },
};
</script>
