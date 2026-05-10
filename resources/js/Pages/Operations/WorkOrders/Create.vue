<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  customers: Array,
  services: Array,
  contractors: Array,
});

const form = useForm({
  contact_id: '',
  service_id: '',
  scheduled_date: '',
  assigned_contractor_id: '',
  special_instructions: '',
  notes: [],
});

const submit = () => form.post('/operations/work-orders');
</script>

<template>
  <AppLayout>
    <!-- Implements Work Order Create from operations-views-and-actions.md -->
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold text-slate-900">Create Work Order</h2>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div>
          <label class="text-sm font-medium text-slate-700">Customer</label>
          <select v-model="form.contact_id" class="mt-1 w-full rounded-md border-slate-300">
            <option value="">Select customer</option>
            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
          </select>
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700">Service</label>
          <select v-model="form.service_id" class="mt-1 w-full rounded-md border-slate-300">
            <option value="">Select service</option>
            <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
          </select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-slate-700">Scheduled Date</label>
            <input v-model="form.scheduled_date" type="date" class="mt-1 w-full rounded-md border-slate-300" />
          </div>
          <div>
            <label class="text-sm font-medium text-slate-700">Assigned Contractor (optional)</label>
            <select v-model="form.assigned_contractor_id" class="mt-1 w-full rounded-md border-slate-300">
              <option value="">Unassigned</option>
              <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">{{ contractor.name }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700">Special Instructions</label>
          <textarea v-model="form.special_instructions" rows="3" class="mt-1 w-full rounded-md border-slate-300" />
        </div>

        <div class="flex items-center gap-3 pt-2">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" :disabled="form.processing">Save</button>
          <Link href="/operations/work-orders" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
