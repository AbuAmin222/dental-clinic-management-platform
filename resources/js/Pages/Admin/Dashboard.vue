<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue"; // سنحتاج لإنشاء هذا المكون
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  faculties: Array,
  users: Object, // يحتوي على البيانات المرقمة (Paginated)
  titles: Array,
});

const showFacultyModal = ref(false);

const form = useForm({
  name: "",
});

const submitFaculty = () => {
  form.post(route("faculties.store"), {
    onSuccess: () => {
      showFacultyModal.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <AppLayout title="لوحة التحكم الأكاديمية">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Developers University - نظام الإدارة المتقدم
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <div class="text-gray-500 text-sm font-medium">إجمالي الكليات</div>
            <div class="text-3xl font-bold">{{ faculties.length }}</div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
            <div class="text-gray-500 text-sm font-medium">إجمالي الطلاب والدكاترة</div>
            <div class="text-3xl font-bold">{{ users.total }}</div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
            <div class="text-gray-500 text-sm font-medium">حجم البيانات المعالجة</div>
            <div class="text-3xl font-bold">ضخمة (10k+)</div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4">إدارة الكادر والطلاب</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  الاسم
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  الرتبة
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  الدور
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                >
                  التخصص
                </th>
                <th
                  class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"
                >
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="user in users.data"
                :key="user.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold"
                    >{{ user.title }}</span
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ user.role }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ user.specialization?.name || "غير محدد" }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button class="text-indigo-600 hover:text-indigo-900 ml-4">
                    تعديل
                  </button>
                  <button class="text-red-600 hover:text-red-900">حذف</button>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="mt-6">
            <Pagination :links="users.links" />
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-bold">إدارة الكليات</h3>
      <button
        @click="showFacultyModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition"
      >
        + إضافة كلية جديدة
      </button>
    </div>

    <div
      v-if="showFacultyModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center"
    >
      <div class="bg-white p-8 rounded-lg shadow-xl w-96">
        <h2 class="text-xl font-bold mb-4">إضافة كلية</h2>
        <input
          v-model="form.name"
          type="text"
          placeholder="اسم الكلية"
          class="w-full border p-2 rounded mb-4"
        />
        <div class="flex justify-end">
          <button @click="showFacultyModal = false" class="ml-2 text-gray-500">
            إلغاء
          </button>
          <button @click="submitFaculty" class="bg-blue-600 text-white px-4 py-2 rounded">
            حفظ
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
