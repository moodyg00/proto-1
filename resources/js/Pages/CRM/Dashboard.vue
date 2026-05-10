<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  stats: Object,
  leads: Array,
  opportunities: Array,
  tickets: Array,
  quickLinks: Array,
});
</script>

<template>
  <AppLayout>
    <!-- Implements CRM Dashboard from crm-views-and-actions.md -->
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold text-slate-900">CRM Dashboard</h2>

      <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-xl bg-white p-4 shadow-sm" v-for="(value, label) in stats" :key="label">
          <p class="text-xs uppercase text-slate-500">{{ label }}</p>
          <p class="mt-2 text-xl font-semibold">{{ value }}</p>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Recent Leads</h3>
          <Link v-for="lead in leads" :key="lead.id" :href="`/crm/leads/${lead.id}`" class="mb-2 block rounded-md border border-slate-200 p-2 text-sm hover:bg-slate-50">
            {{ lead.name }} · {{ lead.source || 'Unknown source' }} · {{ lead.status }}
          </Link>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Open Opportunities</h3>
          <div v-for="opportunity in opportunities" :key="opportunity.id" class="mb-2 rounded-md border border-slate-200 p-2 text-sm">
            {{ opportunity.title }} · {{ opportunity.status }}
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Open Tickets</h3>
          <div v-for="ticket in tickets" :key="ticket.id" class="mb-2 rounded-md border border-slate-200 p-2 text-sm">
            {{ ticket.ticket_number }} · {{ ticket.status }}
          </div>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <h3 class="mb-3 font-semibold">Quick Links</h3>
        <div class="grid gap-3 md:grid-cols-3">
          <a v-for="link in quickLinks" :key="link.href" :href="link.href" class="rounded-lg border border-slate-200 p-3 text-sm hover:bg-slate-50">{{ link.label }}</a>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
