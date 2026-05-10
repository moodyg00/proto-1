<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ vendors: Array });

const form = useForm({
  vendor_organization_id: '',
  issue_date: new Date().toISOString().slice(0, 10),
  due_date: '',
  subtotal: 0,
  tax_amount: 0,
  total_amount: 0,
  notes: '',
});

const submit = () => form.post('/accounting/bills');
</script>

<template>
  <AppLayout>
    <!-- Implements Bill Create from accounting-views-and-actions.md -->
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Bill</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <select v-model="form.vendor_organization_id" class="w-full rounded-md border-slate-300">
          <option value="">Select vendor</option>
          <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">{{ vendor.name }}</option>
        </select>
        <div class="grid gap-4 md:grid-cols-2">
          <input v-model="form.issue_date" type="date" class="rounded-md border-slate-300" />
          <input v-model="form.due_date" type="date" class="rounded-md border-slate-300" />
        </div>
        <div class="grid gap-4 md:grid-cols-3">
          <input v-model.number="form.subtotal" type="number" min="0" step="0.01" class="rounded-md border-slate-300" placeholder="Subtotal" />
          <input v-model.number="form.tax_amount" type="number" min="0" step="0.01" class="rounded-md border-slate-300" placeholder="Tax" />
          <input v-model.number="form.total_amount" type="number" min="0" step="0.01" class="rounded-md border-slate-300" placeholder="Total" />
        </div>
        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" placeholder="Notes" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button>
          <Link href="/accounting/bills" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
