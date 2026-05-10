<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import Modal from '../../../Components/UI/Modal.vue';

const props = defineProps({ reconciliation: Object, unreconciledTransactions: Array, matchedTransactions: Array });
const modal = ref(null);

const matchForm = useForm({ transaction_id: '' });
const adjustmentForm = useForm({ transaction_date: new Date().toISOString().slice(0, 10), description: 'Reconciliation adjustment', internal_category: 'reconciliation_adjustment', notes: '' });

const matchTransaction = () => matchForm.post(`/banking/reconciliations/${props.reconciliation.id}/match-transaction`, { onSuccess: () => (modal.value = null) });
const createAdjustment = () => adjustmentForm.post(`/banking/reconciliations/${props.reconciliation.id}/create-adjustment`, { onSuccess: () => (modal.value = null) });
const complete = () => useForm({}).post(`/banking/reconciliations/${props.reconciliation.id}/complete`);
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold">Bank Reconciliation</h2>
          <p class="text-sm text-slate-600">Statement Date: {{ reconciliation.statement_date }} · Status: {{ reconciliation.status }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'match'">Match Transaction</button>
          <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="modal = 'adjust'">Create Adjustment</button>
          <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white" @click="complete">Complete Reconciliation</button>
        </div>
      </div>

      <section class="grid gap-4 rounded-xl bg-white p-5 shadow-sm md:grid-cols-5 text-sm">
        <p><span class="text-xs text-slate-500">Statement Balance</span><br>{{ reconciliation.statement_balance }}</p>
        <p><span class="text-xs text-slate-500">Book Balance</span><br>{{ reconciliation.book_balance }}</p>
        <p><span class="text-xs text-slate-500">Difference</span><br>{{ reconciliation.difference }}</p>
        <p><span class="text-xs text-slate-500">Status</span><br>{{ reconciliation.status }}</p>
        <p><span class="text-xs text-slate-500">Notes</span><br>{{ reconciliation.notes || '-' }}</p>
      </section>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Unreconciled Transactions</h3>
          <div class="space-y-2 text-sm">
            <div v-for="item in unreconciledTransactions" :key="item.id" class="rounded-md border border-slate-200 p-2">
              {{ item.transaction_date }} · {{ item.description || '-' }} · {{ item.amount }}
            </div>
            <p v-if="unreconciledTransactions.length === 0" class="text-slate-500">No unreconciled transactions.</p>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Matched Items</h3>
          <div class="space-y-2 text-sm">
            <div v-for="item in matchedTransactions" :key="item.id" class="rounded-md border border-slate-200 p-2">
              {{ item.transaction_date }} · {{ item.description || '-' }} · {{ item.amount }}
            </div>
            <p v-if="matchedTransactions.length === 0" class="text-slate-500">No matched items yet.</p>
          </div>
        </div>
      </section>
    </div>

    <Modal :open="modal === 'match'" title="Match Transaction" @close="modal = null">
      <form class="space-y-3" @submit.prevent="matchTransaction">
        <select v-model="matchForm.transaction_id" class="w-full rounded-md border-slate-300">
          <option value="">Select transaction</option>
          <option v-for="item in unreconciledTransactions" :key="item.id" :value="item.id">{{ item.transaction_date }} · {{ item.description || '-' }} · {{ item.amount }}</option>
        </select>
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Match</button>
      </form>
    </Modal>

    <Modal :open="modal === 'adjust'" title="Create Adjustment" @close="modal = null">
      <form class="space-y-3" @submit.prevent="createAdjustment">
        <input v-model="adjustmentForm.transaction_date" type="date" class="w-full rounded-md border-slate-300" />
        <input v-model="adjustmentForm.description" type="text" class="w-full rounded-md border-slate-300" placeholder="Description" />
        <input v-model="adjustmentForm.internal_category" type="text" class="w-full rounded-md border-slate-300" placeholder="Internal category" />
        <textarea v-model="adjustmentForm.notes" rows="2" class="w-full rounded-md border-slate-300" placeholder="Notes" />
        <button class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Create</button>
      </form>
    </Modal>
  </AppLayout>
</template>
