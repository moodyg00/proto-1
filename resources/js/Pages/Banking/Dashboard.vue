<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { ref } from 'vue';

defineProps({
  stats: Object,
  recentTransactions: Array,
  accountsOverview: Array,
  reconciliationAlerts: Array,
  quickLinks: Array,
});

const viewMode = ref('table');
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold text-slate-900">Banking Dashboard</h2>

      <section class="grid gap-4 md:grid-cols-5">
        <div v-for="(value, label) in stats" :key="label" class="rounded-xl bg-white p-4 shadow-sm">
          <p class="text-xs uppercase tracking-wide text-slate-500">{{ label }}</p>
          <p class="mt-2 text-xl font-semibold text-slate-900">{{ value }}</p>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold">Recent Transactions</h3>
          <div class="flex gap-2">
            <button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'table'">Table</button>
            <button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100'" @click="viewMode = 'card'">Card</button>
          </div>
        </div>

        <div v-if="viewMode === 'table'" class="overflow-hidden rounded-lg border border-slate-200">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
              <tr>
                <th class="px-3 py-2">Date</th>
                <th class="px-3 py-2">Description</th>
                <th class="px-3 py-2">Account</th>
                <th class="px-3 py-2">Amount</th>
                <th class="px-3 py-2">Category</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in recentTransactions" :key="transaction.id" class="border-t border-slate-200">
                <td class="px-3 py-2">{{ transaction.transaction_date }}</td>
                <td class="px-3 py-2"><a :href="`/banking/transactions/${transaction.id}`" class="text-blue-600">{{ transaction.description || '-' }}</a></td>
                <td class="px-3 py-2">{{ transaction.account_name || '-' }}</td>
                <td class="px-3 py-2" :class="Number(transaction.amount) >= 0 ? 'text-emerald-700' : 'text-red-600'">{{ transaction.amount }}</td>
                <td class="px-3 py-2">{{ transaction.internal_category || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
          <a v-for="transaction in recentTransactions" :key="transaction.id" :href="`/banking/transactions/${transaction.id}`" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50">
            <p class="text-xs text-slate-500">{{ transaction.transaction_date }}</p>
            <p class="font-medium">{{ transaction.description || '-' }}</p>
            <p class="text-sm text-slate-600">{{ transaction.account_name || '-' }}</p>
            <p class="mt-1 text-sm" :class="Number(transaction.amount) >= 0 ? 'text-emerald-700' : 'text-red-600'">{{ transaction.amount }}</p>
          </a>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Accounts Overview</h3>
          <div class="space-y-2 text-sm">
            <a v-for="account in accountsOverview" :key="account.id" :href="`/banking/transactions?account_id=${account.id}`" class="flex items-center justify-between rounded-md border border-slate-200 p-2 hover:bg-slate-50">
              <span>{{ account.name }} ({{ account.account_type }})</span>
              <span>{{ account.current_balance }}</span>
            </a>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Reconciliation Alerts</h3>
          <div class="space-y-2 text-sm">
            <div v-for="account in reconciliationAlerts" :key="account.id" class="flex items-center justify-between rounded-md border border-amber-200 bg-amber-50 p-2">
              <div>
                <p class="font-medium">{{ account.name }}</p>
                <p class="text-xs text-slate-600">Last reconciled: {{ account.last_reconciled_date || 'Never' }}</p>
              </div>
              <a :href="`/banking/reconciliations/create?account_id=${account.id}`" class="rounded-md border border-amber-300 px-2 py-1 text-xs">Quick reconcile</a>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <h3 class="mb-3 font-semibold">Quick Links</h3>
        <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
          <a v-for="link in quickLinks" :key="link.href" :href="link.href" class="rounded-md border border-slate-200 p-3 text-sm hover:bg-slate-50">{{ link.label }}</a>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
