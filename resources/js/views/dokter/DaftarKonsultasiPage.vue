<!-- 📁 resources/js/views/dokter/DaftarKonsultasiPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Header Hero - Enhanced -->
      <div class="mb-12">
        <div class="inline-flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-linear-to-br from-indigo-600 to-purple-600 rounded-3xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM9 19c-4.35 0-8 1.79-8 4v2h16v-2c0-2.21-3.65-4-8-4z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900">Daftar Konsultasi</h1>
            <p class="text-gray-600 mt-2 text-lg">{{ allKonsultasi.length }} konsultasi • Kelola semua permintaan dari pasien</p>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
          <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition">
            <p class="text-gray-600 text-xs font-bold uppercase tracking-wide">Total</p>
            <p class="text-2xl font-black text-indigo-600 mt-2">{{ allKonsultasi.length }}</p>
          </div>
          <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm hover:shadow-md transition">
            <p class="text-amber-600 text-xs font-bold uppercase tracking-wide">Menunggu</p>
            <p class="text-2xl font-black text-amber-600 mt-2">{{ getCountByStatus('pending') }}</p>
          </div>
          <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm hover:shadow-md transition">
            <p class="text-blue-600 text-xs font-bold uppercase tracking-wide">Aktif</p>
            <p class="text-2xl font-black text-blue-600 mt-2">{{ getCountByStatus('active') }}</p>
          </div>
          <div class="bg-white rounded-2xl p-4 border border-green-100 shadow-sm hover:shadow-md transition">
            <p class="text-green-600 text-xs font-bold uppercase tracking-wide">Selesai</p>
            <p class="text-2xl font-black text-green-600 mt-2">{{ getCountByStatus('closed') }}</p>
          </div>
        </div>
      </div>

      <!-- Filter Pills - Enhanced -->
      <div class="mb-10 sticky top-16 bg-white bg-opacity-95 backdrop-blur-md -mx-4 sm:mx-0 px-4 sm:px-0 py-4 sm:py-0 z-20">
        <div class="flex flex-wrap gap-3 items-center">
          <button
            @click="filterStatus = ''"
            :class="['px-5 py-2.5 rounded-full font-semibold transition flex items-center gap-2 shadow-sm hover:shadow-md',
              filterStatus === '' 
                ? 'bg-linear-to-r from-indigo-600 to-purple-600 text-white shadow-lg scale-105'
                : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-indigo-300 hover:text-indigo-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
            Semua
            <span class="text-xs font-bold opacity-75">{{ allKonsultasi.length }}</span>
          </button>
          <button
            @click="filterStatus = 'pending'"
            :class="['px-5 py-2.5 rounded-full font-semibold transition flex items-center gap-2 shadow-sm hover:shadow-md',
              filterStatus === 'pending'
                ? 'bg-linear-to-r from-amber-500 to-orange-500 text-white shadow-lg scale-105'
                : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-amber-300 hover:text-amber-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Menunggu
            <span class="text-xs font-bold opacity-75">{{ getCountByStatus('pending') }}</span>
          </button>
          <button
            @click="filterStatus = 'active'"
            :class="['px-5 py-2.5 rounded-full font-semibold transition flex items-center gap-2 shadow-sm hover:shadow-md',
              filterStatus === 'active'
                ? 'bg-linear-to-r from-blue-500 to-cyan-500 text-white shadow-lg scale-105'
                : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-blue-300 hover:text-blue-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Aktif
            <span class="text-xs font-bold opacity-75">{{ getCountByStatus('active') }}</span>
          </button>
          <button
            @click="filterStatus = 'closed'"
            :class="['px-5 py-2.5 rounded-full font-semibold transition flex items-center gap-2 shadow-sm hover:shadow-md',
              filterStatus === 'closed'
                ? 'bg-linear-to-r from-green-500 to-emerald-500 text-white shadow-lg scale-105'
                : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-green-300 hover:text-green-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Selesai
            <span class="text-xs font-bold opacity-75">{{ getCountByStatus('closed') }}</span>
          </button>
          <button
            @click="filterStatus = 'cancelled'"
            :class="['px-5 py-2.5 rounded-full font-semibold transition flex items-center gap-2 shadow-sm hover:shadow-md',
              filterStatus === 'cancelled'
                ? 'bg-linear-to-r from-red-500 to-pink-500 text-white shadow-lg scale-105'
                : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-red-300 hover:text-red-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Ditolak
            <span class="text-xs font-bold opacity-75">{{ getCountByStatus('cancelled') }}</span>
          </button>

          <!-- Refresh Button (Spacer + Button) -->
          <div class="flex-1"></div>
          <button
            @click="refreshData"
            :disabled="loading"
            :class="['px-5 py-2.5 rounded-full font-semibold transition flex items-center gap-2 shadow-sm',
              loading ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600 text-white hover:shadow-md']"
            title="Refresh data (Ctrl+R)"
          >
            <svg v-if="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
        </div>
      </div>

      <!-- Loading State with Skeleton Loaders -->
      <div v-if="loading" class="space-y-4 pb-12">
        <SkeletonLoader type="row" />
        <SkeletonLoader type="row" />
        <SkeletonLoader type="row" />
      </div>

      <!-- Empty State with Better Design -->
      <div v-else-if="filteredKonsultasi.length === 0" class="bg-white rounded-3xl shadow-lg p-16 text-center border border-gray-100">
        <div class="mb-6 flex justify-center">
          <div class="w-20 h-20 bg-linear-to-br from-indigo-100 to-purple-100 rounded-3xl flex items-center justify-center">
            <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-2">Tidak ada konsultasi</p>
        <p class="text-gray-600 mb-6">{{ filterStatus ? 'Coba ubah filter untuk melihat konsultasi lain' : 'Belum ada permintaan konsultasi dari pasien' }}</p>
        <button v-if="!filterStatus" @click="refreshData" class="px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-lg transition">
          🔄 Cek Konsultasi Baru
        </button>
      </div>

      <!-- Consultations Grid - Enhanced with Animations -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-12 animate-fadeIn">
        <div
          v-for="(k, index) in filteredKonsultasi"
          :key="k.id"
          class="group bg-white rounded-3xl shadow-md hover:shadow-2xl transition-all duration-300 border border-gray-100 hover:border-indigo-300 hover:-translate-y-2 cursor-pointer overflow-hidden animate-slideUp"
          :style="{ animationDelay: `${index * 50}ms` }"
        >
          <!-- Top accent bar with animation -->
          <div :class="['absolute top-0 left-0 right-0 h-1.5 bg-linear-to-r',
            k.status === 'closed' ? 'from-green-500 to-emerald-500' :
            k.status === 'active' ? 'from-blue-500 to-cyan-500' :
            k.status === 'pending' ? 'from-amber-500 to-orange-500' :
            'from-red-500 to-pink-500'
          ]"></div>

          <router-link :to="`/dokter/konsultasi/${k.id}`" class="block h-full">
            <div class="p-6 space-y-4">
              <!-- Header with Patient Avatar -->
              <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-linear-to-br from-pink-500 to-rose-600 flex items-center justify-center text-white font-bold text-2xl shrink-0 shadow-lg group-hover:scale-110 transition-transform ring-2 ring-pink-100">
                  {{ k.pasien?.pengguna?.name?.charAt(0).toUpperCase() || 'P' }}
                </div>
                
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-gray-900 text-lg truncate group-hover:text-indigo-600 transition">{{ k.pasien?.pengguna?.name || 'Pasien' }}</h3>
                  <p class="text-sm text-pink-600 font-semibold">Pasien</p>
                  <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ new Date(k.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                  </p>
                </div>
              </div>

              <!-- Complaint Preview with improved styling -->
              <div class="bg-linear-to-br from-gray-50 to-gray-100 rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-600 font-bold uppercase tracking-wider mb-2">📋 Keluhan</p>
                <p class="text-sm text-gray-800 font-medium line-clamp-2 group-hover:text-gray-900">{{ k.complaint_type || k.jenis_keluhan }}</p>
              </div>

              <!-- Service Type & Status Row -->
              <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                <span :class="['text-xs px-3 py-1.5 rounded-full font-bold whitespace-nowrap',
                  k.tipe_layanan === 'video' ? 'bg-blue-100 text-blue-700' :
                  k.tipe_layanan === 'chat' ? 'bg-purple-100 text-purple-700' :
                  'bg-green-100 text-green-700'
                ]">
                  {{ k.tipe_layanan === 'video' ? '📹 Video' : k.tipe_layanan === 'chat' ? '💬 Chat' : '📞 Telepon' }}
                </span>

                <span :class="['text-xs px-3 py-1.5 rounded-full font-bold whitespace-nowrap',
                  k.status === 'closed' ? 'bg-green-100 text-green-700' :
                  k.status === 'active' ? 'bg-blue-100 text-blue-700' :
                  k.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                  'bg-red-100 text-red-700'
                ]">
                  {{ k.status === 'closed' ? '✓ Selesai' : 
                     k.status === 'active' ? '⚡ Aktif' :
                     k.status === 'pending' ? '⏱ Menunggu' : '✕ Ditolak' }}
                </span>

                <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { konsultasiAPI } from '@/api/konsultasi'
