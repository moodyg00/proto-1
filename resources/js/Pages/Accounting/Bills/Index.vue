<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ bills: Object, filters: Object });
const viewMode = ref('table');

const filter = (status) => {
  router.get('/accounting/bills', { ...props.filters, status: status || undefined }, { preserveState: true, replace: true });
};
</script>

<template>
  <AppLayout>
    <!-- Implements Bills Index from accounting-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Bills</h2>
        <Link href="/accounting/bills/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Bill</Link>
      </div>

      <div class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm">
        <select class="rounded-md border-slate-300" :value="filters.status || ''" @change="filter($event.target.value)">
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="received">Received</option>
          <option value="approved">Approved</option>
          <option value="paid">Paid</option>
          <option value="overdue">Overdue</option>
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
              <th class="px-4 py-3">Bill #</th>
              <th class="px-4 py-3">Vendor</th>
              <th class="px-4 py-3">Issue</th>
              <th class="px-4 py-3">Due</th>
              <th class="px-4 py-3">Total</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="bill in bills.data" :key="bill.id" class="border-t border-slate-200">
              <td class="px-4 py-3"><Link :href="`/accounting/bills/${bill.id}`" class="text-blue-600">{{ bill.bill_number }}</Link></td>
              <td class="px-4 py-3">{{ bill.vendor_name }}</td>
              <td class="px-4 py-3">{{ bill.issue_date }}</td>
              <td class="px-4 py-3">{{ bill.due_date }}</td>
              <td class="px-4 py-3">{{ bill.total_amount }}</td>
              <td class="px-4 py-3">{{ bill.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link v-for="bill in bills.data" :key="bill.id" :href="`/accounting/bills/${bill.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <p class="font-semibold">{{ bill.bill_number }}</p>
          <p class="text-sm text-slate-600">{{ bill.vendor_name }}</p>
          <p class="mt-2 text-sm">Due: {{ bill.due_date }}</p>
          <p class="text-sm">Total: {{ bill.total_amount }}</p>
          <p class="text-xs uppercase tracking-wide text-slate-500">{{ bill.status }}</p>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
