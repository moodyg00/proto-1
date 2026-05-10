<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({ transaction: Object, journalEntries: Array, accounts: Array });
const modal = ref(null);

const categorizeForm = useForm({
  internal_category: props.transaction.internal_category || '',
  source: 'manual',
});

const linkForm = useForm({
  journal_entry_id: props.transaction.journal_entry_id || '',
});

const editForm = useForm({
  bank_account_id: props.transaction.bank_account_id,
  transaction_date: props.transaction.transaction_date,
  amount: props.transaction.amount,
  transaction_type: props.transaction.transaction_type,
  description: props.transaction.description || '',
  reference: props.transaction.reference || '',
  external_category: props.transaction.external_category || '',
  internal_category: props.transaction.internal_category || '',
  category_source: props.transaction.category_source || 'manual',
  status: props.transaction.status,
  notes: props.transaction.notes || '',
});

const categorize = () => categorizeForm.post(`/banking/transactions/${props.transaction.id}/categorize`, { onSuccess: () => (modal.value = null) });
const linkJournal = () => linkForm.post(`/banking/transactions/${props.transaction.id}/link-journal-entry`, { onSuccess: () => (modal.value = null) });
const updateTransaction = () => editForm.put(`/banking/transactions/${props.transaction.id}`, { onSuccess: () => (modal.value = null) });

const deleteTransaction = () => {
  if (confirm('Delete this transaction?')) {
    router.delete(`/banking/transactions/${props.transaction.id}`);
  }
};
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold">Bank Transaction</h2>
          <p class="text-sm text-slate-600">{{ transaction.transaction_date }} · {{ transaction.transaction_type }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'categorize'">Categorize</button>
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'journal'">Link to Journal Entry</button>
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'edit'">Edit</button>
          <button class="rounded-lg border border-red-300 px-3 py-2 text-sm text-red-600" @click="deleteTransaction">Delete</button>
        </div>
      </div>

      <section class="rounded-xl bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3 text-sm">
          <p><span class="text-xs text-slate-500">Description</span><br>{{ transaction.description || '-' }}</p>
          <p><span class="text-xs text-slate-500">Amount</span><br>{{ transaction.amount }}</p>
          <p><span class="text-xs text-slate-500">Status</span><br>{{ transaction.status }}</p>
          <p><span class="text-xs text-slate-500">External Category</span><br>{{ transaction.external_category || '-' }}</p>
          <p><span class="text-xs text-slate-500">Internal Category</span><br>{{ transaction.internal_category || '-' }}</p>
          <p><span class="text-xs text-slate-500">Journal Entry</span><br>{{ transaction.journal_entry_id || '-' }}</p>
        </div>
        <p class="mt-4 text-sm"><span class="text-xs text-slate-500">Notes</span><br>{{ transaction.notes || '-' }}</p>
      </section>
    </div>

    <Modal :open="modal === 'categorize'" title="Categorize Transaction" @close="modal = null">
      <form class="space-y-3" @submit.prevent="categorize">
        <input v-model="categorizeForm.internal_category" type="text" class="w-full rounded-md border-slate-300" placeholder="Internal category" />
        <select v-model="categorizeForm.source" class="w-full rounded-md border-slate-300">
          <option value="manual">Manual</option>
          <option value="rule">Rule</option>
        </select>
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Save</button>
      </form>
    </Modal>

    <Modal :open="modal === 'journal'" title="Link Journal Entry" @close="modal = null">
      <form class="space-y-3" @submit.prevent="linkJournal">
        <select v-model="linkForm.journal_entry_id" class="w-full rounded-md border-slate-300">
          <option value="">Select journal entry</option>
          <option v-for="entry in journalEntries" :key="entry.id" :value="entry.id">{{ entry.entry_number }} · {{ entry.description || '-' }}</option>
        </select>
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Link</button>
      </form>
    </Modal>

    <Modal :open="modal === 'edit'" title="Edit Transaction" @close="modal = null">
      <form class="space-y-3" @submit.prevent="updateTransaction">
        <select v-model="editForm.bank_account_id" class="w-full rounded-md border-slate-300">
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
        </select>
        <div class="grid gap-3 md:grid-cols-2">
          <input v-model="editForm.transaction_date" type="date" class="rounded-md border-slate-300" />
          <input v-model.number="editForm.amount" type="number" step="0.01" class="rounded-md border-slate-300" />
        </div>
        <select v-model="editForm.transaction_type" class="w-full rounded-md border-slate-300">
          <option value="deposit">Deposit</option>
          <option value="withdrawal">Withdrawal</option>
          <option value="transfer_in">Transfer In</option>
          <option value="transfer_out">Transfer Out</option>
          <option value="fee">Fee</option>
          <option value="interest">Interest</option>
          <option value="other">Other</option>
        </select>
        <input v-model="editForm.description" type="text" class="w-full rounded-md border-slate-300" placeholder="Description" />
        <input v-model="editForm.reference" type="text" class="w-full rounded-md border-slate-300" placeholder="Reference" />
        <input v-model="editForm.external_category" type="text" class="w-full rounded-md border-slate-300" placeholder="External category" />
        <input v-model="editForm.internal_category" type="text" class="w-full rounded-md border-slate-300" placeholder="Internal category" />
        <select v-model="editForm.status" class="w-full rounded-md border-slate-300">
          <option value="pending">Pending</option>
          <option value="categorized">Categorized</option>
          <option value="reconciled">Reconciled</option>
        </select>
        <textarea v-model="editForm.notes" rows="2" class="w-full rounded-md border-slate-300" placeholder="Notes" />
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Update</button>
      </form>
    </Modal>
  </AppLayout>
</template>
