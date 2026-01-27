<!-- 📁 resources/js/views/admin/LogAktivitasPage.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2 mb-1">
        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5-7l3.6-4.8 1.2 1.6H21V5h-4.4l-1.2 1.6L9 1 3 9.5 9 18l1.4-1.9-3.4-4.6z"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-900">Log Aktivitas Sistem</h1>
      </div>
      <p class="text-sm text-gray-600 ml-10">Pantau semua aktivitas dan perubahan dalam sistem</p>
    </div>

    <!-- Filter Panel (Always Visible) -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6 shadow-sm">
      <div class="flex flex-col lg:flex-row gap-4 items-end">
        <!-- Period Select -->
        <div class="shrink-0 min-w-max">
          <label class="block text-xs font-semibold text-gray-700 mb-2">Periode</label>
          <select
            v-model="filterDateDays"
            class="px-4 py-2.5 text-sm border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition bg-white cursor-pointer hover:border-gray-300 appearance-none pr-8 bg-no-repeat"
            style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.5rem center; background-size: 1.25rem;"
            @change="loadLogs"
          >
            <option value="1">Last 24h</option>
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="999">All Time</option>
          </select>
        </div>

        <!-- Action Select -->
        <div class="shrink-0 min-w-max">
          <label class="block text-xs font-semibold text-gray-700 mb-2">Tipe Aksi</label>
          <select
            v-model="filterAction"
            class="px-4 py-2.5 text-sm border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition bg-white cursor-pointer hover:border-gray-300 appearance-none pr-8 bg-no-repeat"
            style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.5rem center; background-size: 1.25rem;"
            @change="loadLogs"
          >
            <option value="">Semua Aksi</option>
            <option value="login">🔐 Login</option>
            <option value="logout">🚪 Logout</option>
            <option value="create">✨ Create</option>
            <option value="update">📝 Update</option>
            <option value="delete">🗑️ Delete</option>
          </select>
        </div>

        <!-- From Date -->
        <div class="shrink-0">
          <label class="block text-xs font-semibold text-gray-700 mb-2">Start Date</label>
          <input
            v-model="filterDateFrom"
            type="date"
            class="px-4 py-2.5 text-sm border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition bg-white cursor-pointer hover:border-gray-300"
            @change="loadLogs"
          />
        </div>

        <!-- To Date -->
        <div class="shrink-0">
          <label class="block text-xs font-semibold text-gray-700 mb-2">End Date</label>
          <input
            v-model="filterDateTo"
            type="date"
            class="px-4 py-2.5 text-sm border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition bg-white cursor-pointer hover:border-gray-300"
            @change="loadLogs"
          />
        </div>

        <!-- Search Box -->
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-semibold text-gray-700 mb-2">Search</label>
          <input
            v-model="filterSearch"
            type="text"
            placeholder="User / Deskripsi..."
            class="w-full px-4 py-2.5 text-sm border-2 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition bg-white hover:border-gray-300"
            @keyup.enter="loadLogs"
          />
        </div>

        <!-- Clear Button -->
        <button
          v-if="hasActiveFilters()"
          @click="clearAllFilters"
          class="shrink-0 px-5 py-2.5 text-sm font-semibold text-indigo-600 bg-indigo-50 border-2 border-indigo-200 rounded-lg hover:bg-indigo-100 transition"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <LoadingSpinner :isLoading="loading" message="Memuat log aktivitas..." />
    </div>

    <!-- Empty State -->
    <div v-else-if="logs.length === 0" class="text-center py-12">
      <div class="flex justify-center mb-3">
        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-1">Tidak Ada Log</h3>
      <p class="text-sm text-gray-600">Belum ada aktivitas yang sesuai dengan filter</p>
    </div>

    <!-- Table View -->
    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
      <!-- Desktop View - Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Waktu</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">User</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Deskripsi</th>
              <th class="px-4 py-3 text-center font-semibold text-gray-700">Detail</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900">{{ log.user?.name || '-' }}</p>
              </td>
              <td class="px-4 py-3">
                <span :class="getActionBadgeClass(log.action)" class="px-2 py-1 rounded-full text-xs font-semibold">
                  {{ log.action.toUpperCase() }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ log.description || '-' }}</td>
              <td class="px-4 py-3 text-center">
                <details v-if="log.data" class="inline">
                  <summary class="cursor-pointer text-indigo-600 hover:underline text-xs font-semibold">Lihat</summary>
                  <div class="absolute bg-gray-900 text-gray-100 rounded-lg p-3 mt-1 z-10 max-w-md">
                    <pre class="text-xs overflow-x-auto font-mono">{{ JSON.stringify(log.data, null, 2) }}</pre>
                  </div>
                </details>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile View - Cards -->
      <div class="md:hidden space-y-2 p-4">
        <div v-for="log in logs" :key="log.id" class="border border-gray-200 rounded-lg p-3 space-y-2">
          <div class="flex justify-between items-start gap-2">
            <div>
              <p class="font-semibold text-gray-900 text-sm">{{ log.user?.name || 'Unknown' }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(log.created_at) }}</p>
            </div>
            <span :class="getActionBadgeClass(log.action)" class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
              {{ log.action.toUpperCase() }}
            </span>
          </div>
          <p class="text-xs text-gray-700">{{ log.description || '-' }}</p>
          <details v-if="log.data" class="text-xs">
            <summary class="cursor-pointer text-indigo-600 hover:underline font-semibold">Lihat Detail</summary>
            <pre class="mt-2 p-2 bg-gray-900 text-gray-100 rounded text-xs overflow-x-auto font-mono">{{ JSON.stringify(log.data, null, 2) }}</pre>
          </details>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const loading = ref(false)
const filterSearch = ref('')
const filterAction = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')
const filterDateDays = ref('7')
const logs = ref([])

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return '-'
  }
}

const getActionBadgeClass = (action) => {
  const baseClass = 'inline-block'
  switch (action.toLowerCase()) {
    case 'login':
      return `${baseClass} bg-blue-100 text-blue-800`
    case 'logout':
      return `${baseClass} bg-red-100 text-red-800`
    case 'create':
      return `${baseClass} bg-green-100 text-green-800`
    case 'update':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'delete':
      return `${baseClass} bg-purple-100 text-purple-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

const hasActiveFilters = () => {
  return filterSearch.value || filterAction.value || filterDateFrom.value || filterDateTo.value || filterDateDays.value !== '7'
}

const getActiveFilterCount = () => {
  let count = 0
  if (filterSearch.value) count++
  if (filterAction.value) count++
  if (filterDateFrom.value) count++
  if (filterDateTo.value) count++
  if (filterDateDays.value !== '7') count++
  return count
}

const clearAllFilters = () => {
  filterSearch.value = ''
  filterAction.value = ''
  filterDateFrom.value = ''
  filterDateTo.value = ''
  filterDateDays.value = '7'
  loadLogs()
}

const loadLogs = async () => {
  loading.value = true
  try {
    const response = await adminAPI.getLogs({
      user: filterSearch.value ? filterSearch.value : undefined,
      action: filterAction.value || undefined,
      date_from: filterDateFrom.value || undefined,
      date_to: filterDateTo.value || undefined,
      days: !filterDateFrom.value && !filterDateTo.value ? filterDateDays.value : undefined
    })
    logs.value = response.data.data
  } catch (error) {
    console.error('Error loading logs:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadLogs()
})
</script>
