<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  metrics: Object,
  recentChangeLog: Array,
})

const kpis = [
  { label: 'Active Users',         value: props.metrics.total_active_users,      link: '/administration/users?is_active=1',         color: 'bg-blue-50 text-blue-700' },
  { label: 'Active AI Agents',     value: props.metrics.active_ai_agents,         link: '/administration/users?user_type=ai_agent',  color: 'bg-violet-50 text-violet-700' },
  { label: 'Open Tickets',         value: props.metrics.open_tickets,             link: '#',                                          color: 'bg-red-50 text-red-700' },
  { label: 'Pending Tasks',        value: props.metrics.pending_tasks,            link: '#',                                          color: 'bg-amber-50 text-amber-700' },
  { label: 'Change Log (24h)',     value: props.metrics.recent_change_log_count,  link: '/administration/change-log',                color: 'bg-gray-50 text-gray-700' },
  { label: 'Low Stock Products',   value: props.metrics.low_stock_products,       link: '/administration/inventory?low_stock=1',     color: 'bg-orange-50 text-orange-700' },
  { label: 'Active Services',      value: props.metrics.active_services,          link: '/administration/services?is_active=1',      color: 'bg-emerald-50 text-emerald-700' },
]

const quickLinks = [
  { label: 'Manage Users',                href: '/administration/users' },
  { label: 'Manage Settings',             href: '/administration/settings' },
  { label: 'Manage Services',             href: '/administration/services' },
  { label: 'Manage Products & Inventory', href: '/administration/products' },
  { label: 'View Change Log',             href: '/administration/change-log' },
]

const actionBadge = (action) => {
  const map = { create: 'bg-green-100 text-green-700', update: 'bg-blue-100 text-blue-700', delete: 'bg-red-100 text-red-700' }
  return map[action] ?? 'bg-gray-100 text-gray-700'
}
</script>

<template>
  <AppLayout title="Administration Dashboard">
    <div class="p-6 space-y-8">
      <!-- KPI Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
        <a v-for="kpi in kpis" :key="kpi.label" :href="kpi.link"
           :class="['rounded-xl p-4 text-center hover:opacity-80 transition', kpi.color]">
          <p class="text-2xl font-bold">{{ kpi.value }}</p>
          <p class="text-xs mt-1">{{ kpi.label }}</p>
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recent Activity</h2>
            <Link href="/administration/change-log" class="text-xs text-blue-600 hover:underline">View all</Link>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-for="entry in recentChangeLog" :key="entry.id" class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50">
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', actionBadge(entry.action)]">{{ entry.action }}</span>
              <span class="text-sm text-gray-700 flex-1">{{ entry.table_name }}</span>
              <span class="text-xs text-gray-400">{{ new Date(entry.created_at).toLocaleString() }}</span>
            </div>
            <p v-if="!recentChangeLog.length" class="px-4 py-6 text-sm text-gray-400 text-center">No recent activity.</p>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Quick Links</h2>
          </div>
          <div class="p-4 space-y-2">
            <a v-for="link in quickLinks" :key="link.href" :href="link.href"
               class="flex items-center gap-2 p-3 rounded-lg border border-gray-100 hover:border-blue-300 hover:bg-blue-50 transition text-sm text-gray-700">
              {{ link.label }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
