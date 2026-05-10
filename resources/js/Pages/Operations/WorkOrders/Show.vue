<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({
  workOrder: Object,
  materials: Array,
  bookings: Array,
  photos: Array,
  missing: Object,
  modalOptions: Object,
});

const modal = ref(null);

const assignForm = useForm({ assigned_contractor_id: '' });
const materialForm = useForm({ product_id: '', quantity: 1, unit_cost: '', source: 'inventory', is_billable: true });
const bookingForm = useForm({ booking_date: '', start_time: '', end_time: '', duration_minutes: '', notes: '' });
const statusForm = useForm({ status: props.workOrder.status });
const photoForm = useForm({ photo_url: '', description: '' });

const open = (name) => (modal.value = name);
const close = () => (modal.value = null);

const assignContractor = () => assignForm.post(`/operations/work-orders/${props.workOrder.id}/assign-contractor`, { onSuccess: close });
const addMaterial = () => materialForm.post(`/operations/work-orders/${props.workOrder.id}/add-material`, { onSuccess: close });
const createBooking = () => bookingForm.post(`/operations/work-orders/${props.workOrder.id}/create-booking`, { onSuccess: close });
const changeStatus = () => statusForm.post(`/operations/work-orders/${props.workOrder.id}/status`);
const uploadPhoto = () => photoForm.post(`/operations/work-orders/${props.workOrder.id}/upload-photo`, { onSuccess: close });
</script>

<template>
  <AppLayout>
    <!-- Implements Work Order Show from operations-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-slate-900">{{ workOrder.work_order_number }}</h2>
          <p class="text-sm text-slate-600">{{ workOrder.customer_name }} · {{ workOrder.service_name || 'No service' }}</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="`/operations/work-orders/${workOrder.id}/edit`" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Edit</Link>
          <Link href="/operations/work-orders" class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Back</Link>
        </div>
      </div>

      <section class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm lg:col-span-2">
          <div class="grid gap-3 md:grid-cols-2">
            <p><span class="text-xs text-slate-500">Status</span><br><span class="font-medium">{{ workOrder.status }}</span></p>
            <p><span class="text-xs text-slate-500">Invoice</span><br><span class="font-medium">{{ workOrder.invoice_number || 'No invoice yet' }}</span></p>
            <p><span class="text-xs text-slate-500">Contact</span><br><span class="font-medium">{{ workOrder.customer_name }}</span></p>
            <p><span class="text-xs text-slate-500">Phone</span><br><span class="font-medium">{{ workOrder.contact_phone || '-' }}</span></p>
            <p><span class="text-xs text-slate-500">Booking</span><br><span class="font-medium">{{ workOrder.booking_date || 'Not booked' }} {{ workOrder.booking_time || '' }}</span></p>
            <p><span class="text-xs text-slate-500">Assigned Contractor</span><br><span class="font-medium">{{ workOrder.assigned_contractor || 'Unassigned' }}</span></p>
          </div>

          <div class="rounded-lg border border-slate-200 p-3">
            <p class="text-xs text-slate-500">Special Instructions</p>
            <p class="text-sm">{{ workOrder.special_instructions || 'None' }}</p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <button v-if="missing.assigned_contractor" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white" @click="open('assign')">Assign Contractor</button>
            <button v-if="missing.materials" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white" @click="open('material')">Add Material</button>
            <button v-if="missing.booking" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white" @click="open('booking')">Create Booking</button>
            <button class="rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white" @click="open('photo')">Upload Photo</button>

            <select v-model="statusForm.status" class="ml-auto rounded-md border-slate-300 text-sm" @change="changeStatus">
              <option value="new">New</option>
              <option value="scheduled">Scheduled</option>
              <option value="assigned">Assigned</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rework">Rework</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>

        <div class="space-y-4 rounded-xl bg-white p-4 shadow-sm">
          <div>
            <h3 class="font-semibold">Materials</h3>
            <div class="mt-2 space-y-2 text-sm">
              <p v-for="material in materials" :key="material.id" class="rounded-md border border-slate-200 p-2">
                {{ material.product_name || material.product_id }} · Qty {{ material.quantity }}
              </p>
              <p v-if="materials.length === 0" class="text-slate-500">No materials yet.</p>
            </div>
          </div>

          <div>
            <h3 class="font-semibold">Photos</h3>
            <div class="mt-2 grid grid-cols-2 gap-2">
              <a v-for="photo in photos" :key="photo.id" :href="photo.photo_url" target="_blank" class="rounded-md border border-slate-200 p-2 text-xs text-blue-600">
                Photo
              </a>
              <p v-if="photos.length === 0" class="text-sm text-slate-500">No photos.</p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <Modal :open="modal === 'assign'" title="Assign Contractor" @close="close">
      <form class="space-y-3" @submit.prevent="assignContractor">
        <select v-model="assignForm.assigned_contractor_id" class="w-full rounded-md border-slate-300">
          <option value="">Select contractor</option>
          <option v-for="contractor in modalOptions.contractors" :key="contractor.id" :value="contractor.id">{{ contractor.name }}</option>
        </select>
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Assign</button>
      </form>
    </Modal>

    <Modal :open="modal === 'material'" title="Add Material" @close="close">
      <form class="space-y-3" @submit.prevent="addMaterial">
        <select v-model="materialForm.product_id" class="w-full rounded-md border-slate-300">
          <option value="">Select product</option>
          <option v-for="product in modalOptions.products" :key="product.id" :value="product.id">{{ product.name }}</option>
        </select>
        <input v-model="materialForm.quantity" type="number" min="1" step="0.01" class="w-full rounded-md border-slate-300" placeholder="Quantity" />
        <input v-model="materialForm.unit_cost" type="number" min="0" step="0.01" class="w-full rounded-md border-slate-300" placeholder="Unit cost" />
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Save Material</button>
      </form>
    </Modal>

    <Modal :open="modal === 'booking'" title="Create Booking" @close="close">
      <form class="space-y-3" @submit.prevent="createBooking">
        <input v-model="bookingForm.booking_date" type="date" class="w-full rounded-md border-slate-300" />
        <input v-model="bookingForm.start_time" type="time" class="w-full rounded-md border-slate-300" />
        <input v-model="bookingForm.end_time" type="time" class="w-full rounded-md border-slate-300" />
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Create Booking</button>
      </form>
    </Modal>

    <Modal :open="modal === 'photo'" title="Upload Photo" @close="close">
      <form class="space-y-3" @submit.prevent="uploadPhoto">
        <input v-model="photoForm.photo_url" type="url" class="w-full rounded-md border-slate-300" placeholder="https://..." />
        <input v-model="photoForm.description" type="text" class="w-full rounded-md border-slate-300" placeholder="Description" />
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Attach Photo</button>
      </form>
    </Modal>
  </AppLayout>
</template>
