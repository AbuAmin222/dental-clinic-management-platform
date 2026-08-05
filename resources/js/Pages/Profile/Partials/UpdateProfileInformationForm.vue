<script setup>
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

import { useNotifications, useFileHandle } from "@/Composables/";

const props = defineProps({
  user: Object,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user?.role);
const { toast } = useNotifications();

const sanitizeDateOnly = (dateTimeString) => {
  if (!dateTimeString) return "";
  return dateTimeString.split(" ")[0].split("T")[0];
};

const form = useForm({
  _method: "PUT",
  first_name: props.user.first_name || "",
  middle_name: props.user.middle_name || "",
  last_name: props.user.last_name || "",

  username: props.user.username || "",
  email: props.user.email || "",

  phone: props.user.phone || "",
  gender: props.user.gender || "",
  date_of_birth: sanitizeDateOnly(props.user.date_of_birth), // Formatted safely
  address: props.user.address || "",

  photo: null,
  profile_photo: null,
});

const { profilePreview, handleFileUpload, removeFile } = useFileHandle(form);
const photoInput = ref(null);

const selectNewPhoto = () => {
  photoInput.value.click();
};
const handlePhotoChange = (e) => {
  handleFileUpload(e, "profile_photo");
};

const updateProfileInformation = () => {
  if (form.profile_photo) {
    form.photo = form.profile_photo;
  }

  form
    .transform((data) => {
      const { profile_photo, ...payload } = data;
      return payload;
    })
    .post(route("user-profile-information.update"), {
      errorBag: "updateProfileInformation",
      preserveScroll: true,
      onSuccess: () => {
        toast("Personal details updated successfully.", "success");
      },
    });
};

const deletePhoto = () => {
  router.delete(route("current-user-photo.destroy"), {
    preserveScroll: true,
    onSuccess: () => {
      removeFile("profile_photo");
      form.photo = null;
      toast("Profile picture removed.", "success");
    },
  });
};
</script>

<template>
  <FormSection @submitted="updateProfileInformation">
    <template #title>
      <span class="text-slate-900 font-bold text-lg tracking-tight"
        >Personal Profile</span
      >
    </template>
    <template #description>
      <span class="text-slate-500 text-sm leading-relaxed">
        Manage your core identity, contact records, and system-wide visibility
        configurations.
      </span>
    </template>

    <template #form>
      <!-- Premium Profile Photo Card Row -->
      <div
        v-if="$page.props.jetstream.managesProfilePhotos"
        class="col-span-6 bg-slate-50/70 border border-slate-100 rounded-2xl p-5 flex flex-col sm:flex-row items-center gap-6 shadow-sm transition duration-200"
      >
        <input
          id="photo"
          ref="photoInput"
          type="file"
          class="hidden"
          @change="handlePhotoChange"
        />
        <div class="relative group">
          <div v-show="!profilePreview" class="relative">
            <img
              :src="$page.props.auth.user.profile_photo_path"
              :alt="$page.props.auth.user.full_name"
              class="rounded-full h-24 w-24 object-cover ring-4 ring-white shadow-md transition duration-300"
            />
            <div
              class="absolute inset-0 rounded-full bg-black/10 opacity-0 group-hover:opacity-100 transition duration-200 flex items-center justify-center text-white text-xs font-medium cursor-pointer"
              @click="selectNewPhoto"
            >
              Change
            </div>
          </div>
          <div v-show="profilePreview">
            <span
              class="block rounded-full w-24 h-24 bg-cover bg-no-repeat bg-center ring-4 ring-white shadow-md"
              :style="'background-image: url(\'' + profilePreview + '\');'"
            />
          </div>
        </div>

        <div
          class="flex-1 flex flex-col gap-2 items-center sm:items-start text-center sm:text-left"
        >
          <div>
            <span
              class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1"
              >Account Privilege</span
            >
            <div
              v-if="userRole === 'patient'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-sm"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Verified Patient Account
            </div>
            <div
              v-if="userRole === 'doctor'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-sm"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
              Specialist Doctor Panel
            </div>
            <div
              v-if="userRole === 'receptionist'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60 shadow-sm"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-purple-500 animate-pulse"></span>
              Medical Desk Operator
            </div>
          </div>
          <div class="flex gap-2 mt-1">
            <button
              type="button"
              @click="selectNewPhoto"
              class="text-xs font-semibold bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 py-1.5 px-3 rounded-xl shadow-sm transition"
            >
              Upload Image
            </button>
            <button
              v-if="user.profile_photo_path || profilePreview"
              type="button"
              @click="deletePhoto"
              class="text-xs font-semibold bg-red-50 hover:bg-red-100/80 border border-red-200 text-red-600 py-1.5 px-3 rounded-xl transition"
            >
              Remove
            </button>
          </div>
        </div>
      </div>

      <!-- Identity Grid Layout -->
      <div class="col-span-6 md:col-span-2">
        <InputLabel
          for="first_name"
          value="First Name"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="first_name"
          v-model="form.first_name"
          type="text"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition"
          placeholder="John"
        />
        <InputError :message="form.errors.first_name" class="mt-1" />
      </div>

      <div class="col-span-6 md:col-span-2">
        <InputLabel
          for="middle_name"
          value="Middle Name"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="middle_name"
          v-model="form.middle_name"
          type="text"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition"
          placeholder="Edward"
        />
        <InputError :message="form.errors.middle_name" class="mt-1" />
      </div>

      <div class="col-span-6 md:col-span-2">
        <InputLabel
          for="last_name"
          value="Last Name"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="last_name"
          v-model="form.last_name"
          type="text"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition"
          placeholder="Doe"
        />
        <InputError :message="form.errors.last_name" class="mt-1" />
      </div>

      <!-- Username -->
      <div class="col-span-6 md:col-span-2">
        <InputLabel
          for="username"
          value="Username"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="username"
          v-model="form.username"
          type="text"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition"
          placeholder="Username"
        />
        <InputError :message="form.errors.username" class="mt-1" />
      </div>

      <div class="col-span-6 md:col-span-4">
        <InputLabel
          for="email"
          value="Email Address"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition bg-slate-50/40"
          required
        />
        <InputError :message="form.errors.email" class="mt-1" />
      </div>

      <div class="col-span-6 md:col-span-2">
        <InputLabel
          for="phone"
          value="Phone Number"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="phone"
          v-model="form.phone"
          type="text"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition"
          placeholder="+970 59-xxxx-xxx"
        />
        <InputError :message="form.errors.phone" class="mt-1" />
      </div>

      <div class="col-span-6 md:col-span-3">
        <InputLabel
          for="gender"
          value="Gender"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <select
          id="gender"
          v-model="form.gender"
          class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl shadow-sm text-sm py-2.5 px-3 transition text-slate-800 outline-none"
        >
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <InputError :message="form.errors.gender" class="mt-1" />
      </div>

      <div class="col-span-6 md:col-span-3">
        <InputLabel
          for="date_of_birth"
          value="Date of Birth"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="date_of_birth"
          v-model="form.date_of_birth"
          type="date"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition text-slate-800"
        />
        <InputError :message="form.errors.date_of_birth" class="mt-1" />
      </div>

      <div class="col-span-6">
        <InputLabel
          for="address"
          value="Residential Address"
          class="text-slate-700 font-semibold mb-1.5"
        />
        <TextInput
          id="address"
          v-model="form.address"
          type="text"
          class="w-full !rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition"
          placeholder="City, Street, Country"
        />
        <InputError :message="form.errors.address" class="mt-1" />
      </div>
    </template>

    <template #actions>
      <PrimaryButton
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
        class="!rounded-xl shadow-md !px-5 !py-2.5 bg-indigo-600 hover:bg-indigo-700 transition"
      >
        Save Structural Changes
      </PrimaryButton>
    </template>
  </FormSection>
</template>
