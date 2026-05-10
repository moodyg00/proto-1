<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({ design: Object, versions: Array, linkedProducts: Array, products: Array });
const modal = ref(null);

const editForm = useForm({ name: props.design.name, design_type: props.design.design_type, description: props.design.description || '', dimensions: props.design.dimensions || '', files: props.design.files || [], status: props.design.status });
const editFiles = useForm({ json: JSON.stringify(props.design.files || [], null, 2) });

const versionForm = useForm({ version_number: '', files: [], notes: '', status: 'draft' });
const versionFiles = useForm({ json: '[]' });

const linkProductForm = useForm({ product_id: '', is_default: false });

const updateDesign = () => {
  try { editForm.files = JSON.parse(editFiles.json || '[]'); } catch { editForm.files = []; }
  editForm.put(`/content/physical-designs/${props.design.id}`, { onSuccess: () => (modal.value = null) });
};

const createVersion = () => {
  try { versionForm.files = JSON.parse(versionFiles.json || '[]'); } catch { versionForm.files = []; }
  versionForm.post(`/content/physical-designs/${props.design.id}/create-version`, { onSuccess: () => (modal.value = null) });
};

const linkProduct = () => linkProductForm.post(`/content/physical-designs/${props.design.id}/link-product`, { onSuccess: () => (modal.value = null) });
const approve = () => router.post(`/content/physical-designs/${props.design.id}/approve`);
const archive = () => router.post(`/content/physical-designs/${props.design.id}/archive`);
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between"><div><h2 class="text-2xl font-semibold">{{ design.name }}</h2><p class="text-sm text-slate-600">{{ design.design_type }} · {{ design.status }}</p></div><div class="flex gap-2"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'version'">Create New Version</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'product'">Link to Product</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="approve">Approve Design</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="archive">Archive</button><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'edit'">Edit</button></div></div>
      <section class="rounded-xl bg-white p-5 shadow-sm"><div class="grid gap-4 md:grid-cols-3 text-sm"><p><span class="text-xs text-slate-500">Description</span><br>{{ design.description || '-' }}</p><p><span class="text-xs text-slate-500">Dimensions</span><br>{{ design.dimensions || '-' }}</p><p><span class="text-xs text-slate-500">Latest Version</span><br>{{ design.latest_version_id || '-' }}</p></div><div class="mt-4"><p class="text-xs text-slate-500">Files</p><pre class="mt-2 overflow-auto rounded-md bg-slate-900 p-3 text-xs text-slate-100">{{ JSON.stringify(design.files || [], null, 2) }}</pre></div></section>
      <section class="grid gap-6 lg:grid-cols-2"><div class="rounded-xl bg-white p-4 shadow-sm"><h3 class="mb-3 font-semibold">Versions</h3><div class="space-y-2 text-sm"><div v-for="version in versions" :key="version.id" class="rounded-md border border-slate-200 p-2"><p class="font-medium">v{{ version.version_number }} · {{ version.status }}</p><p class="text-slate-600">{{ version.notes || '-' }}</p></div></div></div><div class="rounded-xl bg-white p-4 shadow-sm"><h3 class="mb-3 font-semibold">Linked Products</h3><div class="space-y-2 text-sm"><div v-for="product in linkedProducts" :key="product.id" class="rounded-md border border-slate-200 p-2">{{ product.product_name || product.product_id }} <span class="text-xs text-slate-500">{{ product.is_default ? '(default)' : '' }}</span></div><p v-if="linkedProducts.length === 0" class="text-slate-500">No linked products.</p></div></div></section>
    </div>

    <Modal :open="modal === 'edit'" title="Edit Design" @close="modal = null"><form class="space-y-3" @submit.prevent="updateDesign"><input v-model="editForm.name" type="text" class="w-full rounded-md border-slate-300" /><select v-model="editForm.design_type" class="w-full rounded-md border-slate-300"><option value="t_shirt">T-Shirt</option><option value="business_card">Business Card</option><option value="flyer">Flyer</option><option value="sticker">Sticker</option><option value="door_hanger">Door Hanger</option><option value="other">Other</option></select><textarea v-model="editForm.description" rows="2" class="w-full rounded-md border-slate-300" /><input v-model="editForm.dimensions" type="text" class="w-full rounded-md border-slate-300" /><textarea v-model="editFiles.json" rows="5" class="w-full rounded-md border-slate-300 font-mono text-sm" /><button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Save</button></form></Modal>

    <Modal :open="modal === 'version'" title="Create New Version" @close="modal = null"><form class="space-y-3" @submit.prevent="createVersion"><input v-model="versionForm.version_number" type="text" class="w-full rounded-md border-slate-300" placeholder="Version number (optional)" /><textarea v-model="versionFiles.json" rows="5" class="w-full rounded-md border-slate-300 font-mono text-sm" placeholder='Files JSON' /><textarea v-model="versionForm.notes" rows="2" class="w-full rounded-md border-slate-300" placeholder="Notes" /><button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Create</button></form></Modal>

    <Modal :open="modal === 'product'" title="Link to Product" @close="modal = null"><form class="space-y-3" @submit.prevent="linkProduct"><select v-model="linkProductForm.product_id" class="w-full rounded-md border-slate-300"><option value="">Select product</option><option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option></select><label class="inline-flex items-center gap-2 text-sm"><input v-model="linkProductForm.is_default" type="checkbox" /> Set as default</label><button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Link</button></form></Modal>
  </AppLayout>
</template>
