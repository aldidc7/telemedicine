<template>
  <!-- Profile Completion Progress Bar - Show in Dashboard/Header -->
  <div v-if="showBar && completion.percentage < 100" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
    <div class="flex items-start gap-4">
      <div class="shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
      </div>
      <div class="flex-1">
        <h3 class="text-sm font-semibold text-yellow-800">Lengkapi Profil Anda</h3>
        <p class="text-sm text-yellow-700 mt-1">
          Profil Anda baru {{ completion.percentage }}% lengkap. 
          <button
            @click="openModal"
            class="font-bold underline hover:text-yellow-600 transition"
          >
            Lengkapi sekarang
          </button>
        </p>
        
        <!-- Progress Bar -->
        <div class="mt-3 bg-yellow-200 rounded-full h-2 overflow-hidden">
          <div
            class="bg-linear-to-r from-yellow-500 to-orange-500 h-2 transition-all duration-300"
            :style="{ width: completion.percentage + '%' }"
          ></div>
        </div>
        <p class="text-xs text-yellow-600 mt-2">
          {{ completion.completed_count }} dari {{ completion.total_fields }} field sudah diisi
        </p>
      </div>
      
      <!-- Close Button -->
      <button
        @click="dismissBar"
        class="shrink-0 text-yellow-600 hover:text-yellow-800 transition"
        title="Tutup notifikasi"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { authApi } from '@/api/auth'

const emit = defineEmits(['open-modal'])

const showBar = ref(true)
const completion = ref({
  percentage: 0,
  completed_count: 0,
  total_fields: 0
})

const loadCompletion = async () => {
  try {
    const response = await authApi.getProfileCompletion()
    completion.value = response.data.data
    // Hide bar if 100% complete
    showBar.value = completion.value.percentage < 100
  } catch (error) {
    console.error('Error loading completion:', error)
  }
}

const dismissBar = () => {
  showBar.value = false
}

const openModal = () => {
  emit('open-modal')
}

onMounted(() => {
  loadCompletion()
})

defineExpose({
  loadCompletion
})
</script>
