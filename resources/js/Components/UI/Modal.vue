<script setup>
import { computed } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
})

defineEmits(['close'])

const isOpen = computed(() => props.open || props.show)
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <button class="absolute inset-0 bg-black/40" @click="$emit('close')" />
    <div class="relative z-10 w-full max-w-xl rounded-xl bg-white p-6 shadow-2xl">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
        <button class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100" @click="$emit('close')">Close</button>
      </div>
      <slot />
    </div>
  </div>
</template>
