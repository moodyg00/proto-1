<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ transactions: Object, accounts: Array, filters: Object });
const viewMode = ref('table');

const applyFilters = (status, accountId) => {
  router.get('/banking/transactions', {
    ...props.filters,
    status: status || undefined,
    account_id: accountId || undefined,
  }, { preserveState: true, replace: true });
};
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Bank Transactions</h2>
        <Link href="/banking/transactions/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Record Manual Transaction</Link>
      </div>

      <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-4 shadow-sm">
        <select class="rounded-md border-slate-300" :value="filters.status || ''" @change="applyFilters($event.target.value, filters.account_id)">
          <option value="">All status</option>
          <option value="pending">Pending</option>
          <option value="categorized">Categorized</option>
          <option value="reconciled">Reconciled</option>
        </select>

        <select class="rounded-md border-slate-300" :value="filters.account_id || ''" @change="applyFilters(filters.status, $event.target.value)">
          <option value="">All accounts</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
        </select>

        <div class="ml-auto flex gap-2">
          <button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'table'">Table</button>
          <button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'card'">Card</button>
        </div>
      </div>

      <div v-if="viewMode === 'table'" class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
            <tr>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Description</th>
              <th class="px-4 py-3">Amount</th>
              <th class="px-4 py-3">Type</th>
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in transactions.data" :key="transaction.id" class="border-t border-slate-200">
              <td class="px-4 py-3">{{ transaction.transaction_date }}</td>
              <td class="px-4 py-3"><Link :href="`/banking/transactions/${transaction.id}`" class="text-blue-600">{{ transaction.description || '-' }}</Link></td>
              <td class="px-4 py-3" :class="Number(transaction.amount) >= 0 ? 'text-emerald-700' : 'text-red-600'">{{ transaction.amount }}</td>
              <td class="px-4 py-3">{{ transaction.transaction_type }}</td>
              <td class="px-4 py-3">{{ transaction.internal_category || '-' }}</td>
              <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ transaction.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link v-for="transaction in transactions.data" :key="transaction.id" :href="`/banking/transactions/${transaction.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <p class="text-xs text-slate-500">{{ transaction.transaction_date }}</p>
          <p class="font-semibold">{{ transaction.description || '-' }}</p>
          <p class="text-sm">{{ transaction.account_name || '-' }}</p>
          <p class="mt-2 text-sm" :class="Number(transaction.amount) >= 0 ? 'text-emerald-700' : 'text-red-600'">{{ transaction.amount }}</p>
          <p class="text-xs uppercase tracking-wide text-slate-500">{{ transaction.internal_category || '-' }}</p>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
