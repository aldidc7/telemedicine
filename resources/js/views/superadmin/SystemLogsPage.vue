<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-3xl font-bold text-gray-900">Audit Log Sistem</h1>
        <p class="text-gray-600 mt-1">Pantau semua aktivitas admin dan perubahan sistem</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Cari action, resource, IP..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
            <div v-if="searching" class="absolute right-3 top-2.5">
              <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
            </div>
          </div>

          <!-- Filter by Action -->
          <select
            v-model="filterAction"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Semua Action</option>
            <option value="create">Create</option>
            <option value="read">Read</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
            <option value="download">Download</option>
            <option value="export">Export</option>
          </select>

          <!-- Filter by Resource -->
          <select
            v-model="filterResource"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Semua Resource</option>
            <option value="dokter">Dokter</option>
            <option value="pasien">Pasien</option>
            <option value="user">User</option>
            <option value="konsultasi">Konsultasi</option>
            <option value="config">Config</option>
          </select>

          <!-- Filter by Status -->
          <select
            v-model="filterStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          >
            <option value="">Semua Status</option>
            <option value="success">Success</option>
            <option value="failed">Failed</option>
          </select>
        </div>

        <!-- Clear Filters Button -->
        <div class="mt-3">
          <button
            @click="clearFilters"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-sm font-semibold"
          >
            Reset Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Error Alert -->
      <ErrorAlert v-if="errorMessage" :message="errorMessage" />

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <LoadingSpinner />
      </div>

      <!-- Logs Table -->
      <div v-else-if="logs.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Waktu</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Admin</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Resource</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Resource ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">IP Address</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Detail</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="flex flex-col gap-1">
                    <span class="font-medium">{{ formatDate(log.created_at) }}</span>
                    <span class="text-xs text-gray-500">{{ formatTime(log.created_at) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="flex flex-col gap-1">
                    <span class="font-semibold">{{ log.admin?.name || 'Unknown' }}</span>
                    <span class="text-xs text-gray-500">{{ log.admin?.email }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getActionBadgeColor(log.action)]">
                    {{ log.action.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getResourceBadgeColor(log.resource)]">
                    {{ log.resource.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 font-mono">
                  {{ log.resource_id || '-' }}
                </td>
                <td class="px-6 py-4 text-xs text-gray-500 font-mono">
                  {{ log.ip_address }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <span v-if="log.status === 'success'" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                    SUCCESS
                  </span>
                  <span v-else class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                    FAILED
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <button
                    v-if="log.changes"
                    @click="showDetails(log)"
                    class="text-indigo-600 hover:text-indigo-700 font-semibold"
                  >
                    Lihat
                  </button>
                  <span v-else class="text-gray-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16 bg-white rounded-lg">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-gray-500 text-lg font-semibold">Tidak ada log ditemukan</p>
      </div>

      <!-- Pagination -->
      <div v-if="logs.length > 0" class="mt-8 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="text-gray-600 text-sm">
          Halaman <span class="font-semibold">{{ currentPage }}</span> dari <span class="font-semibold">{{ totalPages }}</span>
        </div>
        <div class="flex gap-2">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            ← Sebelumnya
          </button>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            Selanjutnya →
          </button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="relative p-6 border-b border-gray-200">
          <h3 class="text-lg font-bold text-gray-900">Detail Perubahan</h3>
          <button
            @click="showDetailModal = false"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
          >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-6">
          <pre class="bg-gray-100 p-4 rounded-lg text-xs overflow-x-auto">{{ JSON.stringify(selectedLog?.changes, null, 2) }}</pre>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end">
          <button
            @click="showDetailModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { adminAPI } from '@/api/admin'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import ErrorHandler from '@/utils/ErrorHandler'

const logs = ref([])
const loading = ref(false)
const errorMessage = ref(null)
const searching = ref(false)
const search = ref('')
const filterAction = ref('')
const filterResource = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const showDetailModal = ref(false)
const selectedLog = ref(null)

/**
 * Load system logs
 */
const loadLogs = async () => {
  loading.value = true
  errorMessage.value = null
  searching.value = true

  try {
    const response = await adminAPI.getSystemLogs({
      page: currentPage.value,
      per_page: 25,
      search: search.value || undefined,
      action: filterAction.value || undefined,
      resource: filterResource.value || undefined,
      status: filterStatus.value || undefined,
    })

    logs.value = response.data.data
    currentPage.value = response.data.current_page
    totalPages.value = response.data.last_page
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  } finally {
    loading.value = false
    searching.value = false
  }
}

/**
 * Format date
 */
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

/**
 * Format time
 */
const formatTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

/**
 * Get action badge color
 */
const getActionBadgeColor = (action) => {
  const colors = {
    create: 'bg-green-100 text-green-800',
    update: 'bg-blue-100 text-blue-800',
    delete: 'bg-red-100 text-red-800',
    read: 'bg-gray-100 text-gray-800',
    view: 'bg-gray-100 text-gray-800',
    download: 'bg-purple-100 text-purple-800',
    export: 'bg-purple-100 text-purple-800',
  }
  return colors[action] || 'bg-gray-100 text-gray-800'
}

/**
 * Get resource badge color
 */
const getResourceBadgeColor = (resource) => {
  const colors = {
    dokter: 'bg-indigo-100 text-indigo-800',
    pasien: 'bg-green-100 text-green-800',
    user: 'bg-orange-100 text-orange-800',
    konsultasi: 'bg-blue-100 text-blue-800',
    config: 'bg-purple-100 text-purple-800',
  }
  return colors[resource] || 'bg-gray-100 text-gray-800'
}

/**
 * Show details modal
 */
const showDetails = (log) => {
  selectedLog.value = log
  showDetailModal.value = true
}

/**
 * Clear filters
 */
const clearFilters = async () => {
  search.value = ''
  filterAction.value = ''
  filterResource.value = ''
  filterStatus.value = ''
  currentPage.value = 1
  await loadLogs()
}

/**
 * Pagination
 */
const nextPage = async () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    await loadLogs()
  }
}

const prevPage = async () => {
  if (currentPage.value > 1) {
    currentPage.value--
    await loadLogs()
  }
}

/**
 * Debounce search
 */
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

const debouncedSearch = debounce(async () => {
  currentPage.value = 1
  await loadLogs()
}, 500)

// Watchers
watch(search, () => { debouncedSearch() })
watch(filterAction, async () => { currentPage.value = 1; await loadLogs() })
watch(filterResource, async () => { currentPage.value = 1; await loadLogs() })
watch(filterStatus, async () => { currentPage.value = 1; await loadLogs() })

onMounted(() => {
  loadLogs()
})
</script>
