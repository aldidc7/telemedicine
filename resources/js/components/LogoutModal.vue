<template>
  <!-- Logout Confirmation Modal -->
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
      <!-- Header -->
      <div class="bg-linear-to-r from-red-600 to-pink-600 text-white p-6">
        <h2 class="text-2xl font-bold flex items-center gap-3">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.82 1.573l-12.81 12.81A3 3 0 016 17.66V3z" clip-rule="evenodd"/>
          </svg>
          Logout
        </h2>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-4">
        <p class="text-gray-700 text-base">
          Apa yang ingin Anda lakukan?
        </p>

        <!-- Current Session Info -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
          <p class="text-xs text-gray-600 font-semibold mb-2">PERANGKAT SAAT INI</p>
          <p class="text-sm font-semibold text-gray-900">{{ currentDevice }}</p>
          <p class="text-xs text-gray-500">{{ currentIP }}</p>
        </div>

        <!-- Options -->
        <div class="space-y-3">
          <!-- Option 1: Logout Current Device -->
          <button
            @click="handleLogoutCurrent"
            :disabled="isLoading"
            class="w-full px-4 py-3 bg-yellow-50 hover:bg-yellow-100 border-2 border-yellow-300 text-yellow-700 rounded-lg font-semibold transition disabled:opacity-50 text-left"
          >
            <div class="flex items-center justify-between">
              <span>Logout Perangkat Ini</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H5"/>
              </svg>
            </div>
            <p class="text-xs text-yellow-600 mt-1">Hanya logout dari browser ini</p>
          </button>

          <!-- Option 2: Logout All Devices -->
          <button
            @click="handleLogoutAll"
            :disabled="isLoading"
            class="w-full px-4 py-3 bg-red-50 hover:bg-red-100 border-2 border-red-300 text-red-700 rounded-lg font-semibold transition disabled:opacity-50 text-left"
          >
            <div class="flex items-center justify-between">
              <span>Logout Semua Perangkat</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H5"/>
              </svg>
            </div>
            <p class="text-xs text-red-600 mt-1">Logout dari semua perangkat dan browser</p>
          </button>
        </div>

        <!-- Warning -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
          <p class="text-xs text-blue-700">
            <strong>⚠️ Peringatan:</strong> Logout semua perangkat akan memaksa logout dari semua browser yang sedang aktif.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 p-4 bg-gray-50">
        <button
          @click="handleCancel"
          class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold"
        >
          Batalkan
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { authApi } from '@/api/auth'

const router = useRouter()
const authStore = useAuthStore()

const isOpen = ref(false)
const isLoading = ref(false)
const currentDevice = ref('Unknown')
const currentIP = ref('...')

const show = () => {
  isOpen.value = true
  detectDevice()
}

const hide = () => {
  isOpen.value = false
}

const detectDevice = () => {
  // Detect browser
  const ua = navigator.userAgent
  if (ua.indexOf('Chrome') > -1) {
    currentDevice.value = 'Chrome'
  } else if (ua.indexOf('Firefox') > -1) {
    currentDevice.value = 'Firefox'
  } else if (ua.indexOf('Safari') > -1) {
    currentDevice.value = 'Safari'
  } else if (ua.indexOf('Edge') > -1) {
    currentDevice.value = 'Edge'
  }

  // Detect OS
  if (ua.indexOf('Windows') > -1) {
    currentDevice.value += ' on Windows'
  } else if (ua.indexOf('Mac') > -1) {
    currentDevice.value += ' on macOS'
  } else if (ua.indexOf('Linux') > -1) {
    currentDevice.value += ' on Linux'
  } else if (ua.indexOf('iPhone') > -1) {
    currentDevice.value += ' on iOS'
  } else if (ua.indexOf('Android') > -1) {
    currentDevice.value += ' on Android'
  }
}

const handleLogoutCurrent = async () => {
  isLoading.value = true
  try {
    await authApi.logout()
    
    // Clear auth store
    await authStore.logout()
    
    // Redirect to login
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    // Force redirect even if API fails
    await authStore.logout()
    router.push('/login')
  } finally {
    isLoading.value = false
  }
}

const handleLogoutAll = async () => {
  isLoading.value = true
  try {
    await authApi.logoutAll()
    
    // Clear auth store
    await authStore.logout()
    
    // Redirect to login with message
    router.push('/login?message=Anda%20telah%20logout%20dari%20semua%20perangkat')
  } catch (error) {
    console.error('Logout all error:', error)
    // Force redirect even if API fails
    await authStore.logout()
    router.push('/login')
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
  hide()
}

defineExpose({
  show,
  hide
})
</script>
