<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  settings: Array,
  modules: Array,
  filters: Object,
})

const createModal = ref(false)
const editModal   = ref(false)
const editTarget  = ref(null)

const createForm = useForm({ module: '', key: '', value: '', description: '' })
const editForm   = useForm({ module: '', key: '', value: '', description: '' })

function openEdit(setting) {
  editTarget.value = setting
  editForm.module      = setting.module ?? ''
  editForm.key         = setting.key ?? ''
  editForm.value       = Array.isArray(setting.value) || typeof setting.value === 'object'
    ? JSON.stringify(setting.value)
    : setting.value ?? ''
  editForm.description = setting.description ?? ''
  editModal.value = true
}

function submitCreate() {
  createForm.post('/administration/settings', {
    onSuccess: () => { createModal.value = false; createForm.reset() },
  })
}

function submitEdit() {
  editForm.put(`/administration/settings/${editTarget.value.id}`, {
    onSuccess: () => { editModal.value = false },
  })
}
</script>

<template>
  <AppLayout title="Settings">
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">System Settings</h1>
        <button @click="createModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">+ Add Setting</button>
      </div>

      <!-- Group by module -->
      <div v-for="mod in [...new Set(settings.map(s => s.module))].sort()" :key="mod" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
          <h2 class="font-semibold text-gray-700 uppercase text-xs tracking-wide">{{ mod }}</h2>
        </div>
        <table class="w-full text-sm">
          <tbody class="divide-y divide-gray-50">
            <tr v-for="s in settings.filter(x => x.module === mod)" :key="s.id"
                class="hover:bg-gray-50 cursor-pointer" @click="openEdit(s)">
              <td class="px-4 py-3 font-mono text-gray-800 w-64">{{ s.key }}</td>
              <td class="px-4 py-3 text-gray-500 truncate max-w-xs">{{ typeof s.value === 'object' ? JSON.stringify(s.value) : s.value }}</td>
              <td class="px-4 py-3 text-gray-400 text-xs">{{ s.description }}</td>
              <td class="px-4 py-3 text-gray-400 text-xs">{{ s.updated_at ? new Date(s.updated_at).toLocaleDateString() : '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="!settings.length" class="text-center text-gray-400 py-12">No settings found.</p>
    </div>

    <!-- Create Modal -->
    <Modal :show="createModal" title="Add Setting" @close="createModal = false">
      <form @submit.prevent="submitCreate" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Module *</label>
            <input v-model="createForm.module" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key *</label>
            <input v-model="createForm.key" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono" required />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
            <input v-model="createForm.value" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input v-model="createForm.description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="createModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="createForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Save</button>
        </div>
      </form>
    </Modal>

    <!-- Edit Modal -->
    <Modal :show="editModal" title="Edit Setting" @close="editModal = false">
      <form @submit.prevent="submitEdit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Module *</label>
            <input v-model="editForm.module" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key *</label>
            <input v-model="editForm.key" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono" required />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
            <input v-model="editForm.value" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input v-model="editForm.description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="editModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Update</button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>
