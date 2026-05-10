<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ designs: Object, filters: Object });
const viewMode = ref('card');
const applyStatus = (status) => router.get('/content/physical-designs', { ...props.filters, status: status || undefined }, { preserveState: true, replace: true });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><h2 class="text-2xl font-semibold">Physical Designs</h2><Link href="/content/physical-designs/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Design</Link></div>
      <div class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm"><select class="rounded-md border-slate-300" :value="filters.status || ''" @change="applyStatus($event.target.value)"><option value="">All status</option><option value="draft">Draft</option><option value="approved">Approved</option><option value="archived">Archived</option></select><div class="ml-auto flex gap-2"><button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'table'">Table</button><button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'card'">Card</button></div></div>
      <div v-if="viewMode === 'table'" class="overflow-hidden rounded-xl bg-white shadow-sm"><table class="min-w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Latest Version</th><th class="px-4 py-3">Updated</th></tr></thead><tbody><tr v-for="design in designs.data" :key="design.id" class="border-t border-slate-200"><td class="px-4 py-3"><Link :href="`/content/physical-designs/${design.id}`" class="text-blue-600">{{ design.name }}</Link></td><td class="px-4 py-3">{{ design.design_type }}</td><td class="px-4 py-3">{{ design.status }}</td><td class="px-4 py-3">{{ design.latest_version_id || '-' }}</td><td class="px-4 py-3">{{ design.updated_at }}</td></tr></tbody></table></div>
      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><Link v-for="design in designs.data" :key="design.id" :href="`/content/physical-designs/${design.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50"><p class="font-semibold">{{ design.name }}</p><p class="text-sm text-slate-600">{{ design.design_type }}</p><p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ design.status }}</p></Link></div>
    </div>
  </AppLayout>
</template>
