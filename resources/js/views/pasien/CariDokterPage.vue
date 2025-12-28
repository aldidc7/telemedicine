<!-- [CARI-DOKTER] resources/js/views/pasien/CariDokterPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-gray-100 pb-12">
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-10 mb-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div>
            <h1 class="text-3xl md:text-4xl font-black mb-2">Temukan Dokter Terbaik</h1>
            <p class="text-base md:text-lg text-indigo-100 mb-4">Konsultasi dengan dokter spesialis berpengalaman dan terpercaya</p>
            <div class="flex gap-2 flex-wrap text-sm">
              <div class="flex items-center gap-1.5 text-indigo-50">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H3a1 1 0 00-1 1v12a1 1 0 001 1h14a1 1 0 001-1V6a1 1 0 00-1-1h3a1 1 0 000-2h-2.5A2.5 2.5 0 0016 4.5v12a2.5 2.5 0 01-2.5 2.5H4a2.5 2.5 0 01-2.5-2.5v-12A2.5 2.5 0 014 5z" clip-rule="evenodd"></path></svg>
                <span>Dokter Terverifikasi</span>
              </div>
              <div class="flex items-center gap-1.5 text-indigo-50">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H3a1 1 0 01-1-1V6zM12 6a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V6zM2 16a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H3a1 1 0 01-1-1v-2zM12 16a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"></path></svg>
                <span>24/7 Tersedia</span>
              </div>
            </div>
          </div>
          <div class="hidden md:block">
            <svg class="w-32 h-32 mx-auto" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="100" cy="100" r="80" fill="rgba(255,255,255,0.1)"/>
              <path d="M100 60C118.8 60 134 75.2 134 94M100 80C112.7 80 123 90.3 123 103M85 100C85 110.5 93.1 119 103 119C112.9 119 121 110.5 121 100C121 89.5 112.9 81 103 81C93.1 81 85 89.5 85 100Z" stroke="white" stroke-width="3" stroke-linecap="round"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Filter Section with Modern Design -->
      <div class="mb-10">
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-linear-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Cari Dokter Spesialis</h3>
          </div>
          
          <!-- Filter Bars (3 Column Layout + Reset Icon) -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
            <!-- Spesialisasi Dropdown -->
            <div class="group">
              <label class="flex items-center gap-2 text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>
                Spesialisasi
              </label>
              <CustomSelect
                :model-value="filter.spesialisasi"
                :options="spesialisasiOptions"
                @update:model-value="(val) => { filter.spesialisasi = val; loadDokter() }"
              />
            </div>

            <!-- Ketersediaan Dropdown -->
            <div class="group">
              <label class="flex items-center gap-2 text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Ketersediaan
              </label>
              <CustomSelect
                :model-value="filter.tersedia"
                :options="tersediaOptions"
                @update:model-value="(val) => { filter.tersedia = val; loadDokter() }"
              />
            </div>

            <!-- Rating Dropdown -->
            <div class="group">
              <label class="flex items-center gap-2 text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide">
                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                Rating Minimal
              </label>
              <CustomSelect
                :model-value="filter.ratingMin"
                :options="ratingOptions"
                @update:model-value="(val) => { filter.ratingMin = val; loadDokter() }"
              />
            </div>

            <!-- Reset Button Icon (Desktop Only) -->
            <button
              @click="resetFilters()"
              class="hidden md:flex w-12 h-12 items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 hover:text-gray-900 rounded-xl transition shadow-md group/reset"
              title="Reset Semua Filter"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
          </div>

          <!-- Reset Button (Mobile Only) -->
          <button
            @click="resetFilters()"
            class="md:hidden px-6 py-3 bg-linear-to-r from-gray-400 to-gray-500 text-white rounded-xl font-bold hover:from-gray-500 hover:to-gray-600 transition shadow-md w-full flex items-center justify-center gap-2 mb-6"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Reset Semua Filter
          </button>
        </div>
      </div>

      <!-- Loading State with Skeleton Loaders -->
      <div v-if="loading" class="space-y-4">
        <SkeletonLoader type="card" />
        <SkeletonLoader type="card" />
        <SkeletonLoader type="card" />
      </div>

      <!-- Empty State -->
      <div v-else-if="dokterList.length === 0" class="bg-white rounded-2xl shadow-lg p-16 text-center">
        <div class="mb-4">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <p class="text-xl font-bold text-gray-900 mb-2">Dokter Tidak Ditemukan</p>
          <p class="text-gray-600">Tidak ada dokter yang sesuai dengan filter Anda</p>
        </div>
      </div>

      <!-- Dokter List Grid with Animations -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
        <DoctorCard
          v-for="(dokter, index) in dokterList"
          :key="dokter.id"
          :doctor="dokter"
          @consult="handleConsult"
          :style="{ animationDelay: `${index * 50}ms` }"
          class="animate-slideUp"
        />
      </div>

      <!-- Modal Buat Konsultasi -->
      <BuatKonsultasiModal
        v-if="showModal"
        :dokter="selectedDokter"
        @close="showModal = false"
        @success="handleSuccess"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { dokterAPI } from '@/api/dokter'
