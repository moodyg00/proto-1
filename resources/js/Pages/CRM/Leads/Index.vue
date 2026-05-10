<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  leads: Object,
  users: Array,
  filters: Object,
});

const sourceOptions = [
  ['website_organic', 'Website Organic'],
  ['facebook', 'Facebook'],
  ['instagram', 'Instagram'],
  ['craigslist', 'Craigslist'],
  ['nextdoor', 'Nextdoor'],
  ['referral', 'Referral'],
  ['physical_media', 'Physical Media'],
  ['in_person', 'In-Person'],
];

const viewMode = ref('table');

const filter = (key, value) => {
  router.get('/crm/leads', { ...props.filters, [key]: value || undefined }, { preserveState: true, replace: true });
};
</script>

<template>
  <AppLayout>
    <!-- Implements Leads Index from crm-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Leads</h2>
        <Link href="/crm/leads/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create New Lead</Link>
      </div>

      <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-4 shadow-sm">
        <select class="rounded-md border-slate-300" :value="filters.status || ''" @change="filter('status', $event.target.value)">
          <option value="">All statuses</option>
          <option value="uncontacted">Uncontacted</option>
          <option value="contacted">Contacted</option>
          <option value="quoted">Quoted</option>
          <option value="booked">Booked</option>
          <option value="converted">Converted</option>
          <option value="lost">Lost</option>
        </select>

        <select class="rounded-md border-slate-300" :value="filters.source || ''" @change="filter('source', $event.target.value)">
          <option value="">All sources</option>
          <option v-for="[value, label] in sourceOptions" :key="value" :value="value">{{ label }}</option>
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
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Phone</th>
              <th class="px-4 py-3">Source</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Next Follow Up</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="lead in leads.data" :key="lead.id" class="border-t border-slate-200">
              <td class="px-4 py-3"><Link class="text-blue-600" :href="`/crm/leads/${lead.id}`">{{ lead.name }}</Link></td>
              <td class="px-4 py-3">{{ lead.email }}</td>
              <td class="px-4 py-3">{{ lead.phone }}</td>
              <td class="px-4 py-3">{{ lead.source }}</td>
              <td class="px-4 py-3">{{ lead.status }}</td>
              <td class="px-4 py-3">{{ lead.next_follow_up || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link v-for="lead in leads.data" :key="lead.id" :href="`/crm/leads/${lead.id}`" class="rounded-xl bg-white p-4 shadow-sm hover:bg-slate-50">
          <p class="font-semibold">{{ lead.name }}</p>
          <p class="text-sm text-slate-600">{{ lead.source || 'Unknown source' }} · {{ lead.status }}</p>
          <p class="mt-2 text-xs text-slate-500">Next follow up: {{ lead.next_follow_up || 'Not set' }}</p>
          <p class="text-xs text-slate-500">Assigned: {{ lead.assigned_to || 'Unassigned' }}</p>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
