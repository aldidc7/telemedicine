<!-- 📁 resources/js/views/admin/ManagePasienPage.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-10">
      <div class="flex items-center gap-3 mb-2">
        <svg class="w-11 h-11 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
        </svg>
        <h1 class="text-4xl font-bold text-gray-900">Manajemen Pasien</h1>
      </div>
      <p class="text-gray-600">Kelola semua data pasien di sistem telemedicine</p>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 hover:shadow-lg transition">
      <div class="flex flex-col md:flex-row gap-4 items-end">
        <!-- Search Input with Live Search Indicator -->
        <div class="flex-1 relative">
          <input
            id="search-pasien"
            name="search"
            v-model="search"
            type="text"
            placeholder="Cari nama, email, MRN (real-time)..."
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 transition text-gray-700"
            @keyup.enter="loadData"
          />
          <!-- Search Indicator -->
          <div v-if="searching" class="absolute right-3 top-3 flex items-center gap-1">
            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
            <span class="text-xs text-indigo-600 font-medium">Searching...</span>
          </div>
        </div>

        <!-- Filter Status -->
        <select
          id="filter-status"
          name="status"
          v-model="filterStatus"
          class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 transition text-gray-700"
        >
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>

        <!-- Clear Button -->
        <button
          @click="clearFilters"
          :disabled="!search && !filterStatus"
          class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Clear
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <LoadingSpinner :isLoading="loading" message="Memuat data pasien..." />
    </div>

    <!-- Empty State -->
    <div v-else-if="pasienList.length === 0" class="text-center py-16">
      <div class="flex justify-center mb-4">
        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Pasien</h3>
      <p class="text-gray-600">Belum ada data pasien dalam sistem</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-linear-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <tr>
              <th class="px-8 py-4 text-left text-sm font-bold text-gray-700">MRN / Nama</th>
              <th class="px-8 py-4 text-left text-sm font-bold text-gray-700">Email</th>
              <th class="px-8 py-4 text-left text-sm font-bold text-gray-700">Telepon</th>
              <th class="px-8 py-4 text-center text-sm font-bold text-gray-700">Status</th>
              <th class="px-8 py-4 text-center text-sm font-bold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="pasien in pasienList" :key="pasien.id" class="hover:bg-indigo-50 transition">
              <td class="px-8 py-5">
                <div class="flex flex-col gap-1">
                  <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 12a3 3 0 110-6 3 3 0 010 6zm0 0a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z" />
                    </svg>
                    <span class="font-semibold text-gray-900">{{ pasien.pengguna?.name || pasien.nama }}</span>
                  </div>
                  <span class="text-xs text-indigo-600 font-mono ml-7">{{ pasien.medical_record_number || 'N/A' }}</span>
                </div>
              </td>
              <td class="px-8 py-5 text-sm text-gray-600">{{ pasien.pengguna?.email || pasien.email }}</td>
              <td class="px-8 py-5 text-sm text-gray-600">{{ pasien.phone_number || '-' }}</td>
              <td class="px-8 py-5 text-center">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1',
                  pasien.pengguna?.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]">
                  <svg v-if="pasien.pengguna?.is_active" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                  </svg>
                  {{ pasien.pengguna?.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-8 py-5 text-center">
                <div class="flex gap-2 justify-center flex-wrap">
                  <button
                    @click="goToProfile(pasien.id)"
                    class="px-3 py-2 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition flex items-center gap-1"
                    title="Lihat profil pasien"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat
                  </button>
                  <button
                    @click="toggleStatus(pasien, pasien.pengguna?.is_active)"
                    :class="[
                      'px-3 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1',
                      pasien.pengguna?.is_active
                        ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
                        : 'bg-green-100 text-green-800 hover:bg-green-200'
                    ]"
                  >
                    <svg v-if="!pasien.pengguna?.is_active" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                    </svg>
                    {{ pasien.pengguna?.is_active ? 'Nonaktif' : 'Aktif' }}
                  </button>
                  <button
                    @click="hapus(pasien.id)"
                    class="px-3 py-2 rounded-lg text-xs font-bold bg-red-100 text-red-800 hover:bg-red-200 transition flex items-center gap-1"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-9l-1 1H5v2h14V4z" />
                    </svg>
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { adminAPI } from '@/api/admin'
import { pasienAPI } from '@/api/pasien'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import SuccessAlert from '@/components/SuccessAlert.vue'

/**
 * Debounce utility - delay function execution
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

const loading = ref(false)
const searching = ref(false)
const search = ref('')
const filterStatus = ref('')
const pasienList = ref([])
const errorMessage = ref(null)
const successMessage = ref(null)

onMounted(() => {
  loadData()
})

/**
 * Debounced search function - triggers after 500ms of inactivity
 */
const debouncedSearch = debounce(async () => {
  await loadData()
}, 500)

/**
 * Clear all filters and reset
 */
const clearFilters = async () => {
  search.value = ''
  filterStatus.value = ''
  await loadData()
}

const loadData = async () => {
  loading.value = true
  try {
    searching.value = true
    const response = await pasienAPI.getList({ 
      search: search.value,
      status: filterStatus.value
    })
    pasienList.value = response.data.data
    searching.value = false
  } catch (error) {
    searching.value = false
    errorMessage.value = 'Gagal memuat data pasien'
    console.error('Error loading pasien:', error)
  } finally {
    loading.value = false
  }
}

// Watch for search changes - live search with debounce
watch(search, () => {
  debouncedSearch()
})

// Watch for filter changes - immediate search
watch(filterStatus, async () => {
  await loadData()
})

const goToProfile = (pasienId) => {
  // Navigate to pasien detail page
  window.location.href = `/admin/pasien/${pasienId}`
}

const toggleStatus = async (pasienData, isActive) => {
  try {
    if (isActive) {
      await adminAPI.deactivateUser(pasienData.user_id)
    } else {
      await adminAPI.activateUser(pasienData.user_id)
    }
    successMessage.value = 'Status pasien berhasil diubah'
    await loadData()
  } catch (error) {
    errorMessage.value = 'Gagal mengubah status pasien'
    console.error('Error toggling status:', error)
  }
}

const hapus = async (pasienId) => {
  if (!confirm('Yakin ingin menghapus pasien ini? Data tidak bisa dipulihkan.')) return

  try {
    await pasienAPI.delete(pasienId)
    successMessage.value = 'Pasien berhasil dihapus'
    await loadData()
  } catch (error) {
    errorMessage.value = 'Gagal menghapus pasien'
    console.error('Error deleting pasien:', error)
  }
}
</script>
