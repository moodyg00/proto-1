<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const nav = [
  { label: 'Operations',      href: '/operations/dashboard' },
  { label: 'CRM',             href: '/crm/dashboard' },
  { label: 'Accounting',      href: '/accounting/dashboard' },
  { label: 'Banking',         href: '/banking/dashboard' },
  { label: 'Content',         href: '/content/dashboard' },
  { label: 'Administration',  href: '/administration/dashboard' },
];

function isActive(href) {
  const segment = '/' + href.split('/')[1];
  return usePage().url.startsWith(segment);
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">

    <!-- Primary nav — sticky, full-width, horizontal scroll -->
    <nav class="sticky top-0 z-50 w-full bg-slate-950 shadow-lg">
      <div class="flex items-stretch overflow-x-auto scrollbar-none">
        <Link
          v-for="item in nav"
          :key="item.href"
          :href="item.href"
          class="flex-none px-6 py-3.5 text-sm font-medium tracking-wide whitespace-nowrap transition-colors duration-150"
          :class="isActive(item.href)
            ? 'bg-white/10 text-white border-b-2 border-sky-400'
            : 'text-slate-400 hover:text-white hover:bg-white/5 border-b-2 border-transparent'"
        >
          {{ item.label }}
        </Link>
      </div>
    </nav>

    <!-- Brand line -->
    <div class="w-full bg-slate-900 px-6 py-2">
      <p class="text-sm text-slate-400 tracking-wide">Moody Home Services LLC.</p>
    </div>

    <main class="mx-auto max-w-7xl px-6 py-6">
      <slot />
    </main>

  </div>
</template>
