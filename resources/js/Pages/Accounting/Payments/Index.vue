<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({ payments: Object });
</script>

<template>
  <AppLayout>
    <!-- Implements Payments Index from accounting-views-and-actions.md -->
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold">Payments</h2>
      <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600">
            <tr>
              <th class="px-4 py-3">Payment #</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Method</th>
              <th class="px-4 py-3">Amount</th>
              <th class="px-4 py-3">Direction</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in payments.data" :key="payment.id" class="border-t border-slate-200">
              <td class="px-4 py-3">{{ payment.payment_number }}</td>
              <td class="px-4 py-3">{{ payment.payment_date }}</td>
              <td class="px-4 py-3">{{ payment.method }}</td>
              <td class="px-4 py-3">{{ payment.amount }}</td>
              <td class="px-4 py-3">{{ payment.direction }}</td>
              <td class="px-4 py-3">{{ payment.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
