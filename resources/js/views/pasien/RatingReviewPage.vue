<!-- [RATING-REVIEW] resources/js/views/pasien/RatingReviewPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 to-white py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">[RATING] Rating & Review</h1>
        <p class="text-slate-600">Lihat dan berikan penilaian untuk dokter yang telah Anda konsultasi</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-4 mb-6 border-b border-slate-200">
        <button
          @click="activeTab = 'lihat'"
          :class="[
            'px-4 py-2 font-medium border-b-2 transition',
            activeTab === 'lihat'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-600 hover:text-slate-900'
          ]"
        >
          👁️ Rating Saya ({{ ratingList.length }})
        </button>
        <button
          @click="activeTab = 'dokter'"
          :class="[
            'px-4 py-2 font-medium border-b-2 transition',
            activeTab === 'dokter'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-slate-600 hover:text-slate-900'
          ]"
        >
          👨‍⚕️ Rating Dokter
        </button>
      </div>

      <!-- Tab: Lihat Rating Saya -->
      <div v-if="activeTab === 'lihat'" class="space-y-4">
        <!-- Loading -->
        <div v-if="loadingRatings" class="space-y-4">
          <div v-for="i in 3" :key="i" class="bg-white rounded-lg p-6 animate-pulse">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-4"></div>
            <div class="h-4 bg-slate-200 rounded w-1/2"></div>
          </div>
        </div>

        <!-- Rating List -->
        <div v-else-if="ratingList.length > 0" class="space-y-4">
          <div
            v-for="rating in ratingList"
            :key="rating.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-slate-900">
                  Dr. {{ rating.dokter?.name || 'Unknown' }}
                </h3>
                <p class="text-sm text-slate-600 mt-1">
                  Spesialisasi: {{ rating.dokter?.spesialisasi || '-' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                  [DATE] {{ formatDate(rating.created_at) }}
                </p>
              </div>

              <!-- Star Rating Display -->
              <div class="flex items-center gap-2">
                <div class="flex gap-1">
                  <span v-for="star in 5" :key="star" class="text-xl">
                    {{ star <= rating.nilai ? '[STAR]' : '[ ]' }}
                  </span>
                </div>
                <span class="font-bold text-lg text-amber-500">{{ rating.nilai }}/5</span>
              </div>
            </div>

            <!-- Comment -->
            <div v-if="rating.komentar" class="mb-4 p-4 bg-slate-50 rounded-lg">
              <p class="text-slate-700">{{ rating.komentar }}</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <button
                @click="editRating(rating)"
                class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-100 transition"
              >
                ✏️ Edit
              </button>
              <button
                @click="deleteRating(rating.id)"
                class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition"
              >
                🗑️ Hapus
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex items-center justify-center min-h-96 bg-white rounded-lg">
          <div class="text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <p class="text-slate-600 font-medium text-lg mb-1">Belum Ada Rating</p>
            <p class="text-slate-500 text-sm">Konsultasi dengan dokter selesai? Berikan rating mereka</p>
          </div>
        </div>
      </div>

      <!-- Tab: Rating Dokter -->
      <div v-if="activeTab === 'dokter'" class="space-y-4">
        <!-- Search & Filter -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <input
            v-model="searchDoctor"
            type="text"
            placeholder="Cari nama dokter..."
            class="col-span-2 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
          />
          <select
            v-model="sortDoctor"
            class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
          >
            <option value="rating_desc">Rating Tertinggi</option>
            <option value="rating_asc">Rating Terendah</option>
            <option value="nama">Nama A-Z</option>
            <option value="reviews">Paling Banyak Review</option>
          </select>
        </div>

        <!-- Loading -->
        <div v-if="loadingDoctors" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="i in 4" :key="i" class="bg-white rounded-lg p-6 animate-pulse">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-4"></div>
            <div class="h-4 bg-slate-200 rounded w-1/2"></div>
          </div>
        </div>

        <!-- Doctor Cards -->
        <div v-else-if="filteredDoctors.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="doctor in filteredDoctors"
            :key="doctor.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6"
          >
            <!-- Doctor Header -->
            <div class="flex items-start gap-4 mb-4">
              <div class="w-12 h-12 bg-linear-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                {{ getInitials(doctor.name) }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-slate-900 truncate">Dr. {{ doctor.name }}</h3>
                <p class="text-sm text-slate-600">{{ doctor.spesialisasi || '-' }}</p>
              </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-2 mb-4 p-3 bg-slate-50 rounded-lg text-center">
              <div>
                <p class="text-xs text-slate-600">Rating</p>
                <p class="font-bold text-indigo-600">{{ (doctor.average_rating || 0).toFixed(1) }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-600">Review</p>
                <p class="font-bold text-indigo-600">{{ doctor.rating_count || 0 }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-600">Tarif</p>
                <p class="font-bold text-indigo-600">Rp{{ formatCurrency(doctor.tarif_konsultasi) }}</p>
              </div>
            </div>

            <!-- Star Rating -->
            <div class="flex items-center gap-2 mb-4">
              <div class="flex gap-0.5">
                <span v-for="star in 5" :key="star" class="text-lg">
                  {{ star <= Math.round(doctor.average_rating || 0) ? '[STAR]' : '[ ]' }}
                </span>
              </div>
              <span class="text-xs text-slate-600">({{ doctor.rating_count || 0 }} reviews)</span>
            </div>

            <!-- Action -->
            <button
              @click="chatDoctor(doctor)"
              class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
            >
              [CHAT] Mulai Konsultasi
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex items-center justify-center min-h-96 bg-white rounded-lg">
          <div class="text-center">
            <p class="text-slate-600 font-medium">Tidak ada dokter yang ditemukan</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Rating Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Edit Rating</h3>

        <!-- Star Rating -->
        <div class="flex justify-center gap-2 mb-6">
          <button
            v-for="star in 5"
            :key="star"
            @click="editForm.nilai = star"
            :class="[
              'text-3xl transition',
              star <= editForm.nilai ? 'text-amber-400' : 'text-slate-300'
            ]"
          >
            [STAR]
          </button>
        </div>

        <!-- Comment -->
        <textarea
          v-model="editForm.komentar"
          placeholder="Berikan komentar (opsional)"
          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 mb-4 resize-none"
          rows="3"
        ></textarea>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button
            @click="showEditModal = false"
            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition"
          >
            Batal
          </button>
          <button
            @click="saveEdit"
            :disabled="editForm.nilai === 0 || submittingEdit"
            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition"
          >
            {{ submittingEdit ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ratingAPI } from '@/api/rating'
import { dokterAPI } from '@/api/dokter'

const router = useRouter()

// State
const activeTab = ref('lihat')
const ratingList = ref([])
const doctorList = ref([])
const loadingRatings = ref(false)
const loadingDoctors = ref(false)
const showEditModal = ref(false)
const submittingEdit = ref(false)
const searchDoctor = ref('')
const sortDoctor = ref('rating_desc')
const selectedEditRating = ref(null)

const editForm = ref({
  nilai: 0,
  komentar: ''
})

// Computed
const filteredDoctors = computed(() => {
  let result = doctorList.value

  // Search
  if (searchDoctor.value) {
    const search = searchDoctor.value.toLowerCase()
    result = result.filter(d =>
      d.name?.toLowerCase().includes(search) ||
      d.spesialisasi?.toLowerCase().includes(search)
    )
  }

  // Sort
  switch (sortDoctor.value) {
    case 'rating_asc':
      result.sort((a, b) => (a.average_rating || 0) - (b.average_rating || 0))
      break
    case 'nama':
      result.sort((a, b) => a.name?.localeCompare(b.name))
      break
    case 'reviews':
      result.sort((a, b) => (b.rating_count || 0) - (a.rating_count || 0))
      break
    case 'rating_desc':
    default:
      result.sort((a, b) => (b.average_rating || 0) - (a.average_rating || 0))
  }

  return result
})

// Methods
const fetchRatings = async () => {
  loadingRatings.value = true
  try {
    const response = await ratingAPI.getList?.() || { data: [] }
    ratingList.value = response.data || []
  } catch (error) {
    console.error('Error fetching ratings:', error)
    ratingList.value = []
  } finally {
    loadingRatings.value = false
  }
}

const fetchDoctors = async () => {
  loadingDoctors.value = true
  try {
    const response = await dokterAPI.getList()
    doctorList.value = response.data || []
  } catch (error) {
    console.error('Error fetching doctors:', error)
    doctorList.value = []
  } finally {
    loadingDoctors.value = false
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
    day: 'numeric'
  })
}

const formatCurrency = (value) => {
  if (!value) return '0'
  return Math.floor(value).toLocaleString('id-ID')
}

const editRating = (rating) => {
  selectedEditRating.value = rating
  editForm.value = {
    nilai: rating.nilai,
    komentar: rating.komentar || ''
  }
  showEditModal.value = true
}

const saveEdit = async () => {
  if (editForm.value.nilai === 0) return

  submittingEdit.value = true
  try {
    await ratingAPI.update(selectedEditRating.value.id, {
      nilai: editForm.value.nilai,
      komentar: editForm.value.komentar
    })

    // Update local state
    const index = ratingList.value.findIndex(r => r.id === selectedEditRating.value.id)
    if (index >= 0) {
      ratingList.value[index].nilai = editForm.value.nilai
      ratingList.value[index].komentar = editForm.value.komentar
    }

    showEditModal.value = false
    alert('Rating berhasil diperbarui!')
  } catch (error) {
    console.error('Error updating rating:', error)
    alert('Gagal memperbarui rating')
  } finally {
    submittingEdit.value = false
  }
}

const deleteRating = async (ratingId) => {
  if (!confirm('Apakah Anda yakin ingin menghapus rating ini?')) return

  try {
    await ratingAPI.delete(ratingId)
    ratingList.value = ratingList.value.filter(r => r.id !== ratingId)
    alert('Rating berhasil dihapus!')
  } catch (error) {
    console.error('Error deleting rating:', error)
    alert('Gagal menghapus rating')
  }
}

const chatDoctor = (doctor) => {
  // Redirect to chat page or consultation booking
  router.push({
    name: 'cari-dokter',
    query: { doctorId: doctor.user_id }
  })
}

// Lifecycle
onMounted(() => {
  fetchRatings()
  fetchDoctors()
})
</script>

<style scoped>
* {
  @apply transition-all duration-200;
}
</style>
