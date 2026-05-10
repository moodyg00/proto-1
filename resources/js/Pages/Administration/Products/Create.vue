<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  name:            '',
  category:        '',
  sku:             '',
  description:     '',
  unit_price:      '',
  is_for_sale:     true,
  is_internal_use: false,
  inventory: {
    quantity_on_hand: '',
    reorder_level:    '',
    location:         '',
  },
})

function submit() {
  form.post('/administration/products')
}
</script>

<template>
  <AppLayout title="Create Product">
    <div class="p-6 max-w-2xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/administration/products" class="text-blue-600 hover:underline text-sm">← Products</Link>
        <h1 class="text-xl font-bold text-gray-900">Create Product</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="form.name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <input v-model="form.category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="tools, materials…" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
            <input v-model="form.sku" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
            <input v-model="form.unit_price" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="flex gap-4 items-center">
            <label class="flex items-center gap-1 text-sm"><input type="checkbox" v-model="form.is_for_sale" class="rounded" /> For Sale</label>
            <label class="flex items-center gap-1 text-sm"><input type="checkbox" v-model="form.is_internal_use" class="rounded" /> Internal Use</label>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
        </div>

        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Initial Inventory (optional)</h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quantity On Hand</label>
              <input v-model="form.inventory.quantity_on_hand" type="number" step="0.0001" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
              <input v-model="form.inventory.reorder_level" type="number" step="0.0001" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
              <input v-model="form.inventory.location" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Link href="/administration/products" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Create Product</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
