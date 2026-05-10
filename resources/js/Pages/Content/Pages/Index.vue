<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ pages: Object, filters: Object });
const viewMode = ref('table');
const applyStatus = (status) => router.get('/content/pages', { ...props.filters, status: status || undefined }, { preserveState: true, replace: true });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><h2 class="text-2xl font-semibold">Pages</h2><Link href="/content/pages/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Page</Link></div>
      <div class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm"><select class="rounded-md border-slate-300" :value="filters.status || ''" @change="applyStatus($event.target.value)"><option value="">All status</option><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option></select><div class="ml-auto flex gap-2"><button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'table'">Table</button><button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'card'">Card</button></div></div>
      <div v-if="viewMode === 'table'" class="overflow-hidden rounded-xl bg-white shadow-sm"><table class="min-w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="px-4 py-3">Page Title</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Updated</th></tr></thead><tbody><tr v-for="page in pages.data" :key="page.id" class="border-t border-slate-200"><td class="px-4 py-3"><Link :href="`/content/pages/${page.id}`" class="text-blue-600">{{ page.page_title }}</Link></td><td class="px-4 py-3">{{ page.page_slug }}</td><td class="px-4 py-3">{{ page.status }}</td><td class="px-4 py-3">{{ page.updated_at }}</td></tr></tbody></table></div>
      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><Link v-for="page in pages.data" :key="page.id" :href="`/content/pages/${page.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50"><p class="font-semibold">{{ page.page_title }}</p><p class="text-sm text-slate-600">/{{ page.page_slug }}</p><p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ page.status }}</p></Link></div>
    </div>
  </AppLayout>
</template>
