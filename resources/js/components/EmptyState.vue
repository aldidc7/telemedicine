<!-- 📁 resources/js/components/EmptyState.vue -->
<template>
  <div class="text-center py-12">
    <div class="flex justify-center mb-4">
      <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
    </div>
    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ title }}</h3>
    <p class="text-gray-600 mb-6">{{ message }}</p>
    <router-link v-if="actionLink" :to="actionLink" class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
      {{ actionText }}
    </router-link>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Tidak Ada Data'
  },
  message: {
    type: String,
    default: 'Tidak ada data yang ditampilkan'
  },
  actionText: {
    type: String,
    default: null
  },
  actionLink: {
    type: String,
    default: null
  }
})
</script>