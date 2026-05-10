<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ posts: Object, filters: Object });
const viewMode = ref('table');
const applyStatus = (status) => router.get('/content/blog-posts', { ...props.filters, status: status || undefined }, { preserveState: true, replace: true });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><h2 class="text-2xl font-semibold">Blog Posts</h2><Link href="/content/blog-posts/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Post</Link></div>
      <div class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm"><select class="rounded-md border-slate-300" :value="filters.status || ''" @change="applyStatus($event.target.value)"><option value="">All status</option><option value="draft">Draft</option><option value="published">Published</option><option value="scheduled">Scheduled</option><option value="archived">Archived</option></select><div class="ml-auto flex gap-2"><button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'table'">Table</button><button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'card'">Card</button></div></div>

      <div v-if="viewMode === 'table'" class="overflow-hidden rounded-xl bg-white shadow-sm"><table class="min-w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Author</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Published</th><th class="px-4 py-3">Views</th></tr></thead><tbody><tr v-for="post in posts.data" :key="post.id" class="border-t border-slate-200"><td class="px-4 py-3"><Link :href="`/content/blog-posts/${post.id}`" class="text-blue-600">{{ post.title }}</Link></td><td class="px-4 py-3">{{ post.category || '-' }}</td><td class="px-4 py-3">{{ post.author_name || '-' }}</td><td class="px-4 py-3">{{ post.status }}</td><td class="px-4 py-3">{{ post.published_at || '-' }}</td><td class="px-4 py-3">{{ post.view_count }}</td></tr></tbody></table></div>
      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"><Link v-for="post in posts.data" :key="post.id" :href="`/content/blog-posts/${post.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50"><p class="font-semibold">{{ post.title }}</p><p class="text-sm text-slate-600">{{ post.category || '-' }}</p><p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ post.status }}</p></Link></div>
    </div>
  </AppLayout>
</template>
