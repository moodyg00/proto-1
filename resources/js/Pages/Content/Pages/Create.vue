<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const form = useForm({ page_slug: '', page_title: '', status: 'draft', meta_title: '', meta_description: '', sections: [], is_published: false });
const sectionsText = useForm({ json: '[]' });

const submit = () => {
  try {
    form.sections = JSON.parse(sectionsText.json || '[]');
  } catch {
    form.sections = [];
  }
  form.post('/content/pages');
};
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Page</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.page_title" type="text" class="w-full rounded-md border-slate-300" placeholder="Page title" />
        <input v-model="form.page_slug" type="text" class="w-full rounded-md border-slate-300" placeholder="Page slug" />
        <select v-model="form.status" class="w-full rounded-md border-slate-300"><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option></select>
        <input v-model="form.meta_title" type="text" class="w-full rounded-md border-slate-300" placeholder="Meta title" />
        <textarea v-model="form.meta_description" rows="2" class="w-full rounded-md border-slate-300" placeholder="Meta description" />
        <textarea v-model="sectionsText.json" rows="6" class="w-full rounded-md border-slate-300 font-mono text-sm" placeholder='Sections JSON, e.g. [{"type":"hero","content":"..."}]' />
        <label class="inline-flex items-center gap-2 text-sm"><input v-model="form.is_published" type="checkbox" /> Publish now</label>
        <div class="flex items-center gap-3"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button><Link href="/content/pages" class="text-sm text-slate-600">Cancel</Link></div>
      </form>
    </div>
  </AppLayout>
</template>
