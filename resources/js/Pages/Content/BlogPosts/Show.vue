<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({ post: Object, categories: Array });
const modal = ref(null);

const editForm = useForm({ title: props.post.title, slug: props.post.slug, excerpt: props.post.excerpt || '', content: props.post.content, category_id: props.post.category_id || '', category: props.post.category || '', status: props.post.status, featured_image_url: props.post.featured_image_url || '', seo_title: props.post.seo_title || '', seo_description: props.post.seo_description || '', seo_keywords: props.post.seo_keywords || [], reading_time_minutes: props.post.reading_time_minutes || 0 });
const publishForm = useForm({ published_at: props.post.published_at ? props.post.published_at.slice(0, 16) : '' });

const updatePost = () => editForm.put(`/content/blog-posts/${props.post.id}`, { onSuccess: () => (modal.value = null) });
const publishOrSchedule = () => publishForm.post(`/content/blog-posts/${props.post.id}/publish`, { onSuccess: () => (modal.value = null) });
const unpublish = () => router.post(`/content/blog-posts/${props.post.id}/unpublish`);
const duplicate = () => router.post(`/content/blog-posts/${props.post.id}/duplicate`);
const remove = () => { if (confirm('Delete this post?')) router.delete(`/content/blog-posts/${props.post.id}`); };
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><div><h2 class="text-2xl font-semibold">{{ post.title }}</h2><p class="text-sm text-slate-600">/{{ post.slug }}</p></div><div class="flex gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'edit'">Edit</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'publish'">Publish / Schedule</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="unpublish">Unpublish</button><a :href="`/blog/${post.slug}`" target="_blank" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Preview</a><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="duplicate">Duplicate</button><button class="rounded-lg border border-red-300 px-3 py-2 text-sm text-red-600" @click="remove">Delete</button></div></div>
      <section class="rounded-xl bg-white p-5 shadow-sm"><div class="grid gap-4 md:grid-cols-3 text-sm"><p><span class="text-xs text-slate-500">Category</span><br>{{ post.category || '-' }}</p><p><span class="text-xs text-slate-500">Status</span><br>{{ post.status }}</p><p><span class="text-xs text-slate-500">Published</span><br>{{ post.published_at || '-' }}</p><p><span class="text-xs text-slate-500">View Count</span><br>{{ post.view_count }}</p><p><span class="text-xs text-slate-500">Reading Time</span><br>{{ post.reading_time_minutes || 0 }} min</p></div><p class="mt-4 text-sm"><span class="text-xs text-slate-500">Excerpt</span><br>{{ post.excerpt || '-' }}</p><div class="mt-4"><p class="text-xs text-slate-500">Content Preview</p><div class="prose mt-2 max-w-none text-sm" v-html="post.content" /></div><div class="mt-4 grid gap-2 md:grid-cols-3 text-sm"><p><span class="text-xs text-slate-500">SEO Title</span><br>{{ post.seo_title || '-' }}</p><p><span class="text-xs text-slate-500">SEO Description</span><br>{{ post.seo_description || '-' }}</p><p><span class="text-xs text-slate-500">SEO Keywords</span><br>{{ Array.isArray(post.seo_keywords) ? post.seo_keywords.join(', ') : '-' }}</p></div></section>
    </div>

    <Modal :open="modal === 'edit'" title="Edit Blog Post" @close="modal = null"><form class="space-y-3" @submit.prevent="updatePost"><input v-model="editForm.title" type="text" class="w-full rounded-md border-slate-300" placeholder="Title" /><input v-model="editForm.slug" type="text" class="w-full rounded-md border-slate-300" placeholder="Slug" /><select v-model="editForm.category_id" class="w-full rounded-md border-slate-300"><option value="">Category</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select><textarea v-model="editForm.excerpt" rows="2" class="w-full rounded-md border-slate-300" placeholder="Excerpt" /><textarea v-model="editForm.content" rows="6" class="w-full rounded-md border-slate-300" placeholder="Content" /><input v-model="editForm.featured_image_url" type="text" class="w-full rounded-md border-slate-300" placeholder="Featured image URL" /><button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Save</button></form></Modal>

    <Modal :open="modal === 'publish'" title="Publish or Schedule" @close="modal = null"><form class="space-y-3" @submit.prevent="publishOrSchedule"><input v-model="publishForm.published_at" type="datetime-local" class="w-full rounded-md border-slate-300" /><button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Apply</button></form></Modal>
  </AppLayout>
</template>
