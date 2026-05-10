<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const form = useForm({ name: '', design_type: 't_shirt', description: '', dimensions: '', files: [], status: 'draft' });
const filesText = useForm({ json: '[]' });

const submit = () => {
  try { form.files = JSON.parse(filesText.json || '[]'); } catch { form.files = []; }
  form.post('/content/physical-designs');
};
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Physical Design</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.name" type="text" class="w-full rounded-md border-slate-300" placeholder="Design name" />
        <div class="grid gap-4 md:grid-cols-2"><select v-model="form.design_type" class="rounded-md border-slate-300"><option value="t_shirt">T-Shirt</option><option value="business_card">Business Card</option><option value="flyer">Flyer</option><option value="sticker">Sticker</option><option value="door_hanger">Door Hanger</option><option value="other">Other</option></select><input v-model="form.dimensions" type="text" class="rounded-md border-slate-300" placeholder="Dimensions" /></div>
        <textarea v-model="form.description" rows="3" class="w-full rounded-md border-slate-300" placeholder="Description" />
        <textarea v-model="filesText.json" rows="6" class="w-full rounded-md border-slate-300 font-mono text-sm" placeholder='Files JSON, e.g. [{"name":"design.pdf","url":"..."}]' />
        <div class="flex items-center gap-3"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button><Link href="/content/physical-designs" class="text-sm text-slate-600">Cancel</Link></div>
      </form>
    </div>
  </AppLayout>
</template>
