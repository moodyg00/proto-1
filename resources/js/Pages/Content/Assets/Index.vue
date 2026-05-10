<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({ assets: Object });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><h2 class="text-2xl font-semibold">Assets</h2><button class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Upload New Asset</button></div>
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a v-for="asset in assets.data" :key="asset.id" :href="asset.file_url" target="_blank" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <div class="aspect-video overflow-hidden rounded-md bg-slate-100"><img v-if="asset.file_url" :src="asset.file_url" :alt="asset.alt_text || asset.filename" class="h-full w-full object-contain" /></div>
          <p class="mt-3 truncate text-sm font-medium">{{ asset.filename }}</p>
          <p class="text-xs text-slate-600">{{ asset.mime_type }} · {{ asset.created_at }}</p>
          <p class="text-xs text-slate-500">{{ Array.isArray(asset.tags) ? asset.tags.join(', ') : '-' }}</p>
        </a>
      </div>
    </div>
  </AppLayout>
</template>
