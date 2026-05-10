<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ bill: Object, vendors: Array });

const form = useForm({
  vendor_organization_id: props.bill.vendor_organization_id || '',
  issue_date: props.bill.issue_date || '',
  due_date: props.bill.due_date || '',
  subtotal: Number(props.bill.subtotal || 0),
  tax_amount: Number(props.bill.tax_amount || 0),
  total_amount: Number(props.bill.total_amount || 0),
  notes: props.bill.notes || '',
});

const submit = () => form.put(`/accounting/bills/${props.bill.id}`);
</script>

<template>
  <AppLayout>
    <!-- Implements Bill Edit from accounting-views-and-actions.md -->
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Edit {{ bill.bill_number }}</h2>
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
          <input v-model.number="form.subtotal" type="number" class="rounded-md border-slate-300" />
          <input v-model.number="form.tax_amount" type="number" class="rounded-md border-slate-300" />
          <input v-model.number="form.total_amount" type="number" class="rounded-md border-slate-300" />
        </div>
        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Update</button>
          <Link :href="`/accounting/bills/${bill.id}`" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
