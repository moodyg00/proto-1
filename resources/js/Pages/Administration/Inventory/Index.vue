<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  inventory: Object,
  filters: Object,
})

const adjustModal   = ref(false)
const adjustProduct = ref(null)

const stockForm = useForm({
  quantity_on_hand:  '',
  quantity_reserved: '',
  reorder_level:     '',
  location:          '',
})

function openAdjust(item) {
  adjustProduct.value = item
  stockForm.quantity_on_hand  = item.quantity_on_hand ?? ''
  stockForm.quantity_reserved = item.quantity_reserved ?? ''
  stockForm.reorder_level     = item.reorder_level ?? ''
  stockForm.location          = item.location ?? ''
  adjustModal.value = true
}

function submitAdjust() {
  stockForm.post(`/administration/inventory/${adjustProduct.value.product_id}/adjust`, {
    onSuccess: () => { adjustModal.value = false },
  })
}

const isLow = (item) => Number(item.quantity_on_hand) <= Number(item.reorder_level)

const search = ref(props.filters?.search ?? '')
function applySearch() {
  router.get('/administration/inventory', { search: search.value }, { preserveState: true, replace: true })
}
</script>

<template>
  <AppLayout title="Inventory">
    <div class="p-6 space-y-4">
      <div class="flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2">
          <input v-model="search" @keyup.enter="applySearch" placeholder="Search products…"
                 class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64" />
          <button @click="applySearch" class="px-3 py-2 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Search</button>
        </div>
        <Link href="/administration/inventory?low_stock=1" class="text-sm text-orange-600 hover:underline">Show Low Stock Only</Link>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">On Hand</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Reserved</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Reorder Level</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Location</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Last Used</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in inventory.data" :key="item.id"
                :class="['hover:bg-gray-50', isLow(item) ? 'bg-orange-50' : '']">
              <td class="px-4 py-3 font-medium text-gray-900">
                {{ item.product?.name ?? '—' }}
                <span v-if="isLow(item)" class="ml-2 text-xs bg-orange-200 text-orange-800 px-1.5 py-0.5 rounded-full">Low</span>
              </td>
              <td class="px-4 py-3 text-gray-700">{{ item.quantity_on_hand }}</td>
              <td class="px-4 py-3 text-gray-500">{{ item.quantity_reserved }}</td>
              <td class="px-4 py-3 text-gray-500">{{ item.reorder_level }}</td>
              <td class="px-4 py-3 text-gray-400">{{ item.location ?? '—' }}</td>
              <td class="px-4 py-3 text-gray-400">{{ item.last_used_at ? new Date(item.last_used_at).toLocaleDateString() : '—' }}</td>
              <td class="px-4 py-3">
                <button @click="openAdjust(item)" class="text-xs text-blue-600 hover:underline">Adjust</button>
              </td>
            </tr>
            <tr v-if="!inventory.data.length">
              <td colspan="7" class="px-4 py-8 text-center text-gray-400">No inventory records.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="inventory.last_page > 1" class="flex gap-2 justify-center">
        <Link v-for="page in inventory.last_page" :key="page" :href="`/administration/inventory?page=${page}`"
              :class="['px-3 py-1.5 rounded border text-sm', page === inventory.current_page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">
          {{ page }}
        </Link>
      </div>
    </div>

    <!-- Adjust Stock Modal -->
    <Modal :show="adjustModal" title="Adjust Stock" @close="adjustModal = false">
      <p class="text-sm text-gray-600 mb-4 font-medium">{{ adjustProduct?.product?.name }}</p>
      <form @submit.prevent="submitAdjust" class="space-y-4">
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
          <button type="button" @click="adjustModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="stockForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Adjust</button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>
