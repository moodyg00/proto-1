<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({ invoices: Object, filters: Object });
const viewMode = ref('table');

const filter = (status) => {
  router.get('/accounting/invoices', { ...props.filters, status: status || undefined }, { preserveState: true, replace: true });
};
</script>

<template>
  <AppLayout>
    <!-- Implements Invoices Index from accounting-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Invoices</h2>
        <Link href="/accounting/invoices/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Invoice</Link>
      </div>

      <div class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm">
        <select class="rounded-md border-slate-300" :value="filters.status || ''" @change="filter($event.target.value)">
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="partial">Partial</option>
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
              <th class="px-4 py-3">Invoice #</th>
              <th class="px-4 py-3">Contact</th>
              <th class="px-4 py-3">Issue Date</th>
              <th class="px-4 py-3">Due Date</th>
              <th class="px-4 py-3">Total</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Paid</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices.data" :key="invoice.id" class="border-t border-slate-200">
              <td class="px-4 py-3"><Link :href="`/accounting/invoices/${invoice.id}`" class="text-blue-600">{{ invoice.invoice_number }}</Link></td>
              <td class="px-4 py-3">{{ invoice.contact_name || '-' }}</td>
              <td class="px-4 py-3">{{ invoice.issue_date }}</td>
              <td class="px-4 py-3">{{ invoice.due_date }}</td>
              <td class="px-4 py-3">{{ invoice.total_amount }}</td>
              <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs" :class="invoice.status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'">{{ invoice.status }}</span></td>
              <td class="px-4 py-3">{{ invoice.amount_paid }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link v-for="invoice in invoices.data" :key="invoice.id" :href="`/accounting/invoices/${invoice.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <p class="font-semibold">{{ invoice.invoice_number }}</p>
          <p class="text-sm text-slate-600">{{ invoice.contact_name || '-' }}</p>
          <p class="mt-2 text-sm">Due: {{ invoice.due_date }}</p>
          <p class="text-sm">Total: {{ invoice.total_amount }}</p>
          <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ invoice.status }}</p>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
