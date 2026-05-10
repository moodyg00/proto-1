<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ accounts: Array });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Bank Accounts</h2>
        <Link href="/banking/accounts/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Add New Account</Link>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <a v-for="account in accounts" :key="account.id" :href="`/banking/transactions?account_id=${account.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <p class="font-semibold">{{ account.name }}</p>
          <p class="text-sm text-slate-600">{{ account.account_type }} · {{ account.bank_name || '-' }}</p>
          <p class="mt-2 text-sm">Current Balance: {{ account.current_balance }}</p>
          <p class="text-xs text-slate-500">Last Reconciled: {{ account.last_reconciled_date || 'Never' }}</p>
        </a>
      </div>
    </div>
  </AppLayout>
</template>
