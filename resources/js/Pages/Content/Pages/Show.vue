<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({ page: Object });
const modal = ref(null);

const editForm = useForm({ page_slug: props.page.page_slug, page_title: props.page.page_title, status: props.page.status, meta_title: props.page.meta_title || '', meta_description: props.page.meta_description || '', sections: props.page.sections || [], is_published: props.page.is_published });
const sectionText = useForm({ json: JSON.stringify(props.page.sections || [], null, 2) });

const updatePage = () => {
  try { editForm.sections = JSON.parse(sectionText.json || '[]'); } catch { editForm.sections = []; }
  editForm.put(`/content/pages/${props.page.id}`, { onSuccess: () => (modal.value = null) });
};

const publish = () => router.post(`/content/pages/${props.page.id}/publish`);
const unpublish = () => router.post(`/content/pages/${props.page.id}/unpublish`);
const duplicate = () => router.post(`/content/pages/${props.page.id}/duplicate`);
const remove = () => { if (confirm('Delete this page?')) router.delete(`/content/pages/${props.page.id}`); };
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><div><h2 class="text-2xl font-semibold">{{ page.page_title }}</h2><p class="text-sm text-slate-600">/{{ page.page_slug }}</p></div><div class="flex gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'edit'">Edit</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="publish">Publish</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="unpublish">Unpublish</button><a :href="`/${page.page_slug}`" target="_blank" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Preview</a><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="duplicate">Duplicate</button><button class="rounded-lg border border-red-300 px-3 py-2 text-sm text-red-600" @click="remove">Delete</button></div></div>
      <section class="rounded-xl bg-white p-5 shadow-sm"><div class="grid gap-4 md:grid-cols-3 text-sm"><p><span class="text-xs text-slate-500">Status</span><br>{{ page.status }}</p><p><span class="text-xs text-slate-500">Updated</span><br>{{ page.updated_at }}</p><p><span class="text-xs text-slate-500">Published</span><br>{{ page.is_published ? 'Yes' : 'No' }}</p></div><div class="mt-4 grid gap-2 md:grid-cols-2 text-sm"><p><span class="text-xs text-slate-500">Meta Title</span><br>{{ page.meta_title || '-' }}</p><p><span class="text-xs text-slate-500">Meta Description</span><br>{{ page.meta_description || '-' }}</p></div><div class="mt-4"><p class="text-xs text-slate-500">Sections Preview</p><pre class="mt-2 overflow-auto rounded-md bg-slate-900 p-3 text-xs text-slate-100">{{ JSON.stringify(page.sections || [], null, 2) }}</pre></div></section>
    </div>

    <Modal :open="modal === 'edit'" title="Edit Page" @close="modal = null"><form class="space-y-3" @submit.prevent="updatePage"><input v-model="editForm.page_title" type="text" class="w-full rounded-md border-slate-300" placeholder="Page title" /><input v-model="editForm.page_slug" type="text" class="w-full rounded-md border-slate-300" placeholder="Page slug" /><select v-model="editForm.status" class="w-full rounded-md border-slate-300"><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option></select><input v-model="editForm.meta_title" type="text" class="w-full rounded-md border-slate-300" placeholder="Meta title" /><textarea v-model="editForm.meta_description" rows="2" class="w-full rounded-md border-slate-300" placeholder="Meta description" /><textarea v-model="sectionText.json" rows="6" class="w-full rounded-md border-slate-300 font-mono text-sm" placeholder='Sections JSON' /><button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Save</button></form></Modal>
  </AppLayout>
</template>
