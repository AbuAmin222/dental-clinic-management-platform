<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
  canLogin: {
    type: Boolean,
    default: true,
  },
  canRegister: {
    type: Boolean,
    default: true,
  },
});
</script>

<template>
  <Head title="Welcome Portal" />

  <div
    class="min-h-screen bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white font-sans flex flex-col justify-between"
  >
    <!-- Premium Top Navigation Bar -->
    <header
      class="w-full bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 transition-all duration-300"
    >
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
      >
        <!-- Application Branding / Logo Layout -->
        <div class="flex items-center gap-3 group cursor-pointer">
          <div
            class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-200 transition-transform group-hover:scale-105 duration-200"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2.5"
              stroke="currentColor"
              class="w-5 h-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286Z"
              />
            </svg>
          </div>
          <div class="flex flex-col">
            <span class="text-sm font-black text-slate-900 tracking-tight uppercase"
              >CoreSystem</span
            >
            <span
              class="text-[10px] font-bold text-indigo-600 tracking-widest uppercase -mt-0.5"
              >Management Portal</span
            >
          </div>
        </div>

        <!-- Dynamic Context-Aware Action Triggers -->
        <nav v-if="canLogin" class="flex items-center gap-3">
          <template v-if="$page.props.auth.user">
            <Link
              :href="route('dashboard')"
              class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 rounded-xl shadow-md shadow-indigo-100 transition-all duration-200"
            >
              Open Workspace Dashboard →
            </Link>
          </template>

          <template v-else>
            <Link
              :href="route('login')"
              class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200/70 px-4 py-2.5 rounded-xl transition-all duration-200"
            >
              Sign In
            </Link>

            <Link
              v-if="canRegister"
              :href="route('register')"
              class="text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2.5 rounded-xl shadow-sm transition-all duration-200"
            >
              Create Account
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Main Entry Point Dashboard Grid -->
    <main
      class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 flex flex-col justify-center"
    >
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        <!-- Left Column: Hero & Interactive Introduction Segment -->
        <div
          class="lg:col-span-7 flex flex-col text-center lg:text-left items-center lg:items-start space-y-6"
        >
          <div
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm"
          >
            <span class="flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
            Enterprise Level Operations Platform v2.0
          </div>

          <h1
            class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.1]"
          >
            Unified Architecture <br />
            <span
              class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-500"
            >
              For Clinical Workspace Performance.
            </span>
          </h1>

          <p class="text-base text-slate-500 max-w-xl leading-relaxed font-medium">
            A high-performance system layer built to unify medical, operating, and
            administrative data vectors. Manage secure operational logs, access custom
            configurations, and interact with professional modules effortlessly.
          </p>

          <!-- Core Call to Actions Dynamic Container -->
          <div
            class="pt-4 flex flex-wrap justify-center lg:justify-start items-center gap-4 w-full"
          >
            <template v-if="$page.props.auth.user">
              <Link
                :href="route('dashboard')"
                class="w-full sm:w-auto text-center text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-6 py-3.5 rounded-xl shadow-lg shadow-indigo-100 transition duration-200"
              >
                Access Account Control Center
              </Link>
            </template>
            <template v-else>
              <Link
                :href="route('login')"
                class="w-full sm:w-auto text-center text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-6 py-3.5 rounded-xl shadow-lg shadow-indigo-100 transition duration-200"
              >
                Sign In to Session
              </Link>
              <Link
                v-if="canRegister"
                :href="route('register')"
                class="w-full sm:w-auto text-center text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 px-6 py-3.5 rounded-xl shadow-sm transition duration-200"
              >
                Register Professional Credentials
              </Link>
            </template>
          </div>
        </div>

        <!-- Right Column: Micro-Metric Overview Dashboard Layout Cards -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- System Component Card 1: Security -->
          <div
            class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between group"
          >
            <div
              class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition duration-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
                />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">State Integrity</h3>
              <p class="text-xs text-slate-400 leading-relaxed">
                Protected with rigorous 2FA checkpoints, encrypted tables, and state
                tokens.
              </p>
            </div>
          </div>

          <!-- System Component Card 2: Role Handling -->
          <div
            class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between group"
          >
            <div
              class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition duration-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">Role Matrix</h3>
              <p class="text-xs text-slate-400 leading-relaxed">
                Dynamic field synchronization structures for medical specialists,
                operators, and records.
              </p>
            </div>
          </div>

          <!-- System Component Card 3: Performance -->
          <div
            class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between group"
          >
            <div
              class="h-10 w-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition duration-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"
                />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">Inertia Hydration</h3>
              <p class="text-xs text-slate-400 leading-relaxed">
                Zero lag Single Page Application loads utilizing server-driven components
                safely.
              </p>
            </div>
          </div>

          <!-- System Component Card 4: Device Tracking -->
          <div
            class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between group"
          >
            <div
              class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition duration-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"
                />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">Session Tracing</h3>
              <p class="text-xs text-slate-400 leading-relaxed">
                Audit active hardware agents instantly and terminate suspicious user
                tokens remotely.
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Standard Compliant Production Footer -->
    <footer class="w-full bg-white border-t border-slate-100 py-5">
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-semibold text-slate-400"
      >
        <div>&copy; 2026 CoreSystem Global Technologies Ltd. All rights reserved.</div>
        <div class="flex items-center gap-4 font-bold text-slate-500">
          <span class="hover:text-indigo-600 cursor-pointer transition"
            >System Operational Stability: 99.98%</span
          >
        </div>
      </div>
    </footer>
  </div>
</template>
