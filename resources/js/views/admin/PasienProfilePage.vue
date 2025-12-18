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
        <h1 class="text-3xl font-bold text-gray-900">Profil Pasien</h1>
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
    <div v-else-if="pasien" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <!-- Main Card -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-8 text-white">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-2xl font-bold">{{ pasien.pengguna?.name }}</h2>
              <p class="text-green-100 mt-1">{{ pasien.medical_record_number }}</p>
              <div class="mt-3 flex items-center gap-2">
                <span v-if="pasien.status === 'active'" class="px-3 py-1 bg-blue-400 text-blue-900 rounded-full text-sm font-semibold">
                  Aktif
                </span>
                <span v-else class="px-3 py-1 bg-red-400 text-red-900 rounded-full text-sm font-semibold">
                  Nonaktif
                </span>
              </div>
            </div>
            <button
              @click="goToEdit"
              class="px-4 py-2 bg-white text-green-600 rounded-lg font-semibold hover:bg-green-50 transition flex items-center gap-2"
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
                <p class="text-gray-900">{{ pasien.pengguna?.email || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon</label>
                <p class="text-gray-900">{{ pasien.no_telepon || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                <p class="text-gray-900">{{ formatDate(pasien.tanggal_lahir) }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                <p class="text-gray-900">{{ pasien.jenis_kelamin === 'M' ? 'Laki-laki' : 'Perempuan' }}</p>
              </div>
            </div>

            <!-- Column 2 -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">No. Rekam Medis (MRN)</label>
                <p class="text-gray-900 font-mono text-lg">{{ pasien.medical_record_number }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                <p class="text-gray-900">{{ pasien.alamat || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tinggi Badan (cm)</label>
                <p class="text-gray-900">{{ pasien.tinggi_badan || '-' }}</p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Berat Badan (kg)</label>
                <p class="text-gray-900">{{ pasien.berat_badan || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Riwayat Medis Section -->
          <div class="mt-8 pt-8 border-t border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-3">Riwayat Medis</label>
            <p class="text-gray-700">{{ pasien.riwayat_medis || 'Tidak ada riwayat medis' }}</p>
          </div>

          <!-- Alergi Section -->
          <div v-if="pasien.alergi" class="mt-4">
            <label class="block text-sm font-semibold text-gray-700 mb-3">Alergi</label>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="allergi in pasien.alergi.split(',')"
                :key="allergi"
                class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm"
              >
                {{ allergi.trim() }}
              </span>
            </div>
          </div>

          <!-- Statistics -->
          <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Konsultasi</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-blue-600 text-sm font-semibold">Total Konsultasi</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ statistik.total_konsultasi || 0 }}</p>
              </div>
              <div class="bg-green-50 rounded-lg p-4">
                <p class="text-green-600 text-sm font-semibold">Konsultasi Selesai</p>
                <p class="text-2xl font-bold text-green-900 mt-1">{{ statistik.konsultasi_selesai || 0 }}</p>
              </div>
              <div class="bg-yellow-50 rounded-lg p-4">
                <p class="text-yellow-600 text-sm font-semibold">Konsultasi Pending</p>
                <p class="text-2xl font-bold text-yellow-900 mt-1">{{ statistik.konsultasi_pending || 0 }}</p>
              </div>
              <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-purple-600 text-sm font-semibold">Rating Rata-rata</p>
                <p class="text-2xl font-bold text-purple-900 mt-1">{{ (statistik.rating_rata_rata || 0).toFixed(1) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="px-6 py-8 bg-gray-50 border-t border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Sistem</h3>
          <div class="space-y-3 text-sm text-gray-600">
            <p><span class="font-semibold">Bergabung:</span> {{ formatDate(pasien.created_at) }}</p>
            <p><span class="font-semibold">Terakhir Diperbarui:</span> {{ formatDate(pasien.updated_at) }}</p>
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
        <h3 class="text-lg font-semibold text-gray-900">Pasien Tidak Ditemukan</h3>
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
import { pasienAPI } from '@/api/pasien'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import SuccessAlert from '@/components/SuccessAlert.vue'
import ErrorHandler from '@/utils/ErrorHandler'

const route = useRoute()
const router = useRouter()

const pasien = ref(null)
const loading = ref(false)
const errorMessage = ref(null)
const successMessage = ref(null)
const pasienId = route.params.id

const statistik = ref({
  total_konsultasi: 0,
  konsultasi_selesai: 0,
  konsultasi_pending: 0,
  rating_rata_rata: 0
})

/**
 * Load patient data
 */
const loadPasien = async () => {
  loading.value = true
  errorMessage.value = null
  
  try {
    const response = await pasienAPI.getById(pasienId)
    pasien.value = response.data
    
    // Calculate statistics
    if (response.data.konsultasis) {
      const konsultasis = response.data.konsultasis
      statistik.value.total_konsultasi = konsultasis.length
      statistik.value.konsultasi_selesai = konsultasis.filter(k => k.status === 'selesai').length
      statistik.value.konsultasi_pending = konsultasis.filter(k => k.status === 'pending').length
      
      // Calculate average rating
      const ratings = konsultasis.filter(k => k.rating).map(k => k.rating)
      statistik.value.rating_rata_rata = ratings.length > 0 ? ratings.reduce((a, b) => a + b, 0) / ratings.length : 0
    }
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
    day: 'numeric'
  })
}

/**
 * Navigate back to patient list
 */
const goBack = () => {
  router.push('/admin/pasien')
}

/**
 * Navigate to edit page
 */
const goToEdit = () => {
  router.push(`/admin/pasien/${pasienId}/edit`)
}

onMounted(() => {
  loadPasien()
})
</script>
