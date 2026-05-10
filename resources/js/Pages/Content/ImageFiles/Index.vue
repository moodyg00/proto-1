<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({ images: Object });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><h2 class="text-2xl font-semibold">Image Files</h2><button class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Upload New Image</button></div>
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div v-for="image in images.data" :key="image.id" class="rounded-xl bg-white p-4 shadow-sm">
          <div class="aspect-video overflow-hidden rounded-md bg-slate-100"><img v-if="image.thumbnail_url || image.file_url" :src="image.thumbnail_url || image.file_url" :alt="image.alt_text || image.filename" class="h-full w-full object-cover" /></div>
          <p class="mt-3 truncate text-sm font-medium">{{ image.filename }}</p>
          <p class="text-xs text-slate-600">{{ image.library_type }} · {{ image.created_at }}</p>
          <p class="text-xs text-slate-500">{{ image.alt_text || 'No alt text' }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
