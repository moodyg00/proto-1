<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ categories: Array });
const form = useForm({ title: '', slug: '', excerpt: '', content: '', category_id: '', category: '', status: 'draft', featured_image_url: '', seo_title: '', seo_description: '', seo_keywords: [], reading_time_minutes: 1 });
const submit = () => form.post('/content/blog-posts');
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Blog Post</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.title" type="text" class="w-full rounded-md border-slate-300" placeholder="Title" />
        <input v-model="form.slug" type="text" class="w-full rounded-md border-slate-300" placeholder="Slug (optional)" />
        <div class="grid gap-4 md:grid-cols-2"><select v-model="form.category_id" class="rounded-md border-slate-300"><option value="">Category</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select><select v-model="form.status" class="rounded-md border-slate-300"><option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="published">Published</option></select></div>
        <textarea v-model="form.excerpt" rows="2" class="w-full rounded-md border-slate-300" placeholder="Excerpt" />
        <textarea v-model="form.content" rows="8" class="w-full rounded-md border-slate-300" placeholder="Content" />
        <input v-model="form.featured_image_url" type="text" class="w-full rounded-md border-slate-300" placeholder="Featured image URL" />
        <div class="grid gap-4 md:grid-cols-2"><input v-model="form.seo_title" type="text" class="rounded-md border-slate-300" placeholder="SEO title" /><input v-model.number="form.reading_time_minutes" type="number" min="0" class="rounded-md border-slate-300" placeholder="Reading time" /></div>
        <textarea v-model="form.seo_description" rows="2" class="w-full rounded-md border-slate-300" placeholder="SEO description" />
        <div class="flex items-center gap-3"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button><Link href="/content/blog-posts" class="text-sm text-slate-600">Cancel</Link></div>
      </form>
    </div>
  </AppLayout>
</template>
