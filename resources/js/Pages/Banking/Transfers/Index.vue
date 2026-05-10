<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ transfers: Object });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Bank Transfers</h2>
        <Link href="/banking/transfers/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Transfer</Link>
      </div>

      <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
            <tr>
              <th class="px-4 py-3">Transfer #</th>
              <th class="px-4 py-3">From</th>
              <th class="px-4 py-3">To</th>
              <th class="px-4 py-3">Amount</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transfer in transfers.data" :key="transfer.id" class="border-t border-slate-200">
              <td class="px-4 py-3"><Link :href="`/banking/transfers/${transfer.id}`" class="text-blue-600">{{ transfer.transfer_number }}</Link></td>
              <td class="px-4 py-3">{{ transfer.from_account_name || '-' }}</td>
              <td class="px-4 py-3">{{ transfer.to_account_name || '-' }}</td>
              <td class="px-4 py-3">{{ transfer.amount }}</td>
              <td class="px-4 py-3">{{ transfer.transfer_date }}</td>
              <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ transfer.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
