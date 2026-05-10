<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ reconciliations: Object });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Bank Reconciliations</h2>
        <Link href="/banking/reconciliations/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Start New Reconciliation</Link>
      </div>

      <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
            <tr>
              <th class="px-4 py-3">Bank Account</th>
              <th class="px-4 py-3">Statement Date</th>
              <th class="px-4 py-3">Statement Balance</th>
              <th class="px-4 py-3">Book Balance</th>
              <th class="px-4 py-3">Difference</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="reconciliation in reconciliations.data" :key="reconciliation.id" class="border-t border-slate-200">
              <td class="px-4 py-3"><Link :href="`/banking/reconciliations/${reconciliation.id}`" class="text-blue-600">{{ reconciliation.account_name || '-' }}</Link></td>
              <td class="px-4 py-3">{{ reconciliation.statement_date }}</td>
              <td class="px-4 py-3">{{ reconciliation.statement_balance }}</td>
              <td class="px-4 py-3">{{ reconciliation.book_balance }}</td>
              <td class="px-4 py-3">{{ reconciliation.difference }}</td>
              <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ reconciliation.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
