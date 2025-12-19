<!-- 📁 resources/js/views/pasien/RiwayatKonsultasiPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 to-white py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">📋 Riwayat Konsultasi</h1>
        <p class="text-slate-600">Kelola dan lihat semua konsultasi Anda dengan dokter</p>
      </div>

      <!-- Filters & Search -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <div class="relative">
            <svg class="absolute left-3 top-3 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari nama dokter atau keluhan..."
              class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <select
          v-model="filters.status"
          class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        >
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="rejected">Rejected</option>
        </select>

        <!-- Sort -->
        <select
          v-model="filters.sort"
          class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        >
          <option value="terbaru">Terbaru</option>
          <option value="terlama">Terlama</option>
          <option value="dokter_a_z">Dokter A-Z</option>
        </select>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="bg-white rounded-lg p-6 animate-pulse">
          <div class="h-4 bg-slate-200 rounded w-3/4 mb-4"></div>
          <div class="h-4 bg-slate-200 rounded w-1/2"></div>
        </div>
      </div>

      <!-- Consultation List -->
      <div v-else-if="filteredKonsultasi.length > 0" class="space-y-4">
        <div
          v-for="konsultasi in filteredKonsultasi"
          :key="konsultasi.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6"
        >
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left Side: Doctor & Complaint -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start gap-4">
                <!-- Doctor Avatar -->
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-linear-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ getInitials(konsultasi.dokter?.name) }}
                  </div>
                </div>

                <!-- Doctor Info -->
                <div class="flex-1 min-w-0">
                  <h3 class="text-lg font-semibold text-slate-900 truncate">
                    Dr. {{ konsultasi.dokter?.name || 'N/A' }}
                  </h3>
                  <p class="text-sm text-slate-600 mt-1">
                    <span class="font-medium">Keluhan:</span> {{ konsultasi.jenis_keluhan || '-' }}
                  </p>
                  <p class="text-xs text-slate-500 mt-1">
                    📅 {{ formatDate(konsultasi.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Status Badge & Actions -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
              <!-- Status -->
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap',
                  {
                    'bg-yellow-100 text-yellow-800': konsultasi.status === 'pending',
                    'bg-blue-100 text-blue-800': konsultasi.status === 'confirmed',
                    'bg-green-100 text-green-800': konsultasi.status === 'completed',
                    'bg-red-100 text-red-800': konsultasi.status === 'rejected',
                  }
                ]"
              >
                {{ translateStatus(konsultasi.status) }}
              </span>

              <!-- Action Buttons -->
              <div class="flex items-center gap-2">
                <!-- View Details -->
                <button
                  @click="viewDetail(konsultasi)"
                  class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition"
                  title="Lihat Detail"
                >
                  👁️ Lihat
                </button>

                <!-- Chat (if confirmed) -->
                <button
                  v-if="konsultasi.status === 'confirmed'"
                  @click="startChat(konsultasi)"
                  class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-sm font-medium hover:bg-green-100 transition"
                  title="Chat dengan Dokter"
                >
                  💬 Chat
                </button>

                <!-- Rating (if completed) -->
                <button
                  v-if="konsultasi.status === 'completed' && !konsultasi.rating"
                  @click="openRating(konsultasi)"
                  class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-sm font-medium hover:bg-amber-100 transition"
                  title="Berikan Rating"
                >
                  ⭐ Rating
                </button>

                <!-- View Rating (if already rated) -->
                <button
                  v-else-if="konsultasi.rating"
                  @click="viewRating(konsultasi)"
                  class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-sm font-medium cursor-default"
                  title="Rating Sudah Diberikan"
                >
                  ⭐ {{ konsultasi.rating.nilai }}/5
                </button>
              </div>
            </div>
          </div>

          <!-- Expandable Details -->
          <div v-if="expandedId === konsultasi.id" class="mt-4 pt-4 border-t border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-slate-600">Spesialisasi</p>
                <p class="font-medium text-slate-900">{{ konsultasi.dokter?.spesialisasi || '-' }}</p>
              </div>
              <div>
                <p class="text-slate-600">Tarif Konsultasi</p>
                <p class="font-medium text-slate-900">Rp{{ formatCurrency(konsultasi.tarif_konsultasi) }}</p>
              </div>
              <div class="md:col-span-2">
                <p class="text-slate-600">Deskripsi Keluhan</p>
                <p class="text-slate-900 mt-1">{{ konsultasi.deskripsi || 'Tidak ada deskripsi' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex items-center justify-center min-h-96 bg-white rounded-lg">
        <div class="text-center">
          <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-slate-600 font-medium text-lg mb-1">Tidak Ada Konsultasi</p>
          <p class="text-slate-500 text-sm">Mulai konsultasi dengan dokter sekarang</p>
        </div>
      </div>
    </div>

    <!-- Rating Modal -->
    <div v-if="showRatingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Beri Rating untuk Dr. {{ selectedKonsultasi?.dokter?.name }}</h3>

        <!-- Star Rating -->
        <div class="flex justify-center gap-2 mb-6">
          <button
            v-for="star in 5"
            :key="star"
            @click="ratingForm.nilai = star"
            :class="[
              'text-3xl transition',
              star <= ratingForm.nilai ? 'text-amber-400' : 'text-slate-300'
            ]"
          >
            ⭐
          </button>
        </div>

        <!-- Comment -->
        <textarea
          v-model="ratingForm.komentar"
          placeholder="Berikan komentar (opsional)"
          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 mb-4 resize-none"
          rows="3"
        ></textarea>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button
            @click="showRatingModal = false"
            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition"
          >
            Batal
          </button>
          <button
            @click="submitRating"
            :disabled="ratingForm.nilai === 0 || submittingRating"
            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition"
          >
            {{ submittingRating ? 'Mengirim...' : 'Kirim Rating' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { konsultasiAPI } from '@/api/konsultasi'
import { ratingAPI } from '@/api/rating'

const router = useRouter()
const authStore = useAuthStore()

// State
const loading = ref(false)
const konsultasiList = ref([])
const expandedId = ref(null)
const showRatingModal = ref(false)
const submittingRating = ref(false)
const selectedKonsultasi = ref(null)

const filters = ref({
  search: '',
  status: '',
  sort: 'terbaru'
})

const ratingForm = ref({
  nilai: 0,
  komentar: ''
})

// Computed
const filteredKonsultasi = computed(() => {
  let result = konsultasiList.value

  // Filter by search
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(k =>
      k.dokter?.name?.toLowerCase().includes(search) ||
      k.jenis_keluhan?.toLowerCase().includes(search)
    )
  }

  // Filter by status
  if (filters.value.status) {
    result = result.filter(k => k.status === filters.value.status)
  }

  // Sort
  switch (filters.value.sort) {
    case 'terlama':
      result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
      break
    case 'dokter_a_z':
      result.sort((a, b) => a.dokter?.name?.localeCompare(b.dokter?.name))
      break
    case 'terbaru':
    default:
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }

  return result
})

// Methods
const fetchKonsultasi = async () => {
  loading.value = true
  try {
    const response = await konsultasiAPI.getList()
    konsultasiList.value = response.data || []
  } catch (error) {
    console.error('Error fetching konsultasi:', error)
    konsultasiList.value = []
  } finally {
    loading.value = false
  }
}

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (value) => {
  if (!value) return '0'
  return Math.floor(value).toLocaleString('id-ID')
}

const translateStatus = (status) => {
  const translations = {
    pending: 'Menunggu',
    confirmed: 'Dikonfirmasi',
    completed: 'Selesai',
    rejected: 'Ditolak'
  }
  return translations[status] || status
}

const viewDetail = (konsultasi) => {
  if (expandedId.value === konsultasi.id) {
    expandedId.value = null
  } else {
    expandedId.value = konsultasi.id
  }
}

const startChat = (konsultasi) => {
  router.push({
    name: 'chat',
    params: { konsultasiId: konsultasi.id }
  })
}

const openRating = (konsultasi) => {
  selectedKonsultasi.value = konsultasi
  ratingForm.value = {
    nilai: 0,
    komentar: ''
  }
  showRatingModal.value = true
}

const viewRating = (konsultasi) => {
  if (konsultasi.rating) {
    alert(`Rating: ${konsultasi.rating.nilai}/5\nKomentar: ${konsultasi.rating.komentar || '-'}`)
  }
}

const submitRating = async () => {
  if (ratingForm.value.nilai === 0) return

  submittingRating.value = true
  try {
    await ratingAPI.create({
      konsultasi_id: selectedKonsultasi.value.id,
      dokter_id: selectedKonsultasi.value.dokter_id,
      nilai: ratingForm.value.nilai,
      komentar: ratingForm.value.komentar
    })

    // Update local state
    const index = konsultasiList.value.findIndex(k => k.id === selectedKonsultasi.value.id)
    if (index >= 0) {
      konsultasiList.value[index].rating = {
        nilai: ratingForm.value.nilai,
        komentar: ratingForm.value.komentar
      }
    }

    showRatingModal.value = false
    alert('Rating berhasil dikirim!')
  } catch (error) {
    console.error('Error submitting rating:', error)
    alert('Gagal mengirim rating')
  } finally {
    submittingRating.value = false
  }
}

// Lifecycle
onMounted(fetchKonsultasi)
</script>

<style scoped>
/* Smooth transitions */
* {
  @apply transition-all duration-200;
}
</style>
