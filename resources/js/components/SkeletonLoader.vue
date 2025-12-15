<template>
  <div class="space-y-4">
    <!-- Skeleton Card -->
    <div v-if="type === 'card'" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
      <div class="flex gap-4">
        <div class="w-16 h-16 bg-gray-200 rounded-xl animate-pulse shrink-0"></div>
        <div class="flex-1 space-y-3">
          <div class="h-4 bg-gray-200 rounded-lg w-3/4 animate-pulse"></div>
          <div class="h-3 bg-gray-200 rounded-lg w-full animate-pulse"></div>
          <div class="h-3 bg-gray-200 rounded-lg w-5/6 animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Skeleton Table Row -->
    <div v-else-if="type === 'row'" class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
      <div class="flex items-center justify-between gap-4">
        <div class="flex-1 space-y-2">
          <div class="h-4 bg-gray-200 rounded w-1/2 animate-pulse"></div>
          <div class="h-3 bg-gray-200 rounded w-1/3 animate-pulse"></div>
        </div>
        <div class="h-10 w-24 bg-gray-200 rounded-lg animate-pulse"></div>
      </div>
    </div>

    <!-- Skeleton Form Input -->
    <div v-else-if="type === 'input'" class="space-y-2">
      <div class="h-4 bg-gray-200 rounded w-1/4 animate-pulse"></div>
      <div class="h-10 bg-gray-200 rounded-lg animate-pulse"></div>
    </div>

    <!-- Skeleton Avatar -->
    <div v-else-if="type === 'avatar'" class="w-12 h-12 bg-gray-200 rounded-full animate-pulse"></div>

    <!-- Skeleton Text -->
    <div v-else-if="type === 'text'" class="space-y-2">
      <div class="h-4 bg-gray-200 rounded w-full animate-pulse"></div>
      <div class="h-4 bg-gray-200 rounded w-5/6 animate-pulse"></div>
    </div>

    <!-- Skeleton Button -->
    <div v-else-if="type === 'button'" class="h-10 bg-gray-200 rounded-lg animate-pulse w-full"></div>

    <!-- Grid Skeleton (Multiple Cards) -->
    <div v-else-if="type === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex gap-4">
          <div class="w-16 h-16 bg-gray-200 rounded-xl animate-pulse shrink-0"></div>
          <div class="flex-1 space-y-3">
            <div class="h-4 bg-gray-200 rounded-lg w-3/4 animate-pulse"></div>
            <div class="h-3 bg-gray-200 rounded-lg w-full animate-pulse"></div>
            <div class="h-3 bg-gray-200 rounded-lg w-5/6 animate-pulse"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'card',
    validator: (value) => ['card', 'row', 'input', 'avatar', 'text', 'button', 'grid'].includes(value)
  }
});
</script>
