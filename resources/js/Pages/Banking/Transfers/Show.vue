<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({ transfer: Object, fromAccount: Object, toAccount: Object });
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Transfer {{ transfer.transfer_number }}</h2>
        <Link href="/banking/transfers" class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Back</Link>
      </div>

      <section class="rounded-xl bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3 text-sm">
          <p><span class="text-xs text-slate-500">From Account</span><br>{{ fromAccount?.name || '-' }}</p>
          <p><span class="text-xs text-slate-500">To Account</span><br>{{ toAccount?.name || '-' }}</p>
          <p><span class="text-xs text-slate-500">Amount</span><br>{{ transfer.amount }}</p>
          <p><span class="text-xs text-slate-500">Transfer Date</span><br>{{ transfer.transfer_date }}</p>
          <p><span class="text-xs text-slate-500">Status</span><br>{{ transfer.status }}</p>
          <p><span class="text-xs text-slate-500">Description</span><br>{{ transfer.description || '-' }}</p>
        </div>
        <p class="mt-4 text-sm"><span class="text-xs text-slate-500">Notes</span><br>{{ transfer.notes || '-' }}</p>
      </section>
    </div>
  </AppLayout>
</template>
