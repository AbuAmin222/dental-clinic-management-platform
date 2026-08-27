<script setup>
import { ref, watch, onMounted, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import Swal from "sweetalert2";
import ApplicationFooter from "@/Components/ApplicationFooter.vue";

const page = usePage();
const props = defineProps({ title: String });
const showingNavigationDropdown = ref(false);

const userRole = computed(() => page.props.auth.user?.role);

const navigationLinks = computed(() => {
  const links = [{ name: "Main", href: route("dashboard"), routeName: "dashboard" }];

  const roleRoutes = {
    patient: { name: "My Medical File", route: "patient.dashboard" },
    doctor: { name: "Clinic Management", route: "doctor.dashboard" },
    receptionist: { name: "Reception & Appointments", route: "receptionist.dashboard" },
  };

  if (userRole.value && roleRoutes[userRole.value]) {
    const currentRole = roleRoutes[userRole.value];
    links.push({
      name: currentRole.name,
      href: route(currentRole.route),
      routeName: currentRole.route,
    });
  }

  return links;
});

const showToast = (message, type = "success") => {
  Swal.fire({
    title: type === "success" ? "Success!" : "Error!",
    text: message,
    icon: type,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  });
};

const logout = () => router.post(route("logout"));

const switchToTeam = (team) => {
  router.put(
    route("current-team.update"),
    { team_id: team.id },
    { preserveState: false }
  );
};

// 🎯 مراقبة مركزية موحدة للأخطاء والرسائل القادمة من السيرفر لمنع تكرار التنفيذ المعماري
watch(
  () => page.props.errors,
  (errors) => {
    if (Object.keys(errors).length > 0) {
      showToast("Please check the highlighted fields and try again.", "error");
    }
  },
  { deep: true }
);

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) showToast(flash.success, "success");
    if (flash?.error) showToast(flash.error, "error");
  },
  { deep: true, immediate: true }
);
</script>

