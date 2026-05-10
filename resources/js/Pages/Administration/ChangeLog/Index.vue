<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  change_logs: Object,
  filters: Object,
})

const search = ref(props.filters?.table_name ?? '')

function applyFilter(field, val) {
  router.get('/administration/change-log', { ...props.filters, [field]: val }, { preserveState: true, replace: true })
}

const actionBadge = (a) => ({
  create: 'bg-green-100 text-green-700',
  update: 'bg-blue-100 text-blue-700',
  delete: 'bg-red-100 text-red-700',
})[a] ?? 'bg-gray-100 text-gray-700'
</script>

<template>
  <AppLayout title="Change Log">
    <div class="p-6 space-y-4">
      <div class="flex flex-wrap gap-3 items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Change Log</h1>
        <div class="flex gap-2">
          <input v-model="search" @keyup.enter="applyFilter('table_name', search)"
                 placeholder="Filter by table…"
                 class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48" />
          <select @change="(e) => applyFilter('action', e.target.value)" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All actions</option>
            <option value="create">Create</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
          </select>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Table</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Action</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">User</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="entry in change_logs.data" :key="entry.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-gray-700">{{ entry.table_name }}</td>
              <td class="px-4 py-3"><span :class="['text-xs font-medium px-2 py-0.5 rounded-full', actionBadge(entry.action)]">{{ entry.action }}</span></td>
              <td class="px-4 py-3 text-gray-500">{{ entry.user?.full_name ?? '—' }}</td>
              <td class="px-4 py-3 text-gray-400">{{ new Date(entry.created_at).toLocaleString() }}</td>
              <td class="px-4 py-3">
                <Link :href="`/administration/change-log/${entry.id}`" class="text-xs text-blue-600 hover:underline">Details</Link>
              </td>
            </tr>
            <tr v-if="!change_logs.data.length">
              <td colspan="5" class="px-4 py-8 text-center text-gray-400">No log entries.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="change_logs.last_page > 1" class="flex gap-2 justify-center">
        <Link v-for="page in change_logs.last_page" :key="page" :href="`/administration/change-log?page=${page}`"
              :class="['px-3 py-1.5 rounded border text-sm', page === change_logs.current_page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">
          {{ page }}
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