import SkeletonLoader from '@/components/SkeletonLoader.vue'

const allKonsultasi = ref([])
const filterStatus = ref('')
const loading = ref(false)

// Store keyboard handler reference for cleanup
let handleKeypress = null

const filteredKonsultasi = computed(() => {
  if (!filterStatus.value) return allKonsultasi.value
  return allKonsultasi.value.filter(k => k.status === filterStatus.value)
})

const getCountByStatus = (status) => {
  if (!status) return allKonsultasi.value.length
  return allKonsultasi.value.filter(k => k.status === status).length
}

const refreshData = async () => {
  loading.value = true
  try {
    const response = await konsultasiAPI.getList({})
    allKonsultasi.value = response.data.data || response.data || []
    console.log('✅ Data refreshed successfully:', {
      totalRecords: allKonsultasi.value.length,
      timestamp: new Date().toLocaleString('id-ID'),
      response: response.data
    })
  } catch (error) {
    console.error('❌ Error refreshing konsultasi:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    })
    allKonsultasi.value = []
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await refreshData()

  // Add keyboard shortcut for refresh (Ctrl+R)
  handleKeypress = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
      e.preventDefault()
      refreshData()
    }
  }
  window.addEventListener('keydown', handleKeypress)
})

onUnmounted(() => {
  // Cleanup keyboard listener
  if (handleKeypress) {
    window.removeEventListener('keydown', handleKeypress)
  }
})
</script>
