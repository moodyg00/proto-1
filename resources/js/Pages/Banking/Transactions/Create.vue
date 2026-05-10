<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ accounts: Array });

const form = useForm({
  bank_account_id: '',
  transaction_date: new Date().toISOString().slice(0, 10),
  amount: '',
  transaction_type: 'withdrawal',
  description: '',
  reference: '',
  external_category: '',
  internal_category: '',
  category_source: 'manual',
  notes: '',
});

const submit = () => form.post('/banking/transactions');
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Record Manual Transaction</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <select v-model="form.bank_account_id" class="w-full rounded-md border-slate-300">
          <option value="">Select account</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
        </select>

        <div class="grid gap-4 md:grid-cols-3">
          <input v-model="form.transaction_date" type="date" class="rounded-md border-slate-300" />
          <input v-model.number="form.amount" type="number" step="0.01" class="rounded-md border-slate-300" placeholder="Amount" />
          <select v-model="form.transaction_type" class="rounded-md border-slate-300">
            <option value="deposit">Deposit</option>
            <option value="withdrawal">Withdrawal</option>
            <option value="transfer_in">Transfer In</option>
            <option value="transfer_out">Transfer Out</option>
            <option value="fee">Fee</option>
            <option value="interest">Interest</option>
            <option value="other">Other</option>
          </select>
        </div>

        <input v-model="form.description" type="text" class="w-full rounded-md border-slate-300" placeholder="Description" />
        <input v-model="form.reference" type="text" class="w-full rounded-md border-slate-300" placeholder="Reference" />

        <div class="grid gap-4 md:grid-cols-2">
          <input v-model="form.external_category" type="text" class="rounded-md border-slate-300" placeholder="External category" />
          <input v-model="form.internal_category" type="text" class="rounded-md border-slate-300" placeholder="Internal category" />
        </div>

        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" placeholder="Notes" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button>
          <Link href="/banking/transactions" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
