<template>
  <AppLayout title="Staff Salaries">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
          Staff Base Salaries
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Set the policy-level base salary for each staff member. Financial officers own
          the payroll disbursement lifecycle from here on.
        </p>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-5xl mx-auto space-y-8">
        <div v-for="(members, role) in groupedStaff" :key="role" class="space-y-3">
          <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider ps-1">
            {{ roleLabels[role] ?? role }}
          </h2>
          <div
            class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
          >
            <table class="w-full text-left border-collapse hidden sm:table">
              <thead>
                <tr
                  class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider"
                >
                  <th class="py-4 px-6">Name</th>
                  <th class="py-4 px-6">Base Salary (ILS)</th>
                  <th class="py-4 px-6 text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 text-sm">
                <tr
                  v-for="member in members"
                  :key="member.id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <td class="py-4 px-6 font-semibold text-gray-900">
                    {{ member.first_name }} {{ member.last_name }}
                  </td>
                  <td class="py-4 px-6">
                    <input
                      v-model="drafts[member.id]"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="Not set"
                      class="w-40 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 outline-none focus:border-indigo-500 focus:bg-white transition-all"
                      @keyup.enter="saveSalary(member)"
                    />
                    <p
                      v-if="errors[member.id]"
                      class="text-xs text-red-500 mt-1 font-medium"
                    >
                      {{ errors[member.id] }}
                    </p>
                  </td>
                  <td class="py-4 px-6 text-right">
                    <button
                      @click="saveSalary(member)"
                      :disabled="savingId === member.id"
                      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
                    >
                      {{ savingId === member.id ? "Saving..." : "Save" }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Mobile stacked cards -->
            <div class="sm:hidden divide-y divide-gray-50">
              <div v-for="member in members" :key="member.id" class="p-5 space-y-2">
                <p class="font-semibold text-gray-900">
                  {{ member.first_name }} {{ member.last_name }}
                </p>
                <div class="flex items-center gap-2">
                  <input
                    v-model="drafts[member.id]"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="Not set"
                    class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 outline-none focus:border-indigo-500 focus:bg-white transition-all"
                  />
                  <button
                    @click="saveSalary(member)"
                    :disabled="savingId === member.id"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
                  >
                    {{ savingId === member.id ? "..." : "Save" }}
                  </button>
                </div>
                <p v-if="errors[member.id]" class="text-xs text-red-500 font-medium">
                  {{ errors[member.id] }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="staff.length === 0"
          class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center text-gray-400 font-medium"
        >
          No staff members found.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";

const props = defineProps({
  staff: Array,
});

const { toast } = useNotifications();

const roleLabels = {
  doctor: "Doctors",
  receptionist: "Receptionists",
  financial: "Financial Officers",
  admin: "Administrators",
};

const roleOrder = ["doctor", "receptionist", "financial", "admin"];

const groupedStaff = computed(() => {
  const groups = {};
  for (const role of roleOrder) {
    const members = props.staff.filter((m) => m.role === role);
    if (members.length) groups[role] = members;
  }
  return groups;
});

// Draft values keyed by user id, initialized from the current base_salary so
// the field only saves when the officer explicitly clicks Save (or presses Enter),
// never on blur, to avoid accidental writes to a sensitive field.
const drafts = reactive(
  Object.fromEntries(props.staff.map((m) => [m.id, m.base_salary ?? ""]))
);
const errors = reactive({});
const savingId = ref(null);

const saveSalary = (member) => {
  savingId.value = member.id;
  errors[member.id] = null;

  router.patch(
    route("admin.staffSalaries.update", member.id),
    { base_salary: drafts[member.id] === "" ? null : drafts[member.id] },
    {
      preserveScroll: true,
      onSuccess: () => toast("Base salary updated.", "success"),
      onError: (pageErrors) => {
        errors[member.id] = pageErrors.base_salary ?? "Could not save.";
      },
      onFinish: () => {
        savingId.value = null;
      },
    }
  );
};
</script>