import { ratingAPI } from '@/api/rating'
import DoctorCard from '@/components/DoctorCard.vue'
import BuatKonsultasiModal from '@/components/modals/BuatKonsultasiModal.vue'
import CustomSelect from '@/components/CustomSelect.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'

const router = useRouter()
const loading = ref(false)
const dokterList = ref([])
const showModal = ref(false)
const selectedDokter = ref(null)
let filterDebounceTimer = null

const handleConsult = async (doctor) => {
  selectedDokter.value = doctor
  showModal.value = true
}

const filter = ref({
  spesialisasi: '',
  tersedia: '',
  ratingMin: ''
})

// Dropdown Options
const spesialisasiOptions = [
  { value: '', label: 'Semua Spesialisasi' },
  { value: 'Umum', label: 'Dokter Umum' },
  { value: 'Gigi', label: 'Gigi' },
  { value: 'Anak', label: 'Anak' },
  { value: 'Kandungan', label: 'Kandungan' },
  { value: 'Jantung', label: 'Jantung' },
  { value: 'Paru-paru', label: 'Paru-paru' },
  { value: 'Kulit', label: 'Kulit' },
  { value: 'Ortopedi', label: 'Ortopedi' }
]

const tersediaOptions = [
  { value: '', label: 'Semua Status' },
  { value: '1', label: 'Tersedia' },
  { value: '0', label: 'Offline' }
]

const ratingOptions = [
  { value: '', label: 'Semua Rating' },
  { value: '3', label: '[3STAR] 3+ Bintang' },
  { value: '4', label: '[4STAR] 4+ Bintang' },
  { value: '4.5', label: '[4.5STAR] 4.5+ Bintang' }
]

onMounted(() => {
  loadDokter()
})

// Debounce filter changes untuk mengurangi API calls
watch(filter, () => {
  if (filterDebounceTimer) clearTimeout(filterDebounceTimer)
  filterDebounceTimer = setTimeout(() => {
    loadDokter()
  }, 500)
}, { deep: true })

const loadDokter = async () => {
  loading.value = true
  try {
    const params = {}
    if (filter.spesialisasi) params.spesialisasi = filter.spesialisasi
    if (filter.tersedia !== '') params.tersedia = filter.tersedia

    const response = await dokterAPI.getList(params)
    let dokterData = response.data.data

    // Load ratings in parallel untuk semua dokter sekaligus
    const ratingPromises = dokterData.map(dokter =>
      ratingAPI.getDokterRatings(dokter.id)
        .then(ratingResponse => ({
          dokter_id: dokter.id,
          avg_rating: ratingResponse.average_rating,
          rating_count: ratingResponse.total_ratings
        }))
        .catch(() => ({
          dokter_id: dokter.id,
          avg_rating: 0,
          rating_count: 0
        }))
    )

    const ratings = await Promise.all(ratingPromises)
    
    // Map ratings ke dokter
    const ratingMap = Object.fromEntries(ratings.map(r => [r.dokter_id, r]))
    dokterData.forEach(dokter => {
      const rating = ratingMap[dokter.id]
      dokter.avg_rating = rating?.avg_rating ?? 0
      dokter.rating_count = rating?.rating_count ?? 0
    })

    // Apply rating filter
    if (filter.ratingMin) {
      dokterData = dokterData.filter(d => d.avg_rating >= parseFloat(filter.ratingMin))
    }

    dokterList.value = dokterData
  } catch (error) {
    console.error('Error loading dokter:', error)
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filter.value = {
    spesialisasi: '',
    tersedia: '',
    ratingMin: ''
  }
  loadDokter()
}

const buatKonsultasi = (dokter) => {
  selectedDokter.value = dokter
  showModal.value = true
}

const handleSuccess = () => {
  showModal.value = false
  router.push('/konsultasi')
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}
</script>
