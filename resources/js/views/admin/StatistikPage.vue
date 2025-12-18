<!-- 📁 resources/js/views/admin/StatistikPage.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-10">
      <div class="flex items-center gap-3 mb-2">
        <svg class="w-11 h-11 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <h1 class="text-4xl font-bold text-gray-900">Statistik & Analytics</h1>
      </div>
      <p class="text-gray-600">Lihat ringkasan lengkap dan analisis sistem telemedicine</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <LoadingSpinner message="Memuat statistik..." />
    </div>

    <div v-else class="space-y-10">
      <!-- Summary Stats KPI -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Pengguna -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Pengguna</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.totalUser }}</p>
              <p class="text-xs text-gray-500 mt-2">Pasien + Dokter + Admin</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 20h5v-2a3 3 0 00-5.856-1.487M7 20H2v-2a3 3 0 015.856-1.487M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zM5 7a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Total Konsultasi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Konsultasi</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.totalKonsultasi }}</p>
              <p class="text-xs text-gray-500 mt-2">Semua waktu</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-green-100 to-green-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Konsultasi Aktif -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Konsultasi Aktif</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.konsultasiAktif }}</p>
              <p class="text-xs text-gray-500 mt-2">Sedang berlangsung</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-yellow-100 to-yellow-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Selesai Bulan Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Selesai Bulan Ini</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.selesaiBulanIni }}</p>
              <p class="text-xs text-gray-500 mt-2">Bulan ini</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-purple-100 to-purple-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4M7 20H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-5l-4 4z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- User Breakdown -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
        <div class="flex items-center gap-3 mb-8">
          <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 4.354a4 4 0 110 5.292M15 21H3a6 6 0 016-6h6a6 6 0 016 6h-2a4 4 0 00-4-4h-6a4 4 0 00-4 4H3z"/>
          </svg>
          <h2 class="text-2xl font-bold text-gray-900">Breakdown Pengguna</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Total Pasien -->
          <div class="bg-linear-to-br from-blue-50 to-blue-100 p-6 rounded-2xl border-2 border-blue-200">
            <div class="text-center">
              <svg class="w-12 h-12 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Total Pasien</p>
              <p class="text-4xl font-bold text-blue-600 mt-3">{{ stats.totalPasien }}</p>
            </div>
          </div>

          <!-- Total Dokter -->
          <div class="bg-linear-to-br from-green-50 to-green-100 p-6 rounded-2xl border-2 border-green-200">
            <div class="text-center">
              <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Total Dokter</p>
              <p class="text-4xl font-bold text-green-600 mt-3">{{ stats.totalDokter }}</p>
            </div>
          </div>

          <!-- Dokter Tersedia -->
          <div class="bg-linear-to-br from-yellow-50 to-yellow-100 p-6 rounded-2xl border-2 border-yellow-200">
            <div class="text-center">
              <svg class="w-12 h-12 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Dokter Tersedia</p>
              <p class="text-4xl font-bold text-yellow-600 mt-3">{{ stats.dokterTersedia }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Konsultasi Status -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
        <div class="flex items-center gap-3 mb-8">
          <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12 0c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
          </svg>
          <h2 class="text-2xl font-bold text-gray-900">Status Konsultasi</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Menunggu -->
          <div class="bg-linear-to-br from-yellow-50 to-yellow-100 p-6 rounded-2xl border-2 border-yellow-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 1.5m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Menunggu</p>
              <p class="text-4xl font-bold text-yellow-600 mt-3">{{ stats.statusMenunggu }}</p>
            </div>
          </div>

          <!-- Aktif -->
          <div class="bg-linear-to-br from-green-50 to-green-100 p-6 rounded-2xl border-2 border-green-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Aktif</p>
              <p class="text-4xl font-bold text-green-600 mt-3">{{ stats.statusAktif }}</p>
            </div>
          </div>

          <!-- Selesai -->
          <div class="bg-linear-to-br from-blue-50 to-blue-100 p-6 rounded-2xl border-2 border-blue-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Selesai</p>
              <p class="text-4xl font-bold text-blue-600 mt-3">{{ stats.statusSelesai }}</p>
            </div>
          </div>

          <!-- Dibatalkan -->
          <div class="bg-linear-to-br from-red-50 to-red-100 p-6 rounded-2xl border-2 border-red-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-red-600 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Dibatalkan</p>
              <p class="text-4xl font-bold text-red-600 mt-3">{{ stats.statusDibatalkan }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Age Group Distribution (untuk pasien) -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
        <div class="flex items-center gap-3 mb-8">
          <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
          </svg>
          <h2 class="text-2xl font-bold text-gray-900">Distribusi Usia Pasien</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Anak-anak -->
          <div class="bg-linear-to-br from-pink-50 to-pink-100 p-6 rounded-2xl border-2 border-pink-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-pink-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2.757-1.351a1 1 0 00-.946 0L14 7m6 0l-2.757 1.351a1 1 0 00-.946 0L10 7m0 0L7.243 5.649a1 1 0 00-.946 0L4 7m6 0v2.5M4 7v2.5M12 13v2.5M4 13v2.5"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Anak-anak</p>
              <p class="text-4xl font-bold text-pink-600 mt-3">{{ stats.ageAnakAnak }}</p>
            </div>
          </div>

          <!-- Remaja -->
          <div class="bg-linear-to-br from-purple-50 to-purple-100 p-6 rounded-2xl border-2 border-purple-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Remaja</p>
              <p class="text-4xl font-bold text-purple-600 mt-3">{{ stats.ageRemaja }}</p>
            </div>
          </div>

          <!-- Dewasa -->
          <div class="bg-linear-to-br from-orange-50 to-orange-100 p-6 rounded-2xl border-2 border-orange-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Dewasa</p>
              <p class="text-4xl font-bold text-orange-600 mt-3">{{ stats.ageDewasa }}</p>
            </div>
          </div>

          <!-- Lansia -->
          <div class="bg-linear-to-br from-gray-50 to-gray-100 p-6 rounded-2xl border-2 border-gray-200">
            <div class="text-center">
              <svg class="w-10 h-10 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 8c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm0 2c-2.761 0-5 2.239-5 5v2a1 1 0 001 1h8a1 1 0 001-1v-2c0-2.761-2.239-5-5-5z"/>
              </svg>
              <p class="text-gray-700 text-sm font-semibold">Lansia</p>
              <p class="text-4xl font-bold text-gray-600 mt-3">{{ stats.ageLansia }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Spesialisasi Distribution -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
        <div class="flex items-center gap-3 mb-8">
          <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <h2 class="text-2xl font-bold text-gray-900">Distribusi Spesialisasi Dokter</h2>
        </div>
        <div class="space-y-6">
          <div v-for="(count, spesialisasi) in stats.spesialisasi" :key="spesialisasi" class="flex items-center gap-6">
            <p class="text-gray-700 font-semibold min-w-32">{{ spesialisasi }}</p>
            <div class="flex-1">
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div
                  class="bg-linear-to-r from-indigo-600 to-purple-600 h-3 rounded-full transition-all"
                  :style="{ width: ((count / stats.totalDokter) * 100) + '%' }"
                />
              </div>
            </div>
            <p class="text-gray-700 font-bold min-w-12 text-right">{{ count }}</p>
            <p class="text-gray-500 text-sm min-w-16 text-right">{{ Math.round((count / stats.totalDokter) * 100) }}%</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const loading = ref(false)
const stats = ref({
  totalUser: 0,
  totalKonsultasi: 0,
  konsultasiAktif: 0,
  selesaiBulanIni: 0,
  totalPasien: 0,
  totalDokter: 0,
  dokterTersedia: 0,
  statusMenunggu: 0,
  statusAktif: 0,
  statusSelesai: 0,
  statusDibatalkan: 0,
  ageAnakAnak: 0,
  ageRemaja: 0,
  ageDewasa: 0,
  ageLansia: 0,
  spesialisasi: {}
})

onMounted(async () => {
  await loadStatistik()
})

const loadStatistik = async () => {
  loading.value = true
  try {
    const response = await adminAPI.getStatistik()
    const data = response.data.data

    // Map response data to stats
    stats.value = {
      totalUser: data.user_stats?.total || 0,
      totalKonsultasi: data.consultation_stats?.total_konsultasi || 0,
      konsultasiAktif: data.consultation_stats?.aktif || 0,
      selesaiBulanIni: data.monthly_stats?.selesai_bulan_ini || 0,
      totalPasien: data.user_stats?.pasien || 0,
      totalDokter: data.doctor_stats?.total || 0,
      dokterTersedia: data.doctor_stats?.dokter_tersedia || 0,
      statusMenunggu: data.consultation_stats?.menunggu || 0,
      statusAktif: data.consultation_stats?.aktif || 0,
      statusSelesai: data.consultation_stats?.selesai || 0,
      statusDibatalkan: data.consultation_stats?.dibatalkan || 0,
      ageAnakAnak: data.age_distribution?.['anak-anak'] || 0,
      ageRemaja: data.age_distribution?.remaja || 0,
      ageDewasa: data.age_distribution?.dewasa || 0,
      ageLansia: data.age_distribution?.lansia || 0,
      spesialisasi: data.spesialisasi_distribution || {}
    }
  } catch (error) {
    console.error('Error loading statistik:', error)
  } finally {
    loading.value = false
  }
}
</script>
