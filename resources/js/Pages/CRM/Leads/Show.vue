<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  lead: Object,
  relatedEstimates: Array,
  relatedOpportunities: Array,
});

const notesForm = useForm({ note: '' });

const lastNotes = computed(() => props.lead.notes || []);
const lastContacted = computed(() => props.lead.last_contacted_at || 'Not yet');
</script>

<template>
  <AppLayout>
    <!-- Implements Lead Show from crm-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold">{{ lead.name }}</h2>
          <p class="text-sm text-slate-600">{{ lead.email }} · {{ lead.phone }}</p>
        </div>
        <div class="flex gap-2">
          <Link :href="`/crm/leads/${lead.id}/edit`" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Edit</Link>
          <Link href="/crm/leads" class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Back</Link>
        </div>
      </div>

      <section class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm lg:col-span-2">
          <div class="grid gap-3 md:grid-cols-2">
            <p><span class="text-xs text-slate-500">Status</span><br><span class="font-medium">{{ lead.status }}</span></p>
            <p><span class="text-xs text-slate-500">Source</span><br><span class="font-medium">{{ lead.source || '-' }}</span></p>
            <p><span class="text-xs text-slate-500">Next Follow Up</span><br><span class="font-medium">{{ lead.next_follow_up || '-' }}</span></p>
            <p><span class="text-xs text-slate-500">Assigned To</span><br><span class="font-medium">{{ lead.assigned_to || 'Unassigned' }}</span></p>
            <p><span class="text-xs text-slate-500">Last Contacted</span><br><span class="font-medium">{{ lastContacted }}</span></p>
          </div>

          <div class="rounded-lg border border-slate-200 p-3">
            <p class="mb-2 text-xs text-slate-500">Notes / Call History</p>
            <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700">
              <li v-for="(note, idx) in lastNotes" :key="idx">{{ note.note || note }}</li>
              <li v-if="lastNotes.length === 0" class="list-none text-slate-500">No notes yet.</li>
            </ul>
          </div>

          <form class="space-y-2 rounded-lg border border-slate-200 p-3">
            <label class="text-xs text-slate-500">Log Call / Email (UI action placeholder)</label>
            <textarea v-model="notesForm.note" rows="2" class="w-full rounded-md border-slate-300" placeholder="Add note" />
            <p class="text-xs text-slate-500">Business rule: adding note updates last_contacted_at and suggests next_follow_up.</p>
          </form>
        </div>

        <div class="space-y-4 rounded-xl bg-white p-4 shadow-sm">
          <div>
            <h3 class="font-semibold">Related Estimates</h3>
            <div class="mt-2 space-y-2 text-sm">
              <p v-for="estimate in relatedEstimates" :key="estimate.id" class="rounded-md border border-slate-200 p-2">
                {{ estimate.estimate_number }} · {{ estimate.status }}
              </p>
              <p v-if="relatedEstimates.length === 0" class="text-slate-500">No estimates.</p>
            </div>
          </div>

          <div>
            <h3 class="font-semibold">Related Opportunities</h3>
            <div class="mt-2 space-y-2 text-sm">
              <p v-for="opportunity in relatedOpportunities" :key="opportunity.id" class="rounded-md border border-slate-200 p-2">
                {{ opportunity.title }} · {{ opportunity.status }}
              </p>
              <p v-if="relatedOpportunities.length === 0" class="text-slate-500">No opportunities.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
