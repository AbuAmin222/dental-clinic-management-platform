<template>
  <AppLayout title="Pending Account Reviews">
    <template #header>
      <div>
        <Link
          :href="route('admin.users.index')"
          class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition-colors"
        >
          &larr; All users
        </Link>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight mt-1">
          Pending Account Reviews
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          These accounts registered but cannot sign in yet
          (<code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">is_active = false</code>)
          — they are stuck on the holding page until activated here.
        </p>
      </div>
    </template>

    <div class="min-h-screen bg-gray-50 p-6">
      <div class="max-w-4xl mx-auto space-y-4">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm divide-y divide-gray-50">
          <div
            v-for="applicant in pending"
            :key="applicant.id"
            class="p-6 flex items-center justify-between gap-4 flex-wrap"
          >
            <div>
              <p class="font-semibold text-gray-900">
                {{ applicant.first_name }} {{ applicant.last_name }}
              </p>
              <p class="text-sm text-gray-500">{{ applicant.email }}</p>
              <p v-if="applicant.phone" class="text-xs text-gray-400 mt-0.5">
                {{ applicant.phone }}
              </p>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span
                  v-for="role in applicant.roles"
                  :key="role.id"
                  class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-50 border border-gray-200 text-gray-500"
                >
                  {{ role.display_name }}
                </span>
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 border border-amber-200 text-amber-700"
                >
                  Registered {{ formatDate(applicant.created_at) }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <button
                type="button"
                :disabled="acting === applicant.id"
                class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-semibold rounded-xl transition-all disabled:opacity-50"
                @click="confirmReject(applicant)"
              >
                Reject
              </button>
              <button
                type="button"
                :disabled="acting === applicant.id"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all disabled:opacity-50"
                @click="activate(applicant)"
              >
                {{ acting === applicant.id ? "Working…" : "Activate" }}
              </button>
            </div>
          </div>

          <div v-if="pending.length === 0" class="p-12 text-center text-gray-400 font-medium">
            No accounts waiting for review. 🎉
          </div>
        </div>

        <Pagination :links="pendingUsers.links" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { useNotifications } from "@/Composables/UI/useNotifications";

const props = defineProps({
  pendingUsers: { type: Object, required: true },
});

const { confirmAction } = useNotifications();

// Local reactive copy of just this page's rows so an activated/rejected row disappears
// instantly; pagination links still come straight from the server-provided object.
const pending = reactive([...props.pendingUsers.data]);
const acting = ref(null);

const formatDate = (iso) =>
  new Date(iso).toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });

const removeFromList = (id) => {
  const idx = pending.findIndex((u) => u.id === id);
  if (idx !== -1) pending.splice(idx, 1);
};

const activate = (applicant) => {
  acting.value = applicant.id;
  router.patch(
    route("admin.users.activate", applicant.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => removeFromList(applicant.id),
      onFinish: () => (acting.value = null),
    }
  );
};

const reject = (applicant) => {
  acting.value = applicant.id;
  router.patch(
    route("admin.users.reject", applicant.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => removeFromList(applicant.id),
      onFinish: () => (acting.value = null),
    }
  );
};

const confirmReject = (applicant) => {
  confirmAction(
    () => reject(applicant),
    "Reject this account?",
    `${applicant.first_name} ${applicant.last_name}'s registration will be permanently discarded.`
  );
};
</script>
