<!-- 📁 resources/js/views/admin/ManageDokterPage.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-10">
      <div class="flex items-center gap-3 mb-2">
        <svg class="w-11 h-11 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
        </svg>
        <h1 class="text-4xl font-bold text-gray-900">Manajemen Dokter</h1>
      </div>
      <p class="text-gray-600">Kelola semua data dokter dan ketersediaan mereka</p>
    </div>

    <!-- Error Alert -->
    <ErrorAlert 
      v-if="errorMessage" 
      :error="errorMessage"
      title="Terjadi Kesalahan"
      @close="errorMessage = null"
      class="mb-6"
    />

    <!-- Success Alert -->
    <SuccessAlert 
      v-if="successMessage" 
      :success="successMessage"
      title="Berhasil"
      @close="successMessage = null"
      class="mb-6"
    />

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 hover:shadow-lg transition">
      <div class="flex flex-col md:flex-row gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama atau email..."
          class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 transition text-gray-700"
          @keyup.enter="loadData"
        />
        <select
          v-model="filterSpesialisasi"
          class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 transition text-gray-700"
        >
          <option value="">Semua Spesialisasi</option>
          <option value="Umum">Dokter Umum</option>
          <option value="Gigi">Gigi</option>
          <option value="Anak">Anak</option>
          <option value="Kandungan">Kandungan</option>
          <option value="Jantung">Jantung</option>
          <option value="Paru-paru">Paru-paru</option>
          <option value="Kulit">Kulit</option>
          <option value="Ortopedi">Ortopedi</option>
        </select>
        <button
          @click="loadData"
          class="px-4 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold flex items-center gap-2 whitespace-nowrap"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"/>
          </svg>
          Cari
        </button>
      </div>
    </div>

    <!-- Loading -->
    <LoadingSkeleton :isLoading="loading" type="table" :count="5">
      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-linear-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
            <tr>
              <th class="px-8 py-4 text-left text-sm font-bold text-gray-700">Nama Dokter</th>
              <th class="px-8 py-4 text-left text-sm font-bold text-gray-700">Spesialisasi</th>
              <th class="px-8 py-4 text-left text-sm font-bold text-gray-700">Email</th>
              <th class="px-8 py-4 text-center text-sm font-bold text-gray-700">Status</th>
              <th class="px-8 py-4 text-center text-sm font-bold text-gray-700">Ketersediaan</th>
              <th class="px-8 py-4 text-center text-sm font-bold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="dokter in dokterList" :key="dokter.id" class="hover:bg-indigo-50 transition">
              <td class="px-8 py-5 text-sm font-semibold text-gray-900">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM9 12a6 6 0 11-12 0 6 6 0 0112 0z" />
                    <path d="M18 6a3 3 0 11-6 0 3 3 0 016 0zM18 12a6 6 0 11-12 0 6 6 0 0112 0z" />
                  </svg>
                  {{ dokter.pengguna?.name || dokter.user?.name || 'N/A' }}
                </div>
              </td>
              <td class="px-8 py-5 text-sm text-gray-600">{{ dokter.specialization || 'N/A' }}</td>
              <td class="px-8 py-5 text-sm text-gray-600">{{ dokter.pengguna?.email || dokter.user?.email || 'N/A' }}</td>
              <td class="px-8 py-5 text-center">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1',
                  dokter.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]">
                  <svg v-if="dokter.is_active" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                  </svg>
                  {{ dokter.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-8 py-5 text-center">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1',
                  dokter.tersedia
                    ? 'bg-blue-100 text-blue-800'
                    : 'bg-gray-100 text-gray-800'
                ]">
                  <svg v-if="dokter.tersedia" class="w-4 h-4 rounded-full" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                  </svg>
                  <svg v-else class="w-4 h-4 rounded-full text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                  </svg>
                  {{ dokter.tersedia ? 'Tersedia' : 'Sibuk' }}
                </span>
              </td>
              <td class="px-8 py-5 text-center">
                <div class="flex gap-2 justify-center">
                  <button
                    @click="handleToggleStatus(dokter.id, dokter.is_active)"
                    :class="[
                      'px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1',
                      dokter.is_active
                        ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
                        : 'bg-green-100 text-green-800 hover:bg-green-200'
                    ]"
                  >
                    <svg v-if="!dokter.is_active" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                    </svg>
                    {{ dokter.is_active ? 'Nonaktif' : 'Aktif' }}
                  </button>
                  <button
                    @click="hapus(dokter.id)"
                    class="px-4 py-2 rounded-lg text-xs font-bold bg-red-100 text-red-800 hover:bg-red-200 transition flex items-center gap-1"
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

      <!-- Empty State -->
      <div v-if="dokterList.length === 0 && !loading" class="text-center py-16">
        <div class="flex justify-center mb-4">
          <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Dokter</h3>
        <p class="text-gray-600">Belum ada data dokter dalam sistem</p>
      </div>
    </div>
    </LoadingSkeleton>

    <!-- Pagination -->
    <div v-if="dokterList.length > 0" class="mt-8 flex flex-col sm:flex-row gap-4 items-center justify-between">
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
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { dokterAPI } from '@/api/dokter'
import { adminAPI } from '@/api/admin'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import SuccessAlert from '@/components/SuccessAlert.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { useLoadingState, useAsyncOperation } from '@/utils/useLoadingState'
import { usePagination } from '@/utils/usePagination'
import ErrorHandler from '@/utils/ErrorHandler'
import { Sanitizer } from '@/utils/Validation'

// Loading states
const { isLoading: generalLoading } = useLoadingState()
const search = ref('')
const filterSpesialisasi = ref('')
const errorMessage = ref(null)
const successMessage = ref(null)

// Pagination
const {
  items: dokterList,
  currentPage,
  totalPages,
  isLoading: paginationLoading,
  fetchPage,
  nextPage,
  prevPage,
  changePerPage
} = usePagination(async (config) => {
  try {
    const response = await dokterAPI.getList({
      page: config.page,
      per_page: config.per_page,
      search: search.value ? Sanitizer.trim(search.value) : undefined,
      specialization: filterSpesialisasi.value || undefined,
    })
    return response.data
  } catch (err) {
    errorMessage.value = ErrorHandler.getUserMessage(err)
    throw err
  }
})

const loading = paginationLoading

onMounted(() => {
  fetchPage(1)
})

const loadData = async () => {
  errorMessage.value = null
  await fetchPage(1)
}

const toggleStatus = useAsyncOperation(async (dokterId, isActive) => {
  const dokterData = dokterList.value.find(d => d.id === dokterId)
  if (!dokterData?.user_id) {
    throw new Error('Data dokter tidak valid')
  }
  
  try {
    if (isActive) {
      await adminAPI.deactivateUser(dokterData.user_id)
    } else {
      await adminAPI.activateUser(dokterData.user_id)
    }
    successMessage.value = `Status dokter berhasil diubah`
    await loadData()
  } catch (error) {
    throw error
  }
})

const hapus = async (dokterId) => {
  if (!confirm('Yakin ingin menghapus dokter ini?')) return

  const deleteOp = useAsyncOperation(async () => {
    await dokterAPI.delete(dokterId)
  })

  try {
    await deleteOp.execute()
    successMessage.value = 'Dokter berhasil dihapus'
    await loadData()
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  }
}

const handleToggleStatus = async (dokterId, isActive) => {
  try {
    await toggleStatus.execute(dokterId, isActive)
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  }
}
</script>
