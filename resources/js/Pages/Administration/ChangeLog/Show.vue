<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({ change_log: Object })

const actionBadge = (a) => ({
  create: 'bg-green-100 text-green-700',
  update: 'bg-blue-100 text-blue-700',
  delete: 'bg-red-100 text-red-700',
})[a] ?? 'bg-gray-100 text-gray-700'

const formatted = JSON.stringify(props.change_log.changes, null, 2)
</script>

<template>
  <AppLayout title="Change Log Detail">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
      <div class="flex items-center gap-3 mb-2">
        <Link href="/administration/change-log" class="text-blue-600 hover:underline text-sm">← Change Log</Link>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-4">
          <span :class="['text-sm font-medium px-3 py-1 rounded-full', actionBadge(change_log.action)]">{{ change_log.action }}</span>
          <span class="font-mono text-gray-700 font-semibold">{{ change_log.table_name }}</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
          <div><span class="text-gray-400 block text-xs">User</span>{{ change_log.user?.full_name ?? 'System' }}</div>
          <div><span class="text-gray-400 block text-xs">Record ID</span><span class="font-mono text-xs">{{ change_log.record_id }}</span></div>
          <div><span class="text-gray-400 block text-xs">Timestamp</span>{{ new Date(change_log.created_at).toLocaleString() }}</div>
        </div>

        <div>
          <p class="text-xs font-medium text-gray-500 mb-2 uppercase tracking-wide">Changes</p>
          <pre class="bg-gray-50 rounded-lg p-4 text-xs text-gray-700 overflow-auto max-h-96 font-mono">{{ formatted }}</pre>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
