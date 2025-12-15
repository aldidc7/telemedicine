<template>
  <div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header Hero - Simplified -->
      <div class="py-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-linear-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-md">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Dokter</h1>
            <p class="text-sm text-gray-600">Kelola konsultasi Anda</p>
          </div>
        </div>
      </div>

      <!-- Tabs Navigation - Simplified -->
      <div class="sticky top-16 bg-white z-40 border-b border-gray-200 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex gap-6">
          <button
            @click="activeTab = 'overview'"
            :class="['pb-3 font-semibold text-sm transition-colors flex items-center gap-2',
              activeTab === 'overview' 
                ? 'text-indigo-600 border-b-2 border-indigo-600' 
                : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Ringkasan</span>
          </button>
          <button
            @click="activeTab = 'statistik'"
            :class="['pb-3 font-semibold text-sm transition-colors flex items-center gap-2',
              activeTab === 'statistik'
                ? 'text-indigo-600 border-b-2 border-indigo-600'
                : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            <span>Statistik</span>
          </button>
        </div>
      </div>

      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="space-y-4 py-6">
        <!-- Status Ketersediaan - Simplified -->
        <div :class="['rounded-2xl p-6 text-white shadow-md transition',
          tersedia 
            ? 'bg-linear-to-br from-emerald-500 to-green-600'
            : 'bg-linear-to-br from-gray-400 to-gray-600'
        ]">
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <h3 class="text-xl font-bold">Status Ketersediaan</h3>
              <div :class="['flex items-center gap-2 text-sm font-semibold',
                tersedia ? 'text-green-100' : 'text-gray-100'
              ]">
                <span>{{ tersedia ? '🟢 Online' : '⚫ Offline' }}</span>
              </div>
            </div>
            <button
              @click="toggleKetersediaan"
              :class="[
                'px-6 py-3 rounded-xl font-bold text-sm transition active:scale-95',
                tersedia 
                  ? 'bg-white text-green-600 hover:bg-green-50' 
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              ]"
            >
              {{ tersedia ? '✓ ONLINE' : '✕ OFFLINE' }}
            </button>
          </div>
        </div>

        <!-- Key Metrics - 2 Most Important -->
        <div class="grid grid-cols-2 gap-4">
          <div class="group bg-linear-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-md hover:shadow-lg transition">
            <div class="flex justify-between items-start mb-3">
              <div>
                <p class="text-blue-100 text-xs font-medium">Aktif Sekarang</p>
                <p class="text-3xl font-bold mt-1">{{ stats.aktif }}</p>
              </div>
              <div class="w-10 h-10 bg-white bg-opacity-25 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              </div>
            </div>
            <p class="text-blue-100 text-xs">Konsultasi sedang berlangsung</p>
          </div>

          <div class="group bg-linear-to-br from-yellow-500 to-amber-600 rounded-xl p-4 text-white shadow-md hover:shadow-lg transition">
            <div class="flex justify-between items-start mb-3">
              <div>
                <p class="text-yellow-100 text-xs font-medium">Butuh Respons</p>
                <p class="text-3xl font-bold mt-1">{{ stats.menunggu }}</p>
              </div>
              <div class="w-10 h-10 bg-white bg-opacity-25 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-yellow-100 text-xs">Konsultasi baru menunggu</p>
          </div>
        </div>

        <!-- Rating Section -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <h3 class="text-lg font-bold text-gray-900">Rating & Review</h3>
              <div class="flex items-center gap-3">
                <div class="flex gap-1">
                  <svg v-for="i in 5" :key="i" :class="['w-5 h-5 transition', i <= Math.round(profile.rating || 0) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300']" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ profile.rating ? profile.rating.toFixed(1) : 'N/A' }}</span>
                <span class="text-sm text-gray-600">({{ profile.total_reviews || 0 }} reviews)</span>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-600 mb-2">Total Konsultasi Selesai</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.selesai }}</p>
            </div>
          </div>
        </div>

        <!-- Konsultasi Menunggu - Enhanced -->
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
          <div class="bg-linear-to-r from-amber-500 via-orange-500 to-red-500 text-white px-8 py-8 flex items-center justify-between relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-40 h-40 bg-white opacity-10 rounded-full"></div>
            <div class="relative z-10">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center shadow-lg">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                  <h3 class="text-3xl font-black">Menunggu Respons</h3>
                  <p class="text-orange-100 text-sm mt-1">{{ konsultasiMenunggu.length }} konsultasi baru</p>
                </div>
              </div>
            </div>
          </div>
          <div class="p-6 md:p-8">
            <div v-if="konsultasiMenunggu.length === 0" class="text-center py-12 text-gray-500">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-lg font-medium">Tidak Ada Konsultasi Menunggu</p>
              <p class="text-sm mt-1">Semua konsultasi sudah Anda respons</p>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-8">
              <div
                v-for="k in konsultasiMenunggu"
                :key="k.id"
                class="group bg-linear-to-br from-orange-50 to-yellow-50 rounded-2xl border border-orange-200 p-6 hover:shadow-2xl transition hover:border-orange-400 hover:-translate-y-1 flex flex-col cursor-pointer"
              >
                <!-- Top Accent -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-linear-to-r from-amber-500 to-orange-500 rounded-t-2xl"></div>

                <!-- Header -->
                <div class="flex items-start gap-4 mb-4">
                  <div class="w-12 h-12 bg-linear-to-br from-pink-500 to-rose-600 rounded-2xl flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                    {{ k.pasien?.pengguna?.name?.charAt(0)?.toUpperCase() || 'P' }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-gray-900 text-base truncate">{{ k.pasien?.pengguna?.name || 'Pasien' }}</h4>
                    <p class="text-sm text-orange-600 font-semibold mt-1">{{ k.complaint_type || k.jenis_keluhan }}</p>
                  </div>
                </div>

                <!-- Keluhan -->
                <div class="bg-white bg-opacity-60 rounded-xl p-4 mb-4 border border-orange-100 flex-1">
                  <p class="text-sm text-gray-700 font-medium line-clamp-3">{{ k.description || k.deskripsi }}</p>
                </div>

                <!-- Info Quick -->
                <div class="grid grid-cols-2 gap-3 mb-4 text-xs">
                  <div class="bg-white bg-opacity-70 rounded-lg p-2 border border-orange-100 text-center">
                    <p class="text-gray-700 font-bold">{{ k.tipe_layanan === 'video' ? '📹 Video' : k.tipe_layanan === 'chat' ? '💬 Chat' : '📞 Telepon' }}</p>
                  </div>
                  <div class="bg-white bg-opacity-70 rounded p-1.5 border border-yellow-100 text-center">
                    <p class="text-gray-600 font-medium">{{ new Date(k.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</p>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                  <button
                    @click="terimaKonsultasi(k.id)"
                    class="flex-1 bg-linear-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-3 py-2 rounded-lg font-bold text-xs transition shadow hover:shadow-md active:scale-95 flex items-center justify-center gap-1"
                    title="Terima Konsultasi"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="hidden sm:inline">Terima</span>
                  </button>
                  <button
                    @click="tolakKonsultasi(k.id)"
                    class="flex-1 bg-linear-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white px-3 py-2 rounded-lg font-bold text-xs transition shadow hover:shadow-md active:scale-95 flex items-center justify-center gap-1"
                    title="Tolak Konsultasi"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="hidden sm:inline">Tolak</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistik Tab -->
      <div v-else-if="activeTab === 'statistik'" class="space-y-8">
        <!-- Statistik Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Konsultasi -->
          <div class="bg-linear-to-br from-indigo-500 to-indigo-600 rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-indigo-100 text-sm font-bold uppercase tracking-wide">Total Konsultasi</p>
                <p class="text-5xl font-black mt-3">{{ stats.aktif + stats.menunggu + stats.selesai }}</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
              </div>
            </div>
            <p class="text-indigo-100 text-sm">Sepanjang masa</p>
          </div>

          <!-- Konsultasi Aktif -->
          <div class="bg-linear-to-br from-blue-500 to-blue-600 rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-blue-100 text-sm font-bold uppercase tracking-wide">Aktif Sekarang</p>
                <p class="text-5xl font-black mt-3">{{ stats.aktif }}</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              </div>
            </div>
            <p class="text-blue-100 text-sm">Sedang berlangsung</p>
          </div>

          <!-- Menunggu Respons -->
          <div class="bg-linear-to-br from-amber-500 to-orange-600 rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-amber-100 text-sm font-bold uppercase tracking-wide">Menunggu</p>
                <p class="text-5xl font-black mt-3">{{ stats.menunggu }}</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-amber-100 text-sm">Butuh respons</p>
          </div>

          <!-- Selesai -->
          <div class="bg-linear-to-br from-green-500 to-emerald-600 rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-green-100 text-sm font-bold uppercase tracking-wide">Selesai</p>
                <p class="text-5xl font-black mt-3">{{ stats.selesai }}</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-green-100 text-sm">Konsultasi rampung</p>
          </div>
        </div>

        <!-- Info Box -->
        <div class="bg-linear-to-r from-indigo-50 to-purple-50 rounded-3xl p-8 border border-indigo-200">
          <h3 class="text-2xl font-black text-gray-900 mb-4">📊 Ringkasan Performa</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <p class="text-gray-600 font-semibold">Response Rate</p>
              <p class="text-3xl font-black text-indigo-600">{{ stats.menunggu > 0 ? Math.round((stats.aktif / (stats.aktif + stats.menunggu)) * 100) : 0 }}%</p>
              <p class="text-sm text-gray-600">Konsultasi yang sudah Anda respons</p>
            </div>
            <div class="space-y-2">
              <p class="text-gray-600 font-semibold">Completion Rate</p>
              <p class="text-3xl font-black text-green-600">{{ stats.selesai > 0 ? Math.round((stats.selesai / (stats.aktif + stats.selesai + stats.menunggu)) * 100) : 0 }}%</p>
              <p class="text-sm text-gray-600">Konsultasi yang sudah selesai</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDokterAvailability } from '@/stores/dokterAvailability'
import { dokterAPI } from '@/api/dokter'
import { konsultasiAPI } from '@/api/konsultasi'

const authStore = useAuthStore()
const dokterAvailabilityStore = useDokterAvailability()
const activeTab = ref('overview')
const tersedia = computed(() => dokterAvailabilityStore.isAvailable)
const stats = ref({ aktif: 0, menunggu: 0, selesai: 0, selesaiHariIni: 0 })
const konsultasiMenunggu = ref([])
const profile = ref({
  name: '',
  specialization: '',
  rating: 0,
  total_reviews: 0
})


onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  try {
    // Parallel loading untuk lebih cepat
    const [menuResponse, allResponse, dokterResponse] = await Promise.all([
      konsultasiAPI.getList({ status: 'pending' }),
      konsultasiAPI.getList({}),
      dokterAPI.getDetail(authStore.user?.dokter?.id)
    ])

    konsultasiMenunggu.value = menuResponse.data.data
    
    const data = allResponse.data.data
    stats.value = {
      aktif: data.filter(k => k.status === 'active').length,
      menunggu: data.filter(k => k.status === 'pending').length,
      selesai: data.filter(k => k.status === 'closed').length,
      selesaiHariIni: data.filter(
        k => k.status === 'closed' && 
        new Date(k.waktu_selesai).toDateString() === new Date().toDateString()
      ).length
    }

    // Update store with availability status from backend
    if (dokterResponse.data.data) {
      dokterAvailabilityStore.isAvailable = dokterResponse.data.data.tersedia || dokterResponse.data.data.is_available
      // Also load profile data with rating
      profile.value = {
        name: dokterResponse.data.data.user?.name || dokterResponse.data.data.pengguna?.name || '',
        specialization: dokterResponse.data.data.specialization || 'Dokter Umum',
        rating: dokterResponse.data.data.rating || 0,
        total_reviews: dokterResponse.data.data.total_reviews || dokterResponse.data.data.review_count || 0
      }
    }
  } catch (error) {
    console.error('Error loading data:', error)
  }
}

const toggleKetersediaan = async () => {
  try {
    await dokterAvailabilityStore.toggleAvailability()
  } catch (error) {
    console.error('Error updating ketersediaan:', error)
    alert('Gagal mengubah status ketersediaan')
  }
}

const terimaKonsultasi = async (konsultasiId) => {
  const router = useRouter()
  try {
    await konsultasiAPI.terima(konsultasiId)
    // Redirect ke halaman chat langsung setelah accept
    router.push(`/dokter/chat/${konsultasiId}`)
  } catch (error) {
    console.error('Error accepting consultation:', error)
  }
}

const tolakKonsultasi = async (konsultasiId) => {
  const alasan = prompt('Alasan menolak:')
  if (!alasan) return

  try {
    await konsultasiAPI.tolak(konsultasiId, { alasan })
    await loadData()
  } catch (error) {
    console.error('Error rejecting consultation:', error)
  }
}
</script>
