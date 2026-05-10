<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({ bill: Object, payments: Array });

const modal = ref(null);
const paymentForm = useForm({ amount: '', payment_date: new Date().toISOString().slice(0, 10), method: 'bank_transfer', reference: '', notes: '' });

const recordPayment = () => paymentForm.post(`/accounting/bills/${props.bill.id}/record-payment`, { onSuccess: () => (modal.value = null) });

const deleteBill = () => {
  if (confirm('Delete this bill?')) {
    router.delete(`/accounting/bills/${props.bill.id}`);
  }
};
</script>

<template>
  <AppLayout>
    <!-- Implements Bill Show from accounting-views-and-actions.md -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold">{{ bill.bill_number }}</h2>
          <p class="text-sm text-slate-600">{{ bill.vendor_name || '-' }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'payment'">Record Payment</button>
          <Link :href="`/accounting/bills/${bill.id}/edit`" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Edit</Link>
          <button class="rounded-lg border border-red-300 px-3 py-2 text-sm text-red-600" @click="deleteBill">Delete</button>
          <Link href="/accounting/bills" class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Back</Link>
        </div>
      </div>

      <section class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 rounded-xl bg-white p-4 shadow-sm lg:col-span-2">
          <div class="grid gap-3 md:grid-cols-3">
            <p><span class="text-xs text-slate-500">Issue Date</span><br>{{ bill.issue_date }}</p>
            <p><span class="text-xs text-slate-500">Due Date</span><br>{{ bill.due_date }}</p>
            <p><span class="text-xs text-slate-500">Status</span><br><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ bill.status }}</span></p>
          </div>

          <div class="grid gap-2 md:grid-cols-4 text-sm">
            <p><span class="text-xs text-slate-500">Subtotal</span><br>{{ bill.subtotal }}</p>
            <p><span class="text-xs text-slate-500">Tax</span><br>{{ bill.tax_amount }}</p>
            <p><span class="text-xs text-slate-500">Amount Paid</span><br>{{ bill.amount_paid }}</p>
            <p><span class="text-xs text-slate-500">Amount Due</span><br>{{ bill.amount_due }}</p>
          </div>

          <div class="rounded-lg border border-slate-200 p-3 text-sm">
            <p class="text-xs text-slate-500">Notes</p>
            <p class="mt-1">{{ bill.notes || '-' }}</p>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Payment History</h3>
          <div class="space-y-2 text-sm">
            <div v-for="payment in payments" :key="payment.id" class="rounded-md border border-slate-200 p-2">
              {{ payment.payment_number }} · {{ payment.amount }} · {{ payment.payment_date }}
            </div>
            <p v-if="payments.length === 0" class="text-slate-500">No payments yet.</p>
          </div>
        </div>
      </section>
    </div>

    <Modal :open="modal === 'payment'" title="Record Payment" @close="modal = null">
      <form class="space-y-3" @submit.prevent="recordPayment">
        <input v-model.number="paymentForm.amount" type="number" min="0.01" step="0.01" class="w-full rounded-md border-slate-300" placeholder="Amount" />
        <input v-model="paymentForm.payment_date" type="date" class="w-full rounded-md border-slate-300" />
        <select v-model="paymentForm.method" class="w-full rounded-md border-slate-300">
          <option value="cash">Cash</option>
          <option value="check">Check</option>
          <option value="credit_card">Credit Card</option>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="other">Other</option>
        </select>
        <input v-model="paymentForm.reference" type="text" class="w-full rounded-md border-slate-300" placeholder="Reference" />
        <textarea v-model="paymentForm.notes" rows="2" class="w-full rounded-md border-slate-300" placeholder="Notes" />
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Save Payment</button>
      </form>
    </Modal>
  </AppLayout>
</template>
