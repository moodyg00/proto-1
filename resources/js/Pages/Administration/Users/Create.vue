<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  full_name:   '',
  email:       '',
  username:    '',
  user_type:   'human',
  role:        'user',
  ai_model:    '',
  description: '',
  is_active:   true,
  password:    '',
})

function submit() {
  form.post('/administration/users')
}
</script>

<template>
  <AppLayout title="Create User">
    <div class="p-6 max-w-2xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/administration/users" class="text-blue-600 hover:underline text-sm">← Users</Link>
        <h1 class="text-xl font-bold text-gray-900">Create User</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input v-model="form.full_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
            <p v-if="form.errors.full_name" class="text-red-500 text-xs mt-1">{{ form.errors.full_name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User Type *</label>
            <select v-model="form.user_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
              <option value="human">Human</option>
              <option value="ai_agent">AI Agent</option>
              <option value="automation">Automation</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
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
            <input v-model="form.ai_model" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. gpt-4o" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input v-model="form.password" type="password" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
          <div class="flex items-center gap-2">
            <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300" />
            <label for="is_active" class="text-sm text-gray-700">Active</label>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Link href="/administration/users" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Create User</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
