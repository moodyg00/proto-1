<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ accounts: Array });

const form = useForm({
  from_account_id: '',
  to_account_id: '',
  amount: '',
  transfer_date: new Date().toISOString().slice(0, 10),
  status: 'completed',
  description: '',
  notes: '',
});

const submit = () => form.post('/banking/transfers');
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Bank Transfer</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div class="grid gap-4 md:grid-cols-2">
          <select v-model="form.from_account_id" class="rounded-md border-slate-300">
            <option value="">From account</option>
            <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
          </select>
          <select v-model="form.to_account_id" class="rounded-md border-slate-300">
            <option value="">To account</option>
            <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
          </select>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <input v-model.number="form.amount" type="number" step="0.01" class="rounded-md border-slate-300" placeholder="Amount" />
          <input v-model="form.transfer_date" type="date" class="rounded-md border-slate-300" />
          <select v-model="form.status" class="rounded-md border-slate-300">
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <input v-model="form.description" type="text" class="w-full rounded-md border-slate-300" placeholder="Description" />
        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border-slate-300" placeholder="Notes" />

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button>
          <Link href="/banking/transfers" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
