<!-- 📁 resources/js/views/admin/LogAktivitasPage.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-10">
      <div class="flex items-center gap-3 mb-2">
        <svg class="w-11 h-11 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5-7l3.6-4.8 1.2 1.6H21V5h-4.4l-1.2 1.6L9 1 3 9.5 9 18l1.4-1.9-3.4-4.6z"/>
        </svg>
        <h1 class="text-4xl font-bold text-gray-900">Log Aktivitas Sistem</h1>
      </div>
      <p class="text-gray-600">Pantau semua aktivitas dan perubahan dalam sistem</p>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 hover:shadow-lg transition">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filterUser"
          type="text"
          placeholder="Cari pengguna..."
          class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 transition text-gray-700"
          @keyup.enter="loadLogs"
        />
        <select
          v-model="filterAction"
          class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 transition text-gray-700"
        >
          <option value="">Semua Aksi</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
          <option value="create">Create</option>
          <option value="update">Update</option>
          <option value="delete">Delete</option>
        </select>
        <button
          @click="loadLogs"
          class="px-4 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold flex items-center gap-2 whitespace-nowrap"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 9h-4v4h4v-4zm0-5h-4v4h4V7z"/>
          </svg>
          Filter
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <LoadingSpinner message="Memuat log aktivitas..." />
    </div>

    <!-- Empty State -->
    <div v-else-if="logs.length === 0" class="text-center py-16">
      <div class="flex justify-center mb-4">
        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z M9 3v2m6-2v2\" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Log</h3>
      <p class="text-gray-600">Belum ada aktivitas tercatat</p>
    </div>

    <!-- Timeline -->
    <div v-else class="space-y-4">
      <div
        v-for="log in logs"
        :key="log.id"
        class="bg-white rounded-2xl shadow-sm border-l-4 border-indigo-600 p-8 hover:shadow-lg transition"
      >
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-4">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="{
                'bg-blue-100': log.action === 'login',
                'bg-red-100': log.action === 'logout',
                'bg-green-100': log.action === 'create',
                'bg-yellow-100': log.action === 'update',
                'bg-purple-100': log.action === 'delete',
                'bg-gray-100': !['login', 'logout', 'create', 'update', 'delete'].includes(log.action)
              }">
                <svg v-if="log.action === 'login'" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h12.5M11 3H7a2 2 0 00-2 2v14a2 2 0 002 2h4" />
                </svg>
                <svg v-else-if="log.action === 'logout'" class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <svg v-else-if="log.action === 'create'" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <svg v-else-if="log.action === 'update'" class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <svg v-else-if="log.action === 'delete'" class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z" />
                </svg>
                <svg v-else class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="1" />
                </svg>
              </div>
              <h3 class="font-bold text-lg text-gray-900 uppercase tracking-wide">{{ log.action }}</h3>
            </div>
            <p class="text-indigo-600 font-semibold flex items-center gap-2">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
              </svg>
              {{ log.user?.name || 'Unknown User' }}
            </p>
          </div>
          <span class="text-sm text-gray-500 bg-gray-100 px-4 py-2 rounded-lg whitespace-nowrap flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 1.5m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ new Date(log.created_at).toLocaleDateString('id-ID', {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
            }) }}
          </span>
        </div>

        <p class="text-gray-700 mb-4 bg-blue-50 border-l-2 border-blue-500 px-4 py-3 rounded">{{ log.description }}</p>

        <details v-if="log.data" class="text-sm">
          <summary class="cursor-pointer text-indigo-600 hover:underline font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Lihat Detail Data
          </summary>
          <pre class="mt-3 p-4 bg-gray-900 text-gray-100 rounded-lg text-xs overflow-x-auto font-mono">{{ JSON.stringify(log.data, null, 2) }}</pre>
        </details>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const loading = ref(false)
const filterUser = ref('')
const filterAction = ref('')
const logs = ref([])

onMounted(() => {
  loadLogs()
})

const loadLogs = async () => {
  loading.value = true
  try {
    const response = await adminAPI.getLogs({
      user: filterUser.value || undefined,
      action: filterAction.value || undefined
    })
    logs.value = response.data.data
  } catch (error) {
    console.error('Error loading logs:', error)
  } finally {
    loading.value = false
  }
}
</script>
