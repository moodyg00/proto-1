<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ invoice: Object, lineItems: Array, contacts: Array, organizations: Array });

const form = useForm({
  contact_id: props.invoice.contact_id || '',
  organization_id: props.invoice.organization_id || '',
  issue_date: props.invoice.issue_date || '',
  due_date: props.invoice.due_date || '',
  subtotal: Number(props.invoice.subtotal || 0),
  tax_amount: Number(props.invoice.tax_amount || 0),
  total_amount: Number(props.invoice.total_amount || 0),
  notes: props.invoice.notes || '',
  line_items: props.lineItems?.length ? props.lineItems : [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
});

const recalcTotal = () => {
  const subtotal = form.line_items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
  form.subtotal = subtotal;
  form.total_amount = subtotal + Number(form.tax_amount || 0);
};

const submit = () => {
  form.line_items = form.line_items.map((item) => ({ ...item, total: Number(item.quantity) * Number(item.unit_price) }));
  recalcTotal();
  form.put(`/accounting/invoices/${props.invoice.id}`);
};
</script>

<template>
  <AppLayout>
    <!-- Implements Invoice Edit from accounting-views-and-actions.md -->
    <div class="mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Edit {{ invoice.invoice_number }}</h2>
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

        <div v-for="(item, idx) in form.line_items" :key="idx" class="grid gap-2 md:grid-cols-4">
          <input v-model="item.description" type="text" class="rounded-md border-slate-300" placeholder="Description" />
          <input v-model.number="item.quantity" @input="recalcTotal" type="number" min="1" step="0.01" class="rounded-md border-slate-300" />
          <input v-model.number="item.unit_price" @input="recalcTotal" type="number" min="0" step="0.01" class="rounded-md border-slate-300" />
          <input :value="(Number(item.quantity) * Number(item.unit_price)).toFixed(2)" disabled class="rounded-md border-slate-300 bg-slate-100" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <input v-model.number="form.subtotal" type="number" class="rounded-md border-slate-300" readonly />
          <input v-model.number="form.tax_amount" @input="recalcTotal" type="number" class="rounded-md border-slate-300" />
          <input v-model.number="form.total_amount" type="number" class="rounded-md border-slate-300" readonly />
        </div>

        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Update</button>
          <Link :href="`/accounting/invoices/${invoice.id}`" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
