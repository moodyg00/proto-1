<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  users: Object,
  filters: Object,
})

const view = ref('table')
const search = ref(props.filters?.search ?? '')

function applySearch() {
  router.get('/administration/users', { search: search.value }, { preserveState: true, replace: true })
}

const userTypeBadge = (type) => {
  const map = { human: 'bg-blue-100 text-blue-700', ai_agent: 'bg-violet-100 text-violet-700', automation: 'bg-gray-100 text-gray-700' }
  return map[type] ?? 'bg-gray-100 text-gray-700'
}

const statusBadge = (active) => active
  ? 'bg-emerald-100 text-emerald-700'
  : 'bg-red-100 text-red-700'
</script>

<template>
  <AppLayout title="Users">
    <div class="p-6 space-y-4">
      <!-- Toolbar -->
      <div class="flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2">
          <input v-model="search" @keyup.enter="applySearch" placeholder="Search users…"
                 class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64" />
          <button @click="applySearch" class="px-3 py-2 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Search</button>
        </div>
        <div class="flex gap-2 items-center">
          <button @click="view = 'table'" :class="['px-3 py-1.5 text-sm rounded-lg border', view==='table' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">Table</button>
          <button @click="view = 'card'"  :class="['px-3 py-1.5 text-sm rounded-lg border', view==='card'  ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">Cards</button>
          <Link href="/administration/users/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ New User</Link>
        </div>
      </div>

      <!-- Table -->
      <div v-if="view === 'table'" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Role</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Last Login</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 cursor-pointer"
                @click="$inertia.visit(`/administration/users/${user.id}`)">
              <td class="px-4 py-3 font-medium text-gray-900">{{ user.full_name }}</td>
              <td class="px-4 py-3 text-gray-500">{{ user.email }}</td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-0.5 rounded-full font-medium', userTypeBadge(user.user_type)]">{{ user.user_type }}</span></td>
              <td class="px-4 py-3 text-gray-600">{{ user.role }}</td>
              <td class="px-4 py-3"><span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusBadge(user.is_active)]">{{ user.is_active ? 'Active' : 'Inactive' }}</span></td>
              <td class="px-4 py-3 text-gray-400">{{ user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : '—' }}</td>
            </tr>
            <tr v-if="!users.data.length">
              <td colspan="6" class="px-4 py-8 text-center text-gray-400">No users found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link v-for="user in users.data" :key="user.id" :href="`/administration/users/${user.id}`"
              class="bg-white rounded-xl border border-gray-200 p-4 hover:border-blue-300 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <p class="font-semibold text-gray-900 truncate">{{ user.full_name }}</p>
            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusBadge(user.is_active)]">{{ user.is_active ? 'Active' : 'Inactive' }}</span>
          </div>
          <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', userTypeBadge(user.user_type)]">{{ user.user_type }}</span>
          <p class="text-sm text-gray-500 mt-2">{{ user.role }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : 'Never logged in' }}</p>
        </Link>
        <p v-if="!users.data.length" class="col-span-full text-center text-gray-400 py-8">No users found.</p>
      </div>

      <!-- Pagination -->
      <div v-if="users.last_page > 1" class="flex gap-2 justify-center">
        <Link v-for="page in users.last_page" :key="page" :href="`/administration/users?page=${page}`"
              :class="['px-3 py-1.5 rounded border text-sm', page === users.current_page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50']">
          {{ page }}
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