<template>
  <div>
    <Head :title="props.title" />
    <Banner />

    <div class="min-h-screen bg-slate-50">
      <nav
        class="bg-white border-b border-slate-100 sticky top-0 z-40 shadow-sm transition-all"
      >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <!-- Left Side: Logo & Dynamic Main Navigation Links -->
            <div class="flex">
              <div class="shrink-0 flex items-center">
                <Link :href="route('dashboard')">
                  <ApplicationMark class="block h-9 w-auto" />
                </Link>
              </div>

              <!-- Desktop Navigation Links Loop -->
              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink
                  v-for="link in navigationLinks"
                  :key="link.name"
                  :href="link.href"
                  :active="route().current(link.routeName)"
                >
                  {{ link.name }}
                </NavLink>
              </div>
            </div>

            <!-- Right Side: Standard Drops & Configurations -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
              <!-- 🏢 Teams Dropdown (Rendered Only If Enabled) -->
              <div v-if="$page.props.jetstream.hasTeamFeatures" class="relative">
                <Dropdown align="right" width="60">
                  <template #trigger>
                    <button
                      type="button"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-slate-500 bg-white hover:text-slate-700 hover:bg-slate-50 transition duration-150"
                    >
                      {{ $page.props.auth.user.current_team.name }}
                      <svg
                        class="ms-2 -me-0.5 size-4 opacity-70"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"
                        />
                      </svg>
                    </button>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <div
                        class="block px-4 py-2 text-xs text-slate-400 font-bold uppercase tracking-wider"
                      >
                        Manage Team
                      </div>
                      <DropdownLink
                        :href="route('teams.show', $page.props.auth.user.current_team)"
                        >Team Settings</DropdownLink
                      >
                      <DropdownLink
                        v-if="$page.props.jetstream.canCreateTeams"
                        :href="route('teams.create')"
                        >Create New Team</DropdownLink
                      >

                      <template v-if="$page.props.auth.user.all_teams.length > 1">
                        <div class="border-t border-slate-100 my-1" />
                        <div
                          class="block px-4 py-2 text-xs text-slate-400 font-bold uppercase tracking-wider"
                        >
                          Switch Teams
                        </div>
                        <template
                          v-for="team in $page.props.auth.user.all_teams"
                          :key="team.id"
                        >
                          <form @submit.prevent="switchToTeam(team)">
                            <DropdownLink as="button" class="w-full text-left">
                              <div class="flex items-center">
                                <svg
                                  v-if="team.id == $page.props.auth.user.current_team_id"
                                  class="me-2 size-4 text-emerald-500"
                                  fill="none"
                                  viewBox="0 0 24 24"
                                  stroke-width="2"
                                  stroke="currentColor"
                                >
                                  <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12.75l6 6 9-13.5"
                                  />
                                </svg>
                                <div>{{ team.name }}</div>
                              </div>
                            </DropdownLink>
                          </form>
                        </template>
                      </template>
                    </div>
                  </template>
                </Dropdown>
              </div>

              <!-- 👤 Highly Efficient User Profile Settings Dropdown -->
              <div class="relative">
                <Dropdown align="right" width="52">
                  <template #trigger>
                    <button
                      v-if="$page.props.jetstream.managesProfilePhotos"
                      class="flex text-sm border-2 border-slate-100 rounded-full focus:outline-none focus:border-indigo-500 transition shadow-sm hover:scale-105 duration-150"
                    >
                      <img
                        class="size-8 rounded-full object-cover"
                        :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.full_name"
                      />
                    </button>
                    <span v-else class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-slate-600 bg-white hover:text-slate-800 transition duration-150"
                      >
                        {{ $page.props.auth.user.full_name }}
                        <svg
                          class="ms-2 -me-0.5 size-4"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke-width="1.5"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-64 md:w-72 p-1">
                      <div
                        class="block px-4 py-2 text-xs text-slate-400 font-bold uppercase tracking-wider"
                      >
                        Account Settings
                      </div>

                      <!-- الروابط التفصيلية المباشرة المنقولة من واجهة الصفحة -->
                      <DropdownLink
                        :href="route('profile.edit')"
                        class="flex items-center gap-2 text-slate-700 hover:bg-slate-50"
                      >
                        <span>👤</span> Profile & Role Data
                      </DropdownLink>

                      <DropdownLink
                        :href="route('profile.password')"
                        class="flex items-center gap-2 text-slate-700 hover:bg-slate-50"
                      >
                        <span>🔑</span> Manage Password
                      </DropdownLink>

                      <DropdownLink
                        :href="route('profile.two-factor')"
                        class="flex items-center gap-2 text-slate-700 hover:bg-slate-50"
                      >
                        <span>🛡️</span> Two-Factor Auth
                      </DropdownLink>

                      <DropdownLink
                        :href="route('profile.devices')"
                        class="flex items-center gap-2 text-slate-700 hover:bg-slate-50"
                      >
                        <span>💻</span> Active Devices
                      </DropdownLink>

                      <DropdownLink
                        :href="route('profile.delete')"
                        class="flex items-center gap-2 text-red-600 hover:bg-red-50"
                      >
                        <span>❌</span> Delete Account
                      </DropdownLink>

                      <div class="border-t border-slate-100 my-1" />

                      <form @submit.prevent="logout">
                        <DropdownLink
                          as="button"
                          class="text-slate-600 hover:text-red-700 hover:bg-red-50/60 w-full text-left font-medium"
                        >
                          Log Out
                        </DropdownLink>
                      </form>
                    </div>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hamburger Responsive Trigger -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-slate-500 hover:bg-slate-50 focus:outline-none transition duration-150"
              >
                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- 📱 Responsive Mobile Navigation Menu (Clean & Non-Redundant) -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden bg-slate-50 border-t border-slate-100"
        >
          <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink
              v-for="link in navigationLinks"
              :key="'resp-' + link.name"
              :href="link.href"
              :active="route().current(link.routeName)"
            >
              {{ link.name }}
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('profile.edit')"
              :active="route().current('profile.edit')"
            >
              👤 Profile & Role Data
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('profile.password')"
              :active="route().current('profile.password')"
            >
              🔑 Manage Password
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('profile.two-factor')"
              :active="route().current('profile.two-factor')"
            >
              🛡️ Two-Factor Auth
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('profile.devices')"
              :active="route().current('profile.devices')"
            >
              💻 Active Devices
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('profile.delete')"
              :active="route().current('profile.delete')"
              class="text-red-600"
            >
              ❌ Delete Account
            </ResponsiveNavLink>

            <div class="border-t border-slate-200 my-2" />

            <form method="POST" @submit.prevent="logout">
              <ResponsiveNavLink as="button" class="text-slate-600 w-full text-left">
                Log Out
              </ResponsiveNavLink>
            </form>
          </div>

          <!-- Responsive Profile Options -->
          <div class="pt-4 pb-3 border-t border-slate-200 bg-white">
            <div class="flex items-center px-4 mb-3">
              <div
                v-if="$page.props.jetstream.managesProfilePhotos"
                class="shrink-0 me-3"
              >
                <img
                  class="size-10 rounded-full object-cover border border-slate-200"
                  :src="$page.props.auth.user.profile_photo_path"
                  :alt="$page.props.auth.user.full_name"
                />
              </div>
              <div>
                <div class="font-semibold text-base text-slate-800">
                  {{ $page.props.auth.user.full_name }}
                </div>
                <div class="font-medium text-sm text-slate-500">
                  {{ $page.props.auth.user.email }}
                </div>
              </div>
            </div>

            <div class="space-y-1">
              <!-- Team Management Context (Mobile) -->
              <template v-if="$page.props.jetstream.hasTeamFeatures">
                <div class="border-t border-slate-200 my-2" />
                <div
                  class="block px-4 py-2 text-xs text-slate-400 font-bold uppercase tracking-wider"
                >
                  Manage Team
                </div>
                <ResponsiveNavLink
                  :href="route('teams.show', $page.props.auth.user.current_team)"
                  :active="route().current('teams.show')"
                  >Team Settings</ResponsiveNavLink
                >
                <ResponsiveNavLink
                  v-if="$page.props.jetstream.canCreateTeams"
                  :href="route('teams.create')"
                  :active="route().current('teams.create')"
                  >Create New Team</ResponsiveNavLink
                >

                <template v-if="$page.props.auth.user.all_teams.length > 1">
                  <div class="border-t border-slate-200 my-2" />
                  <div
                    class="block px-4 py-2 text-xs text-slate-400 font-bold uppercase tracking-wider"
                  >
                    Switch Teams
                  </div>
                  <template
                    v-for="team in $page.props.auth.user.all_teams"
                    :key="team.id"
                  >
                    <form @submit.prevent="switchToTeam(team)">
                      <ResponsiveNavLink as="button" class="w-full text-left">
                        <div class="flex items-center">
                          <svg
                            v-if="team.id == $page.props.auth.user.current_team_id"
                            class="me-2 size-4 text-emerald-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4.5 12.75l6 6 9-13.5"
                            />
                          </svg>
                          <div>{{ team.name }}</div>
                        </div>
                      </ResponsiveNavLink>
                    </form>
                  </template>
                </template>
              </template>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading Container -->
      <header v-if="$slots.header" class="bg-white shadow-sm border-b border-slate-100">
        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Main Application Content Context View -->
      <main class="animate-fadeIn duration-200">
        <slot />
      </main>

      <ApplicationFooter />
    </div>
  </div>
</template>
