<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ accounts: Array });

const form = useForm({
  bank_account_id: '',
  statement_date: new Date().toISOString().slice(0, 10),
  statement_balance: 0,
  book_balance: 0,
  difference: 0,
  notes: '',
});

const setAccountBalance = (accountId) => {
  const account = props.accounts.find((item) => item.id === accountId);
  form.book_balance = Number(account?.current_balance || 0);
  form.difference = Number(form.statement_balance || 0) - Number(form.book_balance || 0);
};

const updateDifference = () => {
  form.difference = Number(form.statement_balance || 0) - Number(form.book_balance || 0);
};

const submit = () => form.post('/banking/reconciliations');
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Start New Reconciliation</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <select v-model="form.bank_account_id" class="w-full rounded-md border-slate-300" @change="setAccountBalance($event.target.value)">
          <option value="">Select bank account</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
        </select>

        <div class="grid gap-4 md:grid-cols-2">
          <input v-model="form.statement_date" type="date" class="rounded-md border-slate-300" />
          <input v-model.number="form.statement_balance" @input="updateDifference" type="number" step="0.01" class="rounded-md border-slate-300" placeholder="Statement balance" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <input v-model.number="form.book_balance" @input="updateDifference" type="number" step="0.01" class="rounded-md border-slate-300" placeholder="Book balance" />
          <input v-model.number="form.difference" type="number" step="0.01" class="rounded-md border-slate-300" placeholder="Difference" readonly />
        </div>

        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" placeholder="Notes" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Start</button>
          <Link href="/banking/reconciliations" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
