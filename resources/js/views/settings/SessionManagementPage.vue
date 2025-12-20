<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-gray-100 pb-12">
    <!-- Header -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-10 mb-8">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 mb-2">
          <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L11 14H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1118 8zm-6-4a1 1 0 100 2 4 4 0 010 8 1 1 0 000 2 6 6 0 000-12z" clip-rule="evenodd"/>
          </svg>
          <h1 class="text-3xl font-bold">Manajemen Sesi</h1>
        </div>
        <p class="text-indigo-100">Lihat dan kelola semua perangkat yang terhubung dengan akun Anda</p>
      </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Alert -->
      <div v-if="error" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
        <p class="text-red-700 text-sm font-medium">{{ error }}</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse">
          <div class="h-24 bg-gray-200 rounded-lg"></div>
        </div>
        <div class="animate-pulse">
          <div class="h-24 bg-gray-200 rounded-lg"></div>
        </div>
      </div>

      <!-- Sessions List -->
      <div v-else-if="sessions.length > 0" class="space-y-4">
        <!-- Current Session -->
        <div v-for="(session, index) in sessions" :key="session.id" class="bg-white rounded-lg shadow hover:shadow-lg transition border-l-4" :class="session.is_active ? 'border-green-500' : 'border-gray-300'">
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex items-start gap-4">
                <!-- Device Icon -->
                <div class="shrink-0">
                  <div class="flex items-center justify-center w-12 h-12 rounded-lg" :class="session.is_active ? 'bg-green-100' : 'bg-gray-100'">
                    <svg class="w-6 h-6" :class="session.is_active ? 'text-green-600' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM15 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z"/>
                    </svg>
                  </div>
                </div>

                <!-- Device Info -->
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-lg font-bold text-gray-900">
                      {{ getBrowserName(session.user_agent) }} on {{ getOsName(session.user_agent) }}
                    </h3>
                    <span v-if="isCurrentSession(session.token)" class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">
                      PERANGKAT INI
                    </span>
                    <span v-if="session.is_active" class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">
                      AKTIF
                    </span>
                    <span v-else class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded">
                      TIDAK AKTIF
                    </span>
                  </div>

                  <!-- Details -->
                  <div class="space-y-1 text-sm text-gray-600">
                    <p>
                      <strong>IP Address:</strong>
                      {{ session.ip_address }}
                    </p>
                    <p v-if="session.device_name">
                      <strong>Nama Perangkat:</strong>
                      {{ session.device_name }}
                    </p>
                    <p>
                      <strong>Login:</strong>
                      {{ formatDate(session.created_at) }}
                    </p>
                    <p v-if="session.last_activity_at">
                      <strong>Aktivitas Terakhir:</strong>
                      {{ formatRelativeTime(session.last_activity_at) }}
                    </p>
                    <p v-if="!session.is_active">
                      <strong>Logout:</strong>
                      {{ formatDate(session.updated_at) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="shrink-0">
                <button
                  v-if="session.is_active && !isCurrentSession(session.token)"
                  @click="handleLogoutSession(session.id)"
                  :disabled="actionLoading"
                  class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-semibold text-sm transition disabled:opacity-50"
                >
                  {{ actionLoading ? 'Loading...' : 'Logout' }}
                </button>
                <span v-else class="text-gray-400 text-sm">-</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Logout All Button -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
          <h3 class="text-lg font-bold text-gray-900 mb-2">Logout dari Semua Perangkat</h3>
          <p class="text-gray-600 text-sm mb-4">
            Ini akan logout Anda dari semua perangkat dan browser yang sedang aktif. Anda harus login kembali.
          </p>
          <button
            @click="handleLogoutAll"
            :disabled="actionLoading"
            class="px-6 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg font-semibold transition disabled:opacity-50"
          >
            {{ actionLoading ? 'Processing...' : 'Logout Semua Perangkat' }}
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center border border-gray-100">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m0 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Sesi</h3>
        <p class="text-gray-600">Tidak ada sesi aktif yang ditemukan.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { authApi } from '@/api/auth'

const loading = ref(false)
const actionLoading = ref(false)
const error = ref(null)
const sessions = ref([])
const currentToken = ref(localStorage.getItem('token'))

onMounted(() => {
  loadSessions()
})

const loadSessions = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await authApi.getSessions()
    sessions.value = response.data.data || []
  } catch (err) {
    error.value = 'Gagal memuat daftar sesi'
    console.error('Load sessions error:', err)
  } finally {
    loading.value = false
  }
}

const isCurrentSession = (token) => {
  return token === currentToken.value
}

const getBrowserName = (userAgent) => {
  if (userAgent.indexOf('Chrome') > -1) return 'Chrome'
  if (userAgent.indexOf('Firefox') > -1) return 'Firefox'
  if (userAgent.indexOf('Safari') > -1) return 'Safari'
  if (userAgent.indexOf('Edge') > -1) return 'Edge'
  return 'Unknown Browser'
}

const getOsName = (userAgent) => {
  if (userAgent.indexOf('Windows') > -1) return 'Windows'
  if (userAgent.indexOf('Mac') > -1) return 'macOS'
  if (userAgent.indexOf('Linux') > -1) return 'Linux'
  if (userAgent.indexOf('iPhone') > -1) return 'iOS'
  if (userAgent.indexOf('Android') > -1) return 'Android'
  return 'Unknown OS'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatRelativeTime = (date) => {
  const now = new Date()
  const then = new Date(date)
  const diff = Math.floor((now - then) / 1000)
  
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`
  return `${Math.floor(diff / 86400)} hari lalu`
}

const handleLogoutSession = async (sessionId) => {
  if (!confirm('Logout perangkat ini?')) return
  
  actionLoading.value = true
  try {
    await authApi.logoutSession(sessionId)
    await loadSessions()
  } catch (err) {
    error.value = 'Gagal logout perangkat'
    console.error('Logout session error:', err)
  } finally {
    actionLoading.value = false
  }
}

const handleLogoutAll = async () => {
  if (!confirm('Logout dari SEMUA perangkat? Anda harus login kembali.')) return
  
  actionLoading.value = true
  try {
    await authApi.logoutAll()
    // Redirect to login
    window.location.href = '/login'
  } catch (err) {
    error.value = 'Gagal logout semua perangkat'
    console.error('Logout all error:', err)
  } finally {
    actionLoading.value = false
  }
}
</script>
