<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ contacts: Array, organizations: Array });

const form = useForm({
  contact_id: '',
  organization_id: '',
  issue_date: new Date().toISOString().slice(0, 10),
  due_date: '',
  subtotal: 0,
  tax_amount: 0,
  total_amount: 0,
  notes: '',
  line_items: [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
});

const recalcTotal = () => {
  const subtotal = form.line_items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
  form.subtotal = subtotal;
  form.total_amount = subtotal + Number(form.tax_amount || 0);
};

const addLineItem = () => form.line_items.push({ description: '', quantity: 1, unit_price: 0, total: 0 });

const submit = () => {
  form.line_items = form.line_items.map((item) => ({ ...item, total: Number(item.quantity) * Number(item.unit_price) }));
  recalcTotal();
  form.post('/accounting/invoices');
};
</script>

<template>
  <AppLayout>
    <!-- Implements Invoice Create from accounting-views-and-actions.md -->
    <div class="mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Invoice</h2>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div class="grid gap-4 md:grid-cols-2">
          <select v-model="form.contact_id" class="rounded-md border-slate-300">
            <option value="">Select contact</option>
            <option v-for="contact in contacts" :key="contact.id" :value="contact.id">{{ contact.name }}</option>
          </select>

          <select v-model="form.organization_id" class="rounded-md border-slate-300">
            <option value="">Select organization</option>
            <option v-for="org in organizations" :key="org.id" :value="org.id">{{ org.name }}</option>
          </select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <input v-model="form.issue_date" type="date" class="rounded-md border-slate-300" />
          <input v-model="form.due_date" type="date" class="rounded-md border-slate-300" />
        </div>

        <div class="space-y-3 rounded-lg border border-slate-200 p-3">
          <div v-for="(item, idx) in form.line_items" :key="idx" class="grid gap-2 md:grid-cols-4">
            <input v-model="item.description" type="text" class="rounded-md border-slate-300" placeholder="Description" />
            <input v-model.number="item.quantity" @input="recalcTotal" type="number" min="1" step="0.01" class="rounded-md border-slate-300" placeholder="Qty" />
            <input v-model.number="item.unit_price" @input="recalcTotal" type="number" min="0" step="0.01" class="rounded-md border-slate-300" placeholder="Unit Price" />
            <input :value="(Number(item.quantity) * Number(item.unit_price)).toFixed(2)" disabled class="rounded-md border-slate-300 bg-slate-100" />
          </div>
          <button type="button" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" @click="addLineItem">Add row</button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <input v-model.number="form.subtotal" type="number" class="rounded-md border-slate-300" placeholder="Subtotal" readonly />
          <input v-model.number="form.tax_amount" @input="recalcTotal" type="number" min="0" step="0.01" class="rounded-md border-slate-300" placeholder="Tax" />
          <input v-model.number="form.total_amount" type="number" class="rounded-md border-slate-300" placeholder="Total" readonly />
        </div>

        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" placeholder="Notes" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button>
          <Link href="/accounting/invoices" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
