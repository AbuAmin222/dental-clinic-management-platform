<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";

defineProps({
  terms: String,
});
</script>

<template>
  <Head title="Terms of Service" />

  <div
    class="font-sans text-slate-900 antialiased bg-gradient-to-br from-slate-50 via-gray-100 to-slate-100 min-h-screen"
  >
    <div class="py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto flex flex-col items-center">
        <!-- شعار المنصة -->
        <div class="transform hover:scale-105 transition-transform duration-300 mb-8">
          <AuthenticationCardLogo />
        </div>

        <!-- حاوية المحتوى القانوني -->
        <div
          class="w-full bg-white shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-100 overflow-hidden backdrop-blur-sm"
        >
          <!-- رأس الصفحة الداخلي -->
          <div
            class="bg-gradient-to-r from-slate-800 to-indigo-900 p-8 text-center sm:text-left"
          >
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Terms of Service
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-2 font-medium">
              Last updated: June 2026 • Platform Usage Agreements
            </p>
          </div>

          <!-- متن الشروط المجلوب من السيرفر -->
          <div
            class="p-8 prose prose-slate max-w-none text-slate-600 leading-relaxed space-y-4 text-sm sm:text-base border-b border-slate-100"
            v-html="terms"
          />

          <!-- التوقيع الاحترافي الذكي المدمج في نهاية الصفحة (Developer Signature) -->
          <div
            class="bg-slate-50/70 p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-50"
          >
            <div class="flex items-center gap-3.5">
              <div
                class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center shadow-md shadow-indigo-200"
              >
                <span class="text-white font-black text-sm tracking-wider font-mono"
                  >DD</span
                >
              </div>
              <div class="text-center sm:text-left">
                <h4 class="text-sm font-bold text-slate-800 tracking-wide">
                  Engineered by
                  <span class="text-indigo-600 font-extrabold">Dev.DIA</span>
                </h4>
                <p
                  class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest mt-0.5"
                >
                  Systems Architect
                </p>
              </div>
            </div>

            <!-- أزرار التواصل والروابط المهنية للمطور -->
            <div class="flex flex-wrap items-center justify-center gap-2.5">
              <!-- LinkedIn -->
              <a
                href="https://www.linkedin.com/in/diael-dinhabib/"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-blue-400 text-slate-600 hover:text-blue-600 font-medium text-xs shadow-sm transition-all duration-200 hover:-translate-y-0.5"
              >
                <span class="text-blue-600 font-bold">in</span> LinkedIn
              </a>
              <!-- Freelancer -->
              <a
                href="https://www.freelancer.com/u/DevDiaeldin"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-cyan-500 text-slate-600 hover:text-cyan-600 font-medium text-xs shadow-sm transition-all duration-200 hover:-translate-y-0.5"
              >
                <span class="text-cyan-500 font-black">f</span> Freelancer
              </a>
              <!-- Europass CV -->
              <a
                href="https://europa.eu/europass/eportfolio/screen/share/74f94f0c-da5b-40d4-b8cd-4129f9e8dd01?lang=en"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-amber-500 text-slate-600 hover:text-amber-600 font-medium text-xs shadow-sm transition-all duration-200 hover:-translate-y-0.5"
              >
                <span class="text-amber-500 font-bold">★</span> Europass CV
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
