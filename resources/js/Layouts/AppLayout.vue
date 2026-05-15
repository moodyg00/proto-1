<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  title: { type: String, default: '' },
})

const page = usePage();
const mobileOpen = ref(false)

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

const branding = computed(() => page.props.branding ?? {})
const brandName = computed(() => branding.value.brand_name ?? 'Moody Home Services, LLC')
const logoUrl = computed(() => branding.value.logo_url ?? '/images/moody-home-services-mark.svg')
const pageTitle = computed(() => props.title ? `${props.title} • ${brandName.value}` : brandName.value)
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <Head :title="pageTitle" />

    <header class="sticky top-0 z-50 border-b border-slate-800 bg-slate-950 text-white shadow-lg">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
          <button
            type="button"
            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-100 transition hover:bg-white/10 lg:hidden"
            @click="mobileOpen = !mobileOpen"
          >
            <span class="sr-only">Toggle navigation</span>
            <span class="flex flex-col gap-1.5">
              <span class="h-0.5 w-5 rounded-full bg-current" />
              <span class="h-0.5 w-5 rounded-full bg-current" />
              <span class="h-0.5 w-5 rounded-full bg-current" />
            </span>
          </button>

          <Link href="/" class="flex min-w-0 items-center gap-3">
            <img :src="logoUrl" :alt="brandName" class="h-9 w-auto rounded-xl bg-white/5 p-1" />
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold tracking-[0.18em] text-amber-300">Moody Home Services, LLC</p>
              <p class="truncate text-xs text-slate-400">Operations, CRM, accounting, and field work in one place.</p>
            </div>
          </Link>
        </div>

        <div class="hidden text-right lg:block">
          <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Workspace</p>
          <p class="text-sm font-medium text-slate-100">{{ props.title || brandName }}</p>
        </div>
      </div>
    </header>

    <nav class="border-b border-slate-200 bg-white shadow-sm">
      <div class="mx-auto max-w-7xl">
        <div :class="['overflow-x-auto scrollbar-none', mobileOpen ? 'block' : 'hidden lg:block']">
          <div class="flex items-stretch">
        <Link
          v-for="item in nav"
          :key="item.href"
          :href="item.href"
          class="flex-none px-6 py-3.5 text-sm font-medium tracking-wide whitespace-nowrap transition-colors duration-150"
          :class="isActive(item.href)
            ? 'border-b-2 border-amber-500 bg-amber-50 text-slate-950'
            : 'border-b-2 border-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-950'"
        >
          {{ item.label }}
        </Link>
          </div>
        </div>
      </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-6">
      <slot />
    </main>

  </div>
</template>
