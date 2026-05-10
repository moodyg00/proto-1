<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ product: Object })

const editModal  = ref(false)
const stockModal = ref(false)

const form = useForm({
  name:            props.product.name,
  category:        props.product.category,
  sku:             props.product.sku ?? '',
  description:     props.product.description ?? '',
  unit_price:      props.product.unit_price ?? '',
  is_for_sale:     props.product.is_for_sale,
  is_internal_use: props.product.is_internal_use,
  inventory: {
    quantity_on_hand:  props.product.inventory?.quantity_on_hand ?? '',
    reorder_level:     props.product.inventory?.reorder_level ?? '',
    location:          props.product.inventory?.location ?? '',
  },
})

const stockForm = useForm({
  quantity_on_hand:  props.product.inventory?.quantity_on_hand ?? '',
  quantity_reserved: props.product.inventory?.quantity_reserved ?? '',
  reorder_level:     props.product.inventory?.reorder_level ?? '',
  location:          props.product.inventory?.location ?? '',
})

function submitEdit() {
  form.put(`/administration/products/${props.product.id}`, {
    onSuccess: () => { editModal.value = false },
  })
}

function submitStock() {
  stockForm.post(`/administration/inventory/${props.product.id}/adjust`, {
    onSuccess: () => { stockModal.value = false },
  })
}

function deleteProduct() {
  if (confirm('Delete this product?')) {
    router.delete(`/administration/products/${props.product.id}`)
  }
}

const inv = props.product.inventory
const isLowStock = inv && Number(inv.quantity_on_hand) <= Number(inv.reorder_level)
</script>

<template>
  <AppLayout :title="product.name">
    <div class="p-6 space-y-6">
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between">
          <div>
            <div class="mb-1">
              <Link href="/administration/products" class="text-blue-600 hover:underline text-sm">← Products</Link>
            </div>
            <h1 class="text-xl font-bold text-gray-900">{{ product.name }}</h1>
            <div class="flex gap-2 mt-3">
              <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ product.category }}</span>
              <span v-if="product.is_for_sale" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">For Sale</span>
              <span v-if="product.is_internal_use" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Internal Use</span>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="editModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</button>
            <button @click="deleteProduct" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100">Delete</button>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
          <div><span class="text-gray-400 block text-xs">Unit Price</span>${{ Number(product.unit_price ?? 0).toFixed(2) }}</div>
          <div v-if="product.sku"><span class="text-gray-400 block text-xs">SKU</span><span class="font-mono">{{ product.sku }}</span></div>
        </div>

        <p v-if="product.description" class="mt-4 text-sm text-gray-600">{{ product.description }}</p>
      </div>

      <!-- Inventory -->
      <div class="bg-white rounded-xl border p-6" :class="isLowStock ? 'border-orange-300 bg-orange-50' : 'border-gray-200'">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-gray-800">Inventory</h2>
          <div class="flex items-center gap-2">
            <span v-if="isLowStock" class="text-xs bg-orange-200 text-orange-800 px-2 py-0.5 rounded-full font-medium">Low Stock</span>
            <button @click="stockModal = true" class="px-3 py-1.5 text-sm bg-gray-100 rounded-lg hover:bg-gray-200">Adjust Stock</button>
          </div>
        </div>
        <div v-if="product.inventory" class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
          <div><span class="text-gray-400 block text-xs">On Hand</span>{{ product.inventory.quantity_on_hand }}</div>
          <div><span class="text-gray-400 block text-xs">Reserved</span>{{ product.inventory.quantity_reserved }}</div>
          <div><span class="text-gray-400 block text-xs">Reorder Level</span>{{ product.inventory.reorder_level }}</div>
          <div><span class="text-gray-400 block text-xs">Location</span>{{ product.inventory.location ?? '—' }}</div>
        </div>
        <p v-else class="text-sm text-gray-400">No inventory record yet. <button @click="stockModal = true" class="text-blue-600 hover:underline">Add stock</button></p>
      </div>
    </div>

    <!-- Edit Modal -->
    <Modal :show="editModal" title="Edit Product" @close="editModal = false">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="form.name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <input v-model="form.category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
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
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="editModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Save</button>
        </div>
      </form>
    </Modal>

    <!-- Adjust Stock Modal -->
    <Modal :show="stockModal" title="Adjust Stock" @close="stockModal = false">
      <form @submit.prevent="submitStock" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity On Hand *</label>
            <input v-model="stockForm.quantity_on_hand" type="number" step="0.0001" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reserved</label>
            <input v-model="stockForm.quantity_reserved" type="number" step="0.0001" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
            <input v-model="stockForm.reorder_level" type="number" step="0.0001" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <input v-model="stockForm.location" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="stockModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="stockForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Adjust</button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>
