<template>
  <!-- Loading Skeleton Component -->
  <div v-if="isLoading" class="space-y-4">
    <div v-for="i in count" :key="i" class="animate-pulse">
      <div v-if="type === 'text'" class="h-4 bg-gray-300 rounded w-3/4"></div>
      <div v-else-if="type === 'card'" class="bg-gray-300 rounded-lg h-48"></div>
      <div v-else-if="type === 'table'">
        <div class="grid grid-cols-4 gap-4 mb-2">
          <div class="h-4 bg-gray-300 rounded"></div>
          <div class="h-4 bg-gray-300 rounded"></div>
          <div class="h-4 bg-gray-300 rounded"></div>
          <div class="h-4 bg-gray-300 rounded"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- Content -->
  <slot v-else></slot>
</template>

<script setup>
defineProps({
  isLoading: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'text', // text, card, table
  },
  count: {
    type: Number,
    default: 3,
  },
})
</script>
