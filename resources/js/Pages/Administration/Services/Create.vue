<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  name:            '',
  category:        '',
  description:     '',
  suggested_price: '',
  is_active:       true,
})

function submit() {
  form.post('/administration/services')
}
</script>

<template>
  <AppLayout title="Create Service">
    <div class="p-6 max-w-xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/administration/services" class="text-blue-600 hover:underline text-sm">← Services</Link>
        <h1 class="text-xl font-bold text-gray-900">Create Service</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
          <input v-model="form.name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
          <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
          <input v-model="form.category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. plumbing, electrical" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Suggested Price</label>
          <input v-model="form.suggested_price" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div class="flex items-center gap-2">
          <input id="svc_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300" />
          <label for="svc_active" class="text-sm text-gray-700">Active</label>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Link href="/administration/services" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Create Service</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
