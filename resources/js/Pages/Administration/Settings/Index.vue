<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  settings: Array,
  modules: Array,
  filters: Object,
  branding: Object,
})

const createModal = ref(false)
const editModal   = ref(false)
const editTarget  = ref(null)

const brandingForm = useForm({
  brand_name: props.branding?.brand_name ?? 'Moody Home Services, LLC',
  logo: null,
})

const createForm = useForm({ module: '', key: '', value: '', description: '' })
const editForm   = useForm({ module: '', key: '', value: '', description: '' })

const categories = computed(() => ([
  { key: 'business', title: 'Business Settings', modules: ['business'], description: 'Branding, identity, and public business defaults.' },
  { key: 'operations', title: 'Operations Settings', modules: ['operations'], description: 'Scheduling, crews, work-order defaults, and operational rules.' },
  { key: 'customer_relations', title: 'Customer Relations Settings', modules: ['crm', 'customer_relations'], description: 'Pipelines, relationship workflows, and follow-up defaults.' },
  { key: 'user_preferences', title: 'User Preferences', modules: ['ui_preferences', 'user_preferences'], description: 'Saved default views, board preferences, and personalized defaults.' },
]))

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

function onLogoChange(event) {
  brandingForm.logo = event.target.files?.[0] ?? null
}

function settingsForCategory(category) {
  return props.settings.filter((setting) => category.modules.includes(setting.module))
}

function submitBranding() {
  brandingForm.post('/administration/settings/branding', {
    forceFormData: true,
    onSuccess: () => {
      brandingForm.reset('logo')
    },
  })
}
</script>

<template>
  <AppLayout title="Settings">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
          <p class="mt-1 text-sm text-gray-500">Business, operations, customer relations, and user preference controls in one place.</p>
        </div>
        <button @click="createModal = true" class="rounded-lg bg-slate-950 px-4 py-2 text-sm text-white transition hover:bg-slate-800">+ Add Setting</button>
      </div>

      <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between gap-6">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-600">Business Settings</p>
              <h2 class="mt-2 text-xl font-semibold text-slate-950">Branding & Header</h2>
              <p class="mt-2 max-w-2xl text-sm text-slate-500">Update the company name and upload a replacement logo. Uploading a new logo automatically replaces the previous stored file.</p>
            </div>

            <img :src="branding.logo_url || '/images/moody-home-services-mark.svg'" alt="Current logo" class="h-16 w-auto rounded-2xl border border-slate-200 bg-slate-50 p-2" />
          </div>

          <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="submitBranding">
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Business Name</label>
              <input v-model="brandingForm.brand_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">Logo Upload</label>
              <input type="file" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" @change="onLogoChange" />
            </div>
            <div class="md:col-span-2 flex justify-end">
              <button type="submit" :disabled="brandingForm.processing" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-amber-400 disabled:opacity-50">
                Save Branding
              </button>
            </div>
          </form>
        </div>

        <aside class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-950">Configuration Categories</h2>
          <div class="mt-4 space-y-3">
            <div v-for="category in categories" :key="category.key" class="rounded-2xl border border-slate-200 px-4 py-3">
              <div class="text-sm font-semibold text-slate-900">{{ category.title }}</div>
              <div class="mt-1 text-xs leading-5 text-slate-500">{{ category.description }}</div>
            </div>
          </div>
        </aside>
      </section>

      <section class="space-y-4">
        <div v-for="category in categories" :key="category.key" class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
          <div class="border-b border-gray-100 bg-slate-50 px-5 py-4">
            <h2 class="font-semibold text-gray-900">{{ category.title }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ category.description }}</p>
          </div>

          <table v-if="settingsForCategory(category).length" class="w-full text-sm">
            <tbody class="divide-y divide-gray-50">
              <tr v-for="s in settingsForCategory(category)" :key="s.id" class="cursor-pointer hover:bg-gray-50" @click="openEdit(s)">
                <td class="w-44 px-5 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ s.module }}</td>
                <td class="w-64 px-5 py-4 font-mono text-gray-800">{{ s.key }}</td>
                <td class="max-w-xl px-5 py-4 text-gray-500">{{ typeof s.value === 'object' ? JSON.stringify(s.value) : s.value }}</td>
                <td class="px-5 py-4 text-xs text-gray-400">{{ s.description }}</td>
                <td class="px-5 py-4 text-xs text-gray-400">{{ s.updated_at ? new Date(s.updated_at).toLocaleDateString() : '' }}</td>
              </tr>
            </tbody>
          </table>

          <p v-else class="px-5 py-8 text-sm text-slate-400">No settings saved in this category yet.</p>
        </div>
      </section>

      <p v-if="!settings.length" class="text-center text-gray-400 py-12">No settings found.</p>
    </div>

    <!-- Create Modal -->
    <Modal :open="createModal" title="Add Setting" @close="createModal = false">
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
    <Modal :open="editModal" title="Edit Setting" @close="editModal = false">
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
