<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  stats: Object,
  recentInvoices: Array,
  recentBills: Array,
  pendingPayments: Array,
  quickLinks: Array,
});
</script>

<template>
  <AppLayout>
    <!-- Implements Accounting Dashboard from accounting-views-and-actions.md -->
    <div class="space-y-6">
      <h2 class="text-2xl font-semibold text-slate-900">Accounting Dashboard</h2>

      <section class="grid gap-4 md:grid-cols-4">
        <div v-for="(value, label) in stats" :key="label" class="rounded-xl bg-white p-4 shadow-sm">
          <p class="text-xs uppercase tracking-wide text-slate-500">{{ label }}</p>
          <p class="mt-2 text-xl font-semibold" :class="label.includes('overdue') ? 'text-red-600' : 'text-slate-900'">{{ value }}</p>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Recent Invoices</h3>
          <div class="space-y-2 text-sm">
            <a v-for="invoice in recentInvoices" :key="invoice.id" :href="`/accounting/invoices/${invoice.id}`" class="block rounded-md border border-slate-200 p-2 hover:bg-slate-50">
              {{ invoice.invoice_number }} · {{ invoice.contact_name }} · {{ invoice.status }}
            </a>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Recent Bills</h3>
          <div class="space-y-2 text-sm">
            <a v-for="bill in recentBills" :key="bill.id" :href="`/accounting/bills/${bill.id}`" class="block rounded-md border border-slate-200 p-2 hover:bg-slate-50">
              {{ bill.bill_number }} · {{ bill.vendor_name }} · {{ bill.status }}
            </a>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h3 class="mb-3 font-semibold">Pending Payments</h3>
          <div class="space-y-2 text-sm">
            <div v-for="payment in pendingPayments" :key="payment.id" class="rounded-md border border-slate-200 p-2">
              {{ payment.payment_number }} · {{ payment.amount }} · {{ payment.method }}
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-xl bg-white p-4 shadow-sm">
        <h3 class="mb-3 font-semibold">Quick Links</h3>
        <div class="grid gap-3 md:grid-cols-4">
          <a v-for="link in quickLinks" :key="link.href" :href="link.href" class="rounded-md border border-slate-200 p-3 text-sm hover:bg-slate-50">{{ link.label }}</a>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
