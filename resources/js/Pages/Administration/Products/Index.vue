<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  products: Object,
  filters: Object,
})

const view = ref('table')
const search = ref(props.filters?.search ?? '')

function applySearch() {
  router.get('/administration/products', { search: search.value }, { preserveState: true, replace: true })
}
</script>

<template>
  <AppLayout title="Products">
    <div class="p-6 space-y-4">
      <div class="flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2">
          <input v-model="search" @keyup.enter="applySearch" placeholder="Search products…"
                 class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64" />
          <button @click="applySearch" class="px-3 py-2 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Search</button>
        </div>
        <div class="flex gap-2 items-center">
          <button @click="view = 'table'" :class="['px-3 py-1.5 text-sm rounded-lg border', view==='table' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">Table</button>
          <button @click="view = 'card'"  :class="['px-3 py-1.5 text-sm rounded-lg border', view==='card'  ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">Cards</button>
          <Link href="/administration/products/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ New Product</Link>
        </div>
      </div>

      <!-- Table -->
      <div v-if="view === 'table'" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Price</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">For Sale</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Internal Use</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">SKU</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="prod in products.data" :key="prod.id" class="hover:bg-gray-50 cursor-pointer"
                @click="$inertia.visit(`/administration/products/${prod.id}`)">
              <td class="px-4 py-3 font-medium text-gray-900">{{ prod.name }}</td>
              <td class="px-4 py-3"><span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ prod.category }}</span></td>
              <td class="px-4 py-3 text-gray-600">${{ Number(prod.unit_price ?? 0).toFixed(2) }}</td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-0.5 rounded-full font-medium', prod.is_for_sale ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500']">{{ prod.is_for_sale ? 'Yes' : 'No' }}</span></td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-0.5 rounded-full font-medium', prod.is_internal_use ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500']">{{ prod.is_internal_use ? 'Yes' : 'No' }}</span></td>
              <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ prod.sku ?? '—' }}</td>
            </tr>
            <tr v-if="!products.data.length">
              <td colspan="6" class="px-4 py-8 text-center text-gray-400">No products found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link v-for="prod in products.data" :key="prod.id" :href="`/administration/products/${prod.id}`"
              class="bg-white rounded-xl border border-gray-200 p-4 hover:border-blue-300 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <p class="font-semibold text-gray-900 truncate">{{ prod.name }}</p>
            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', prod.is_for_sale ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500']">{{ prod.is_for_sale ? 'For Sale' : 'Internal' }}</span>
          </div>
          <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ prod.category }}</span>
          <p class="text-sm text-gray-600 mt-2">${{ Number(prod.unit_price ?? 0).toFixed(2) }}</p>
        </Link>
        <p v-if="!products.data.length" class="col-span-full text-center text-gray-400 py-8">No products found.</p>
      </div>

      <!-- Pagination -->
      <div v-if="products.last_page > 1" class="flex gap-2 justify-center">
        <Link v-for="page in products.last_page" :key="page" :href="`/administration/products?page=${page}`"
              :class="['px-3 py-1.5 rounded border text-sm', page === products.current_page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">
          {{ page }}
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
