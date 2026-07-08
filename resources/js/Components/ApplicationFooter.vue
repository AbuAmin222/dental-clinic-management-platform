<script setup>
import { computed } from "vue";

// الحصول على السنة الحالية ديناميكياً لضمان دقة الأداء
const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
  <footer class="bg-white border-t border-slate-100 mt-auto transition-all duration-200">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- جهة اليمين: التوقيع الخاص بك (Special Signature) -->
        <div class="text-sm text-slate-500 font-medium order-2 md:order-1">
          <span>© {{ currentYear }} Dental Clinic App. DEVELOPED BY </span>
          <span
            class="font-bold text-indigo-600 hover:text-indigo-700 cursor-pointer transition"
          >
            DIA
          </span>
        </div>

        <!-- جهة اليسار: الروابط المهنية والشبكات الذكية -->
        <div class="flex items-center gap-5 order-1 md:order-2">
          <!-- رابط LinkedIn -->
          <a
            href="https://www.linkedin.com/in/diael-dinhabib/"
            target="_blank"
            rel="noopener noreferrer"
            class="text-slate-400 hover:text-blue-600 text-sm font-medium flex items-center gap-1 transition"
          >
            <span>🔗</span> LinkedIn
          </a>

          <span class="text-slate-200 hidden sm:inline">|</span>

          <!-- رابط Freelancer -->
          <a
            href="https://www.freelancer.com/u/DevDiaeldin"
            target="_blank"
            rel="noopener noreferrer"
            class="text-slate-400 hover:text-cyan-600 text-sm font-medium flex items-center gap-1 transition"
          >
            <span>💼</span> Freelancer
          </a>

          <span class="text-slate-200 hidden sm:inline">|</span>

          <!-- رابط السيرة الذاتية الرقمية (CV / Portfolio Site) -->
          <a
            href="https://europa.eu/europass/eportfolio/screen/share/74f94f0c-da5b-40d4-b8cd-4129f9e8dd01?lang=en"
            target="_blank"
            rel="noopener noreferrer"
            class="text-slate-400 hover:text-indigo-600 text-sm font-medium flex items-center gap-1 transition"
          >
            <span>📄</span> Curriculum Vitae
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>
