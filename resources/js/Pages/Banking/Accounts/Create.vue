<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const form = useForm({
  name: '',
  account_type: 'checking',
  bank_name: '',
  account_number: '',
  currency: 'USD',
  current_balance: 0,
  is_active: true,
});

const submit = () => form.post('/banking/accounts');
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Add Bank Account</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.name" type="text" class="w-full rounded-md border-slate-300" placeholder="Account name" />

        <div class="grid gap-4 md:grid-cols-2">
          <select v-model="form.account_type" class="rounded-md border-slate-300">
            <option value="checking">Checking</option>
            <option value="savings">Savings</option>
            <option value="cash">Cash</option>
            <option value="credit_card">Credit Card</option>
            <option value="other">Other</option>
          </select>
          <input v-model="form.bank_name" type="text" class="rounded-md border-slate-300" placeholder="Bank name" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <input v-model="form.account_number" type="text" class="rounded-md border-slate-300" placeholder="Account number" />
          <input v-model="form.currency" type="text" class="rounded-md border-slate-300" placeholder="Currency" />
          <input v-model.number="form.current_balance" type="number" step="0.01" class="rounded-md border-slate-300" placeholder="Current balance" />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
          <input v-model="form.is_active" type="checkbox" />
          Active account
        </label>

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button>
          <Link href="/banking/accounts" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
