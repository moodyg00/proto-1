<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({ journalEntries: Object });
</script>

<template>
  <AppLayout>
    <!-- Implements Journal Entries Index from accounting-views-and-actions.md -->
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold">Journal Entries</h2>
      <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
            <tr>
              <th class="px-4 py-3">Entry #</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Reference</th>
              <th class="px-4 py-3">Total</th>
              <th class="px-4 py-3">Posted</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="entry in journalEntries.data" :key="entry.id" class="border-t border-slate-200">
              <td class="px-4 py-3">{{ entry.entry_number }}</td>
              <td class="px-4 py-3">{{ entry.entry_date }}</td>
              <td class="px-4 py-3">{{ entry.reference || '-' }}</td>
              <td class="px-4 py-3">{{ entry.total_amount }}</td>
              <td class="px-4 py-3">{{ entry.is_posted ? 'Yes' : 'No' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
