<!-- 📁 resources/js/views/pasien/DashboardPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header/Hero -->
      <div class="mb-10">
        <div class="inline-block">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 bg-linear-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center">
              <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
              </svg>
            </div>
            <div>
              <h1 class="text-4xl font-black text-gray-900">Dashboard Kesehatan</h1>
              <p class="text-gray-600 mt-1 font-medium">Kelola kesehatan Anda dengan mudah dan aman</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Tabs Navigation -->
      <div class="mb-8 border-b border-gray-200 sticky top-16 bg-white bg-opacity-95 -mx-4 sm:mx-0 px-4 sm:px-0 z-30">
        <div class="flex gap-6">
          <button
            @click="activeTab = 'overview'"
            :class="['px-4 py-3 font-semibold border-b-2 transition text-sm md:text-base flex items-center gap-2', 
              activeTab === 'overview' 
                ? 'border-indigo-600 text-indigo-600' 
                : 'border-transparent text-gray-600 hover:text-gray-900']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Ringkasan
          </button>
          <button
            @click="activeTab = 'riwayat'"
            :class="['px-4 py-3 font-semibold border-b-2 transition text-sm md:text-base flex items-center gap-2',
              activeTab === 'riwayat'
                ? 'border-indigo-600 text-indigo-600'
                : 'border-transparent text-gray-600 hover:text-gray-900']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Riwayat Konsultasi
          </button>
        </div>
      </div>

      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="group bg-linear-to-br from-blue-500 to-blue-600 rounded-3xl p-8 text-white shadow-lg hover:shadow-2xl transition border border-blue-400 border-opacity-20">
            <div class="flex justify-between items-start mb-6">
              <div>
                <p class="text-blue-100 text-sm font-bold uppercase tracking-wide">Konsultasi Aktif</p>
                <p class="text-6xl font-black mt-4">{{ stats.aktif }}</p>
              </div>
              <div class="w-18 h-18 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center group-hover:bg-opacity-30 transition shadow-lg">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              </div>
            </div>
            <p class="text-blue-100 text-sm">Sedang berlangsung sekarang</p>
          </div>

          <div class="group bg-linear-to-br from-yellow-500 to-amber-600 rounded-3xl p-8 text-white shadow-lg hover:shadow-2xl transition border border-yellow-400 border-opacity-20">
            <div class="flex justify-between items-start mb-6">
              <div>
                <p class="text-yellow-100 text-sm font-bold uppercase tracking-wide">Menunggu Respon</p>
                <p class="text-6xl font-black mt-4">{{ stats.menunggu }}</p>
              </div>
              <div class="w-18 h-18 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center group-hover:bg-opacity-30 transition shadow-lg">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-yellow-100 text-sm">Nunggu dokter merespons</p>
          </div>

          <div class="group bg-linear-to-br from-green-500 to-emerald-600 rounded-3xl p-8 text-white shadow-lg hover:shadow-2xl transition border border-green-400 border-opacity-20">
            <div class="flex justify-between items-start mb-6">
              <div>
                <p class="text-green-100 text-sm font-bold uppercase tracking-wide">Total Konsultasi</p>
                <p class="text-6xl font-black mt-4">{{ stats.total }}</p>
              </div>
              <div class="w-18 h-18 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center group-hover:bg-opacity-30 transition shadow-lg">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-green-100 text-sm">Sepanjang waktu</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-linear-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl p-8 md:p-12 text-white shadow-xl overflow-hidden relative">
          <div class="absolute -right-12 -top-12 w-48 h-48 bg-white opacity-5 rounded-full"></div>
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-6">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              <h3 class="text-3xl font-black">Aksi Cepat</h3>
            </div>
            <p class="text-indigo-100 mb-6 text-lg">Mulai konsultasi dengan dokter atau lihat riwayat Anda</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <button
                @click="showBuatKonsultasi = true"
                class="bg-white text-indigo-600 px-8 py-4 rounded-2xl font-bold hover:shadow-2xl active:scale-95 transition flex items-center justify-center gap-3 shadow-lg text-base"
              >
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Konsultasi
              </button>
              <router-link
                to="/cari-dokter"
                class="bg-indigo-500 hover:bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold active:scale-95 transition flex items-center justify-center gap-3 text-base shadow-lg hover:shadow-2xl"
              >
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Cari Dokter
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Tab -->
      <div v-if="activeTab === 'riwayat'" class="space-y-6">
        <!-- Filter Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <button
            @click="filterStatus = ''"
            :class="['px-4 py-3 rounded-2xl font-bold transition text-center shadow-md hover:shadow-lg flex items-center justify-center gap-2 relative',
              filterStatus === '' 
                ? 'bg-linear-to-r from-indigo-600 to-purple-600 text-white' 
                : 'bg-white text-gray-700 border border-gray-200 hover:border-indigo-300']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m0 0V3a2 2 0 00-2-2h-2a2 2 0 00-2 2v2z"/></svg>
            <span>Semua</span>
            <span v-if="allKonsultasi.length > 0" :class="['text-xs font-black px-2 py-0.5 rounded-full', filterStatus === '' ? 'bg-white bg-opacity-30' : 'bg-gray-200']">{{ allKonsultasi.length }}</span>
          </button>
          <button
            @click="filterStatus = 'pending'"
            :class="['px-4 py-3 rounded-2xl font-bold transition text-center shadow-md hover:shadow-lg flex items-center justify-center gap-2',
              filterStatus === 'pending'
                ? 'bg-linear-to-r from-amber-500 to-orange-600 text-white' 
                : 'bg-white text-gray-700 border border-gray-200 hover:border-amber-300']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Menunggu</span>
            <span v-if="getCountByStatus('pending') > 0" :class="['text-xs font-black px-2 py-0.5 rounded-full', filterStatus === 'pending' ? 'bg-white bg-opacity-30' : 'bg-gray-200']">{{ getCountByStatus('pending') }}</span>
          </button>
          <button
            @click="filterStatus = 'active'"
            :class="['px-4 py-3 rounded-2xl font-bold transition text-center shadow-md hover:shadow-lg flex items-center justify-center gap-2',
              filterStatus === 'active' 
                ? 'bg-linear-to-r from-blue-500 to-cyan-600 text-white' 
                : 'bg-white text-gray-700 border border-gray-200 hover:border-blue-300']"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="currentColor"/></svg>
            <span>Aktif</span>
            <span v-if="getCountByStatus('active') > 0" :class="['text-xs font-black px-2 py-0.5 rounded-full', filterStatus === 'active' ? 'bg-white bg-opacity-30' : 'bg-gray-200']">{{ getCountByStatus('active') }}</span>
          </button>
          <button
            @click="filterStatus = 'closed'"
            :class="['px-4 py-3 rounded-2xl font-bold transition text-center shadow-md hover:shadow-lg flex items-center justify-center gap-2',
              filterStatus === 'closed' 
                ? 'bg-linear-to-r from-green-500 to-emerald-600 text-white' 
                : 'bg-white text-gray-700 border border-gray-200 hover:border-green-300']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Selesai</span>
            <span v-if="getCountByStatus('closed') > 0" :class="['text-xs font-black px-2 py-0.5 rounded-full', filterStatus === 'closed' ? 'bg-white bg-opacity-30' : 'bg-gray-200']">{{ getCountByStatus('closed') }}</span>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loadingRiwayat" class="text-center py-12 bg-white rounded-3xl shadow-lg">
          <div class="animate-spin w-8 h-8 border-4 border-gray-200 border-t-indigo-600 rounded-full mx-auto mb-4"></div>
          <p class="text-gray-600 font-medium">Memuat riwayat konsultasi...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredKonsultasi.length === 0" class="text-center py-12 bg-white rounded-3xl shadow-lg border border-gray-100">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m0 0V3a2 2 0 00-2-2h-2a2 2 0 00-2 2v2z" />
            </svg>
          </div>
          <p class="text-lg font-bold text-gray-700">Belum Ada Riwayat</p>
          <p class="text-gray-600 mt-2">Anda belum melakukan konsultasi</p>
        </div>

        <!-- Konsultasi List -->
        <div v-else class="space-y-4">
          <div 
            v-for="k in filteredKonsultasi" 
            :key="k.id" 
            :class="['rounded-2xl p-6 border-l-4 hover:shadow-lg transition bg-white shadow-md',
              k.status === 'active' ? 'border-l-blue-500 bg-linear-to-r from-blue-50 to-white' :
              k.status === 'pending' ? 'border-l-amber-500 bg-linear-to-r from-amber-50 to-white' :
              k.status === 'closed' ? 'border-l-green-500 bg-linear-to-r from-green-50 to-white' :
              'border-l-red-500 bg-linear-to-r from-red-50 to-white'
            ]"
          >
            <div class="flex justify-between items-start gap-4">
              <div class="flex-1 min-w-0">
                <!-- Doctor Info -->
                <div class="flex items-center gap-4 mb-3">
                  <div class="w-12 h-12 rounded-full bg-linear-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-md">
                    {{ k.dokter?.pengguna?.name?.charAt(0).toUpperCase() || k.dokter?.name?.charAt(0).toUpperCase() }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-gray-900 text-lg">Dr. {{ k.dokter?.pengguna?.name || k.dokter?.name }}</h4>
                    <p class="text-xs text-gray-600 mt-0.5">{{ k.dokter?.specialization || 'Dokter Umum' }}</p>
                  </div>
                </div>

                <!-- Keluhan & Status -->
                <div class="mb-3">
                  <p class="text-sm text-gray-600 font-medium mb-2">
                    <span class="font-bold">Keluhan:</span> {{ k.complaint_type || k.jenis_keluhan }}
                  </p>
                  <p class="text-sm text-gray-700 line-clamp-2">{{ k.description || k.deskripsi }}</p>
                </div>

                <!-- Date -->
                <p class="text-xs text-gray-500 font-medium">
                  {{ new Date(k.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                </p>
              </div>

              <!-- Status Badge & Action -->
              <div class="flex flex-col items-end gap-3 shrink-0">
                <span :class="['text-xs px-4 py-2 rounded-full font-bold whitespace-nowrap',
                  k.status === 'closed' ? 'bg-green-100 text-green-700' :
                  k.status === 'active' ? 'bg-blue-100 text-blue-700' :
                  k.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                  'bg-red-100 text-red-700'
                ]">
                  {{ k.status === 'closed' ? 'Selesai' : 
                     k.status === 'active' ? 'Aktif' :
                     k.status === 'pending' ? 'Menunggu' : 'Ditolak' }}
                </span>
                
                <router-link
                  :to="`/konsultasi/${k.id}`"
                  class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-bold transition flex items-center gap-1"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                  Detail
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Buat Konsultasi -->
    <BuatKonsultasiModal
      v-if="showBuatKonsultasi"
      @close="showBuatKonsultasi = false"
      @success="handleKonsultasiBuat"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { konsultasiAPI } from '@/api/konsultasi'
import BuatKonsultasiModal from '@/components/modals/BuatKonsultasiModal.vue'

const authStore = useAuthStore()
const activeTab = ref('overview')
const showBuatKonsultasi = ref(false)
const stats = ref({ aktif: 0, menunggu: 0, total: 0 })
const allKonsultasi = ref([])
const filterStatus = ref('')
const loadingRiwayat = ref(false)

const filteredKonsultasi = computed(() => {
  const filtered = !filterStatus.value 
    ? allKonsultasi.value 
    : allKonsultasi.value.filter(k => k.status === filterStatus.value)
  
  console.log(`Filter: "${filterStatus.value}" | Showing: ${filtered.length}/${allKonsultasi.value.length}`)
  return filtered
})

// Helper function to get count by status
const getCountByStatus = (status) => {
  if (!status) return allKonsultasi.value.length
  return allKonsultasi.value.filter(k => k.status === status).length
}

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  try {
    // Load semua data sekali saja
    const response = await konsultasiAPI.getList({})
    const data = response.data.data || response.data || []
    
    console.log('Dashboard data loaded:', data)
    
    // Update allKonsultasi dengan data terbaru
    allKonsultasi.value = data
    
    // Update stats dari data yang sama (menggunakan English enum values)
    stats.value = {
      aktif: data.filter(k => k.status === 'active').length,
      menunggu: data.filter(k => k.status === 'pending').length,
      total: data.length
    }
  } catch (error) {
    console.error('Error loading data:', error)
    allKonsultasi.value = []
  }
}

const loadRiwayat = async () => {
  loadingRiwayat.value = true
  try {
    const response = await konsultasiAPI.getList({})
    const data = response.data.data || response.data || []
    console.log('Riwayat konsultasi loaded:', data)
    allKonsultasi.value = data
  } catch (error) {
    console.error('Error loading riwayat:', error)
    allKonsultasi.value = []
  } finally {
    loadingRiwayat.value = false
  }
}

const handleKonsultasiBuat = async () => {
  showBuatKonsultasi.value = false
  await loadData()
  // Reset filter dan switch to Riwayat tab to show new consultation
  filterStatus.value = ''
  activeTab.value = 'riwayat'
}

watch(() => activeTab.value, (newTab) => {
  if (newTab === 'riwayat') {
    // Always reload data when switching to riwayat tab to ensure fresh data
    loadRiwayat()
  }
})
</script>
