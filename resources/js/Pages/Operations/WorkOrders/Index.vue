<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  workOrders: Object,
  filters: Object,
});

const viewMode = ref('card');

const filter = (key, value) => {
  router.get('/operations/work-orders', { ...props.filters, [key]: value || undefined }, { preserveState: true, replace: true });
};
</script>

<template>
  <AppLayout>
    <!-- Implements Work Orders Index from operations-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-slate-900">Work Orders</h2>
        <Link href="/operations/work-orders/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Create New Work Order</Link>
      </div>

      <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-4 shadow-sm">
        <select class="rounded-md border-slate-300" :value="filters.status || ''" @change="filter('status', $event.target.value)">
          <option value="">All statuses</option>
          <option value="new">New</option>
          <option value="scheduled">Scheduled</option>
          <option value="assigned">Assigned</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>

        <input class="rounded-md border-slate-300" type="date" :value="filters.due_date || ''" @change="filter('due_date', $event.target.value)" />
        <input class="rounded-md border-slate-300" type="text" :value="filters.assigned_contractor || ''" placeholder="assigned_contractor" @change="filter('assigned_contractor', $event.target.value)" />

        <div class="ml-auto flex gap-2">
          <button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'card' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" @click="viewMode = 'card'">Card</button>
          <button class="rounded-md px-3 py-1.5 text-sm" :class="viewMode === 'table' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" @click="viewMode = 'table'">Table</button>
        </div>
      </div>

      <div v-if="viewMode === 'card'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link v-for="row in workOrders.data" :key="row.id" :href="`/operations/work-orders/${row.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <p class="font-semibold text-slate-900">{{ row.work_order_number }}</p>
          <p class="text-sm text-slate-600">{{ row.customer_name }}</p>
          <p class="mt-2 text-xs text-slate-500">{{ row.status }} · {{ row.booking_date || 'No booking' }}</p>
          <p class="text-xs text-slate-500">{{ row.assigned_contractor || 'Unassigned' }}</p>
          <p class="text-xs text-slate-500">{{ row.invoice_number || 'No invoice' }}</p>
        </Link>
      </div>

      <div v-else class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-600">
            <tr>
              <th class="px-4 py-3">Work Order</th>
              <th class="px-4 py-3">Customer</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Booking</th>
              <th class="px-4 py-3">Contractor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in workOrders.data" :key="row.id" class="border-t border-slate-200">
              <td class="px-4 py-3"><Link class="text-blue-600" :href="`/operations/work-orders/${row.id}`">{{ row.work_order_number }}</Link></td>
              <td class="px-4 py-3">{{ row.customer_name }}</td>
              <td class="px-4 py-3">{{ row.status }}</td>
              <td class="px-4 py-3">{{ row.booking_date || '-' }}</td>
              <td class="px-4 py-3">{{ row.assigned_contractor || 'Unassigned' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
