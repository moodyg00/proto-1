<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  user: Object,
  recentActivity: Array,
  tickets: Array,
  tasks: Array,
})

const editModal = ref(false)
const form = useForm({
  full_name:   props.user.full_name,
  email:       props.user.email,
  username:    props.user.username ?? '',
  user_type:   props.user.user_type,
  role:        props.user.role,
  ai_model:    props.user.ai_model ?? '',
  description: props.user.description ?? '',
  is_active:   props.user.is_active,
  password:    '',
})

function submitEdit() {
  form.put(`/administration/users/${props.user.id}`, {
    onSuccess: () => { editModal.value = false },
  })
}

function toggleActive() {
  router.post(`/administration/users/${props.user.id}/toggle-active`)
}

const typeBadge = (t) => ({ human: 'bg-blue-100 text-blue-700', ai_agent: 'bg-violet-100 text-violet-700', automation: 'bg-gray-100 text-gray-700' })[t] ?? 'bg-gray-100 text-gray-700'
const actionBadge = (a) => ({ create: 'bg-green-100 text-green-700', update: 'bg-blue-100 text-blue-700', delete: 'bg-red-100 text-red-700' })[a] ?? 'bg-gray-100 text-gray-700'
</script>

<template>
  <AppLayout :title="user.full_name">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-xl font-bold text-gray-900">{{ user.full_name }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ user.email }}</p>
            <div class="flex gap-2 mt-3">
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', typeBadge(user.user_type)]">{{ user.user_type }}</span>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-700">{{ user.role }}</span>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', user.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="editModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</button>
            <button @click="toggleActive"
                    :class="['px-4 py-2 rounded-lg text-sm', user.is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100']">
              {{ user.is_active ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
          <div v-if="user.username"><span class="text-gray-400 block text-xs">Username</span>{{ user.username }}</div>
          <div v-if="user.ai_model"><span class="text-gray-400 block text-xs">AI Model</span>{{ user.ai_model }}</div>
          <div><span class="text-gray-400 block text-xs">Last Login</span>{{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : '—' }}</div>
          <div><span class="text-gray-400 block text-xs">Created</span>{{ new Date(user.created_at).toLocaleDateString() }}</div>
        </div>

        <p v-if="user.description" class="mt-4 text-sm text-gray-600">{{ user.description }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
          <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Recent Activity</h2>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-for="entry in recentActivity" :key="entry.id" class="px-4 py-3 flex items-center gap-3">
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', actionBadge(entry.action)]">{{ entry.action }}</span>
              <span class="text-sm text-gray-700 flex-1">{{ entry.table_name }}</span>
              <span class="text-xs text-gray-400">{{ new Date(entry.created_at).toLocaleString() }}</span>
            </div>
            <p v-if="!recentActivity.length" class="px-4 py-6 text-sm text-gray-400 text-center">No activity recorded.</p>
          </div>
        </div>

        <!-- Tickets & Tasks -->
        <div class="space-y-4">
          <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-100">
              <h2 class="font-semibold text-gray-800">Open Tickets ({{ tickets.length }})</h2>
            </div>
            <div class="p-4 space-y-2">
              <div v-for="t in tickets" :key="t.id" class="text-sm text-gray-700 p-2 bg-gray-50 rounded-lg">{{ t.title ?? t.id }}</div>
              <p v-if="!tickets.length" class="text-sm text-gray-400">No open tickets.</p>
            </div>
          </div>
          <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-100">
              <h2 class="font-semibold text-gray-800">Pending Tasks ({{ tasks.length }})</h2>
            </div>
            <div class="p-4 space-y-2">
              <div v-for="t in tasks" :key="t.id" class="text-sm text-gray-700 p-2 bg-gray-50 rounded-lg">{{ t.title ?? t.id }}</div>
              <p v-if="!tasks.length" class="text-sm text-gray-400">No pending tasks.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <Modal :show="editModal" title="Edit User" @close="editModal = false">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input v-model="form.full_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
            <p v-if="form.errors.full_name" class="text-red-500 text-xs mt-1">{{ form.errors.full_name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
            <select v-model="form.user_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
              <option value="human">Human</option>
              <option value="ai_agent">AI Agent</option>
              <option value="automation">Automation</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="form.role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
              <option value="user">User</option>
              <option value="admin">Admin</option>
              <option value="moderator">Moderator</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input v-model="form.username" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">AI Model</label>
            <input v-model="form.ai_model" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep)</label>
            <input v-model="form.password" type="password" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="sm:col-span-2">
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
  </AppLayout>
</template>
