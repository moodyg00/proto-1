<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({ users: Array });

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

const form = useForm({
  name: '',
  email: '',
  phone: '',
  source: '',
  status: 'uncontacted',
  next_follow_up: '',
  assigned_to: '',
  expected_value: '',
  notes: [],
});

const submit = () => form.post('/crm/leads');
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-2xl font-semibold">Create Lead</h2>
      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <input v-model="form.name" type="text" class="w-full rounded-md border-slate-300" placeholder="Name" />
        <input v-model="form.email" type="email" class="w-full rounded-md border-slate-300" placeholder="Email" />
        <input v-model="form.phone" type="text" class="w-full rounded-md border-slate-300" placeholder="Phone" />
        <select v-model="form.source" class="w-full rounded-md border-slate-300">
          <option value="">Select source</option>
          <option v-for="[value, label] in sourceOptions" :key="value" :value="value">{{ label }}</option>
        </select>

        <div class="grid gap-4 md:grid-cols-2">
          <select v-model="form.status" class="w-full rounded-md border-slate-300">
            <option value="uncontacted">Uncontacted</option>
            <option value="contacted">Contacted</option>
            <option value="quoted">Quoted</option>
            <option value="booked">Booked</option>
            <option value="converted">Converted</option>
            <option value="lost">Lost</option>
          </select>
        </div>

        <input v-model="form.next_follow_up" type="date" class="w-full rounded-md border-slate-300" />

        <select v-model="form.assigned_to" class="w-full rounded-md border-slate-300">
          <option value="">Assign to</option>
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.full_name }}</option>
        </select>

        <div class="flex items-center gap-3">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" :disabled="form.processing">Save</button>
          <Link href="/crm/leads" class="text-sm text-slate-600">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
