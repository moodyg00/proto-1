<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ service: Object })

const editModal = ref(false)
const form = useForm({
  name:            props.service.name,
  category:        props.service.category,
  description:     props.service.description ?? '',
  suggested_price: props.service.suggested_price ?? '',
  is_active:       props.service.is_active,
})

function submitEdit() {
  form.put(`/administration/services/${props.service.id}`, {
    onSuccess: () => { editModal.value = false },
  })
}

function deleteService() {
  if (confirm('Delete this service?')) {
    router.delete(`/administration/services/${props.service.id}`)
  }
}
</script>

<template>
  <AppLayout :title="service.name">
    <div class="p-6 space-y-6">
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <Link href="/administration/services" class="text-blue-600 hover:underline text-sm">← Services</Link>
            </div>
            <h1 class="text-xl font-bold text-gray-900">{{ service.name }}</h1>
            <div class="flex gap-2 mt-3">
              <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ service.category }}</span>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', service.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                {{ service.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="editModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</button>
            <button @click="deleteService" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100">Delete</button>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="text-gray-400 block text-xs">Suggested Price</span>
            ${{ Number(service.suggested_price ?? 0).toFixed(2) }}
          </div>
          <div>
            <span class="text-gray-400 block text-xs">Created</span>
            {{ new Date(service.created_at).toLocaleDateString() }}
          </div>
        </div>

        <p v-if="service.description" class="mt-4 text-sm text-gray-600">{{ service.description }}</p>
      </div>
    </div>

    <!-- Edit Modal -->
    <Modal :show="editModal" title="Edit Service" @close="editModal = false">
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Suggested Price</label>
            <input v-model="form.suggested_price" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
          <div class="flex items-center gap-2">
            <input id="svc_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300" />
            <label for="svc_active" class="text-sm text-gray-700">Active</label>
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
