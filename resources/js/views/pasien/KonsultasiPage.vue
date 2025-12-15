<!-- 📁 resources/js/views/pasien/KonsultasiPage.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header - Simplified -->
      <div class="py-4 border-b border-gray-200">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 bg-linear-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-md">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Konsultasi</h1>
            <p class="text-sm text-gray-600">{{ allKonsultasi.length }} konsultasi</p>
          </div>
        </div>

        <!-- Summary Stats - Compact -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
            <p class="text-gray-600 text-xs font-semibold">Total</p>
            <p class="text-xl font-bold text-indigo-600 mt-1">{{ allKonsultasi.length }}</p>
          </div>
          <div class="bg-white rounded-lg p-3 border border-amber-100 shadow-sm">
            <p class="text-amber-600 text-xs font-semibold">Menunggu</p>
            <p class="text-xl font-bold text-amber-600 mt-1">{{ getCountByStatus('pending') }}</p>
          </div>
          <div class="bg-white rounded-lg p-3 border border-blue-100 shadow-sm">
            <p class="text-blue-600 text-xs font-semibold">Aktif</p>
            <p class="text-xl font-bold text-blue-600 mt-1">{{ getCountByStatus('active') }}</p>
          </div>
          <div class="bg-white rounded-lg p-3 border border-green-100 shadow-sm">
            <p class="text-green-600 text-xs font-semibold">Selesai</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ getCountByStatus('closed') }}</p>
          </div>
        </div>
      </div>

      <!-- Filter Pills - Simplified -->
      <div class="sticky top-16 bg-white z-40 border-b border-gray-200 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-wrap gap-2">
        <button
          @click="filterStatus = ''"
          :class="['px-4 py-2 rounded-full font-semibold text-xs transition flex items-center gap-1.5 shadow-sm',
            filterStatus === '' 
              ? 'bg-indigo-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:border-indigo-300']"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
          Semua
        </button>
        <button
          @click="filterStatus = 'pending'"
          :class="['px-4 py-2 rounded-full font-semibold text-xs transition flex items-center gap-1.5 shadow-sm',
            filterStatus === 'pending'
              ? 'bg-amber-500 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:border-amber-300']"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Menunggu
        </button>
        <button
          @click="filterStatus = 'active'"
          :class="['px-4 py-2 rounded-full font-semibold text-xs transition flex items-center gap-1.5 shadow-sm',
            filterStatus === 'active'
              ? 'bg-blue-500 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:border-blue-300']"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          Aktif
        </button>
        <button
          @click="filterStatus = 'closed'"
          :class="['px-4 py-2 rounded-full font-semibold text-xs transition flex items-center gap-1.5 shadow-sm',
            filterStatus === 'closed'
              ? 'bg-green-500 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:border-green-300']"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Selesai
        </button>
        <button
          @click="filterStatus = 'cancelled'"
          :class="['px-4 py-2 rounded-full font-semibold text-xs transition flex items-center gap-1.5 shadow-sm',
            filterStatus === 'cancelled'
              ? 'bg-red-500 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:border-red-300']"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Ditolak
        </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4 pb-12">
        <SkeletonLoader type="row" />
        <SkeletonLoader type="row" />
        <SkeletonLoader type="row" />
      </div>

      <!-- Empty State with Better Design -->
      <div v-else-if="filteredKonsultasi.length === 0" class="bg-white rounded-3xl shadow-lg p-16 text-center border border-gray-100 my-8">
        <div class="mb-6 flex justify-center">
          <div class="w-20 h-20 bg-linear-to-br from-indigo-100 to-purple-100 rounded-3xl flex items-center justify-center">
            <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-2">Belum ada konsultasi</p>
        <p class="text-gray-600 mb-6">{{ filterStatus ? 'Coba ubah filter untuk melihat konsultasi lain' : 'Mulai konsultasi dengan dokter terpercaya sekarang' }}</p>
        <router-link v-if="!filterStatus" to="/cari-dokter" class="inline-block px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-lg transition">
          🔍 Cari Dokter
        </router-link>
      </div>

      <!-- Consultations Grid - Compact with Animations -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 py-6 pb-12 animate-fadeIn">
        <div
          v-for="(k, index) in filteredKonsultasi"
          :key="k.id"
          class="group bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all border border-gray-100 hover:border-indigo-300 cursor-pointer overflow-hidden animate-slideUp"
          :style="{ animationDelay: `${index * 50}ms` }"
        >
          <!-- Top accent bar -->
          <div :class="['h-1 bg-linear-to-r',
            k.status === 'closed' ? 'from-green-500 to-emerald-500' :
            k.status === 'active' ? 'from-blue-500 to-cyan-500' :
            k.status === 'pending' ? 'from-amber-500 to-orange-500' :
            'from-red-500 to-pink-500'
          ]"></div>

          <router-link :to="`/konsultasi/${k.id}`" class="block">
            <div class="p-4">
              <!-- Header with Doctor -->
              <div class="flex items-start gap-3 mb-3">
                <div class="w-12 h-12 rounded-xl bg-linear-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-md">
                  {{ k.dokter?.pengguna?.name?.charAt(0).toUpperCase() || 'D' }}
                </div>
                
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-gray-900 text-sm truncate">Dr. {{ k.dokter?.pengguna?.name || 'Dokter' }}</h3>
                  <p class="text-xs text-indigo-600 font-semibold">{{ k.dokter?.specialization || 'Dokter Umum' }}</p>
                </div>
              </div>

              <!-- Complaint -->
              <div class="bg-gray-50 rounded-xl p-3 mb-3 border border-gray-200">
                <p class="text-xs text-gray-700 font-medium line-clamp-2">{{ k.complaint_type || k.jenis_keluhan }}</p>
              </div>

              <!-- Footer Info -->
              <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                <span :class="['text-xs px-2.5 py-1 rounded-full font-bold',
                  k.tipe_layanan === 'video' ? 'bg-blue-100 text-blue-700' :
                  k.tipe_layanan === 'chat' ? 'bg-purple-100 text-purple-700' :
                  'bg-green-100 text-green-700'
                ]">
                  {{ k.tipe_layanan === 'video' ? '📹' : k.tipe_layanan === 'chat' ? '💬' : '📞' }}
                </span>

                <span :class="['text-xs px-2.5 py-1 rounded-full font-bold',
                  k.status === 'closed' ? 'bg-green-100 text-green-700' :
                  k.status === 'active' ? 'bg-blue-100 text-blue-700' :
                  k.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                  'bg-red-100 text-red-700'
                ]">
                  {{ k.status === 'closed' ? '✓' : 
                     k.status === 'active' ? '⚡' :
                     k.status === 'pending' ? '⏱' : '✕' }}
                </span>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { konsultasiAPI } from '@/api/konsultasi'
import SkeletonLoader from '@/components/SkeletonLoader.vue'

const allKonsultasi = ref([])
const filterStatus = ref('')
const loading = ref(false)

const filteredKonsultasi = computed(() => {
  if (!filterStatus.value) return allKonsultasi.value
  return allKonsultasi.value.filter(k => k.status === filterStatus.value)
})

const getCountByStatus = (status) => {
  if (!status) return allKonsultasi.value.length
  return allKonsultasi.value.filter(k => k.status === status).length
}

onMounted(async () => {
  loading.value = true
  try {
    const response = await konsultasiAPI.getList({})
    allKonsultasi.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error loading konsultasi:', error)
    allKonsultasi.value = []
  } finally {
    loading.value = false
  }
})
</script>
