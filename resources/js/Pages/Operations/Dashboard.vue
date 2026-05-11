<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const adminJobsUrl = '/admin/jobs?view_type=table';

const adminJobSearchUrl = (workOrderNumber) => `/admin/jobs?view_type=table&tableSearch=${encodeURIComponent(workOrderNumber)}`;

defineProps({
  stats: Object,
  workOrders: Array,
  operationsTickets: Array,
  quickLinks: Array,
});
</script>

<template>
  <AppLayout>
    <!-- Implements Operations Dashboard from operations-views-and-actions.md -->
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold text-slate-900">Operations Dashboard</h2>

      <section class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm" v-for="(value, label) in stats" :key="label">
          <p class="text-xs uppercase tracking-wide text-slate-500">{{ label }}</p>
          <p class="mt-2 text-2xl font-semibold text-slate-900">{{ value }}</p>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="mb-3 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Work Orders</h3>
            <Link :href="adminJobsUrl" class="text-sm text-blue-600">View all</Link>
          </div>
          <div class="space-y-2">
            <Link
              v-for="workOrder in workOrders"
              :key="workOrder.id"
              :href="adminJobSearchUrl(workOrder.work_order_number)"
              class="block rounded-lg border border-slate-200 p-3 hover:bg-slate-50"
            >
              <p class="text-sm font-medium">{{ workOrder.work_order_number }} · {{ workOrder.customer_name }}</p>
              <p class="text-xs text-slate-500">Status: {{ workOrder.status }} | Contractor: {{ workOrder.assigned_contractor || 'Unassigned' }}</p>
            </Link>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="mb-3 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Operations Tickets</h3>
            <a href="/crm/dashboard" class="text-sm text-blue-600">CRM</a>
          </div>
          <div class="space-y-2">
            <div v-for="ticket in operationsTickets" :key="ticket.id" class="rounded-lg border border-slate-200 p-3">
              <p class="text-sm font-medium">{{ ticket.ticket_number }} · {{ ticket.title }}</p>
              <p class="text-xs text-slate-500">Priority: {{ ticket.priority }} | Status: {{ ticket.status }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <h3 class="mb-3 font-semibold text-slate-900">Quick Links</h3>
        <div class="grid gap-3 md:grid-cols-3">
          <a v-for="link in quickLinks" :key="link.href" :href="link.href" class="rounded-lg border border-slate-200 p-3 text-sm hover:bg-slate-50">
            {{ link.label }}
          </a>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
