<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <button
          @click="goBack"
          class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700 mb-4"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Kembali
        </button>
        <h1 class="text-3xl font-bold text-gray-900">Profil Dokter</h1>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <LoadingSpinner />
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <ErrorAlert :message="errorMessage" />
      <button
        @click="goBack"
        class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition"
      >
        Kembali ke Daftar
      </button>
    </div>

    <!-- Content -->
    <div v-else-if="dokter" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Main Card -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Profile Header -->
        <div class="bg-linear-to-r from-indigo-500 to-indigo-600 px-6 py-8 text-white">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-2xl font-bold">{{ dokter.nama_dokter }}</h2>
              <p class="text-indigo-100 mt-1">{{ dokter.spesialisasi }}</p>
              <div class="mt-3 flex items-center gap-2">
                <span v-if="dokter.is_active" class="px-3 py-1 bg-green-400 text-green-900 rounded-full text-sm font-semibold">
                  Aktif
                </span>
                <span v-else class="px-3 py-1 bg-red-400 text-red-900 rounded-full text-sm font-semibold">
                  Nonaktif
                </span>
              </div>
            </div>
            <button
              @click="goToEdit"
              class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </button>
          </div>
        </div>

        <!-- Profile Information -->
        <div class="px-6 py-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Column 1 -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <p class="text-gray-900">{{ dokter.pengguna?.email || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon</label>
                <p class="text-gray-900">{{ dokter.no_telepon || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Spesialisasi</label>
                <div class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-800 rounded-lg">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
                  </svg>
                  <span class="font-medium">{{ dokter.spesialisasi }}</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pengalaman (Tahun)</label>
                <p class="text-gray-900">{{ dokter.tahun_pengalaman || '-' }} tahun</p>
              </div>
            </div>

            <!-- Column 2 -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">No. Registrasi Praktik</label>
                <p class="text-gray-900 font-mono">{{ dokter.no_registrasi_praktik || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Kerja</label>
                <p class="text-gray-900">{{ formatJamKerja(dokter.jam_kerja) }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bergabung Sejak</label>
                <p class="text-gray-900">{{ formatDate(dokter.created_at) }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Terakhir Diperbarui</label>
                <p class="text-gray-900">{{ formatDate(dokter.updated_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Bio Section -->
          <div v-if="dokter.bio" class="mt-8 pt-8 border-t border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-3">Biodata</label>
            <p class="text-gray-700 leading-relaxed">{{ dokter.bio }}</p>
          </div>

          <!-- Statistics -->
          <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-blue-600 text-sm font-semibold">Konsultasi Total</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ dokter.total_konsultasi || 0 }}</p>
              </div>
              <div class="bg-green-50 rounded-lg p-4">
                <p class="text-green-600 text-sm font-semibold">Rating Rata-rata</p>
                <p class="text-2xl font-bold text-green-900 mt-1">{{ (dokter.rating_rata_rata || 0).toFixed(1) }}</p>
              </div>
              <div class="bg-yellow-50 rounded-lg p-4">
                <p class="text-yellow-600 text-sm font-semibold">Pasien Unik</p>
                <p class="text-2xl font-bold text-yellow-900 mt-1">{{ dokter.total_pasien || 0 }}</p>
              </div>
              <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-purple-600 text-sm font-semibold">Tingkat Respons</p>
                <p class="text-2xl font-bold text-purple-900 mt-1">{{ (dokter.response_rate || 0).toFixed(0) }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-between">
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition"
          >
            Kembali
          </button>
          <div class="flex gap-3">
            <button
              @click="goToEdit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit Profil
            </button>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <SuccessAlert v-if="successMessage" :message="successMessage" />
    </div>

    <!-- Not Found -->
    <div v-else class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-semibold text-gray-900">Dokter Tidak Ditemukan</h3>
        <button
          @click="goBack"
          class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
        >
          Kembali ke Daftar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { dokterAPI } from '@/api/dokter'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import SuccessAlert from '@/components/SuccessAlert.vue'
import ErrorHandler from '@/utils/ErrorHandler'

const route = useRoute()
const router = useRouter()

const dokter = ref(null)
const loading = ref(false)
const errorMessage = ref(null)
const successMessage = ref(null)
const dokterId = route.params.id

/**
 * Load doctor data
 */
const loadDokter = async () => {
  loading.value = true
  errorMessage.value = null
  
  try {
    const response = await dokterAPI.getById(dokterId)
    dokter.value = response.data
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  } finally {
    loading.value = false
  }
}

/**
 * Format date to Indonesian format
 */
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Format jam kerja (work hours)
 */
const formatJamKerja = (jamKerja) => {
  if (!jamKerja) return '-'
  if (typeof jamKerja === 'object') {
    const { jam_mulai, jam_selesai } = jamKerja
    return `${jam_mulai || '09:00'} - ${jam_selesai || '17:00'}`
  }
  return jamKerja
}

/**
 * Navigate back to doctor list
 */
const goBack = () => {
  router.push('/admin/dokter')
}

/**
 * Navigate to edit page
 */
const goToEdit = () => {
  router.push(`/admin/dokter/${dokterId}/edit`)
}

onMounted(() => {
  loadDokter()
})
</script>
