<!-- 📁 resources/js/views/admin/DashboardPage.vue -->
<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-10">
        <h1 class="text-4xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
          Admin Dashboard
        </h1>
        <p class="text-gray-600">Pantau dan kelola sistem telemedicine Anda</p>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Users -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Pengguna</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ dashboardData.user_stats?.total || 0 }}</p>
              <p class="text-xs text-gray-500 mt-2">Pasien + Dokter + Admin</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2zM9 6a2 2 0 11-4 0 2 2 0 014 0zm0 8a2 2 0 11-4 0 2 2 0 014 0zm6-6a2 2 0 11-4 0 2 2 0 014 0zm0 8a2 2 0 11-4 0 2 2 0 014 0zm4-2a2 2 0 100 4 2 2 0 000-4z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Total Consultations -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Konsultasi</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ dashboardData.consultation_stats?.total_konsultasi || 0 }}</p>
              <p class="text-xs text-gray-500 mt-2">Semua konsultasi</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-green-100 to-green-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Available Doctors -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Dokter Tersedia</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ dashboardData.doctor_stats?.dokter_tersedia || 0 }}</p>
              <p class="text-xs text-gray-500 mt-2">Siap berkonsultasi</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-yellow-100 to-yellow-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- New Patients This Month -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Pasien Baru (Bulan Ini)</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ dashboardData.monthly_stats?.pasien_baru || 0 }}</p>
              <p class="text-xs text-gray-500 mt-2">Registrasi terbaru</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-purple-100 to-purple-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Management Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Tabs Header -->
        <div class="border-b border-gray-200">
          <div class="flex">
            <button
              @click="activeTab = 'quick-access'"
              :class="[
                'flex-1 py-4 px-6 font-semibold transition border-b-2 flex items-center justify-center gap-2',
                activeTab === 'quick-access'
                  ? 'text-indigo-600 border-indigo-600 bg-indigo-50'
                  : 'text-gray-600 border-transparent hover:text-gray-900'
              ]"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              Quick Access
            </button>
            <button
              @click="activeTab = 'system-health'"
              :class="[
                'flex-1 py-4 px-6 font-semibold transition border-b-2 flex items-center justify-center gap-2',
                activeTab === 'system-health'
                  ? 'text-indigo-600 border-indigo-600 bg-indigo-50'
                  : 'text-gray-600 border-transparent hover:text-gray-900'
              ]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              System Health
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="p-8">
          <!-- Quick Access Tab -->
          <div v-if="activeTab === 'quick-access'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kelola Pasien -->
            <router-link
              to="/admin/pasien"
              class="group p-6 rounded-2xl border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition cursor-pointer"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                  <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                  </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Kelola Pasien</h3>
              <p class="text-gray-600 text-sm">Lihat, edit, dan kelola data semua pasien terdaftar</p>
            </router-link>

            <!-- Kelola Dokter -->
            <router-link
              to="/admin/dokter"
              class="group p-6 rounded-2xl border-2 border-gray-200 hover:border-green-500 hover:bg-green-50 transition cursor-pointer"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                  <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                  </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Kelola Dokter</h3>
              <p class="text-gray-600 text-sm">Kelola profil dokter dan status ketersediaan</p>
            </router-link>

            <!-- Activity Log -->
            <router-link
              to="/admin/log"
              class="group p-6 rounded-2xl border-2 border-gray-200 hover:border-yellow-500 hover:bg-yellow-50 transition cursor-pointer"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                  <svg class="w-7 h-7 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-2.12-2.58-1.63 2.04L12 13l5.04-6.71z"/>
                  </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-yellow-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Activity Log</h3>
              <p class="text-gray-600 text-sm">Pantau semua aktivitas dan perubahan di sistem</p>
            </router-link>

            <!-- Statistik -->
            <router-link
              to="/admin/statistik"
              class="group p-6 rounded-2xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition cursor-pointer"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                  <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/>
                  </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Statistik</h3>
              <p class="text-gray-600 text-sm">Lihat statistik lengkap dan analisis sistem</p>
            </router-link>

            <!-- Advanced Analytics -->
            <router-link
              to="/admin/analytics"
              class="group p-6 rounded-2xl border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                  <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 9.5c0 .83-.67 1.5-1.5 1.5S11 13.33 11 12.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5zm3 0c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5.67-1.5 1.5-1.5 1.5.67 1.5 1.5z"/>
                  </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Advanced Analytics</h3>
              <p class="text-gray-600 text-sm">Real-time metrics, doctor performance & revenue insights</p>
            </router-link>
          </div>
              class="group p-6 rounded-2xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition cursor-pointer"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                  <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/>
                  </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Statistik</h3>
              <p class="text-gray-600 text-sm">Analisis mendalam data sistem</p>
            </router-link>
          </div>
        </div>

        <!-- System Health -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
          <div class="mb-6 pb-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
              <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
              </svg>
              System Health
            </h3>
          </div>

          <div class="space-y-4">
            <!-- API Status -->
            <div class="flex items-center justify-between p-6 bg-linear-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 13H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM20 3H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zM7 11c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900">API Server</h4>
                  <p class="text-sm text-gray-600">Connected and running</p>
                </div>
              </div>
              <span class="px-4 py-2 bg-green-200 text-green-800 rounded-full text-sm font-semibold">Active</span>
            </div>

            <!-- Database Status -->
            <div class="flex items-center justify-between p-6 bg-linear-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 13H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM20 3H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zM7 11c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900">Database</h4>
                  <p class="text-sm text-gray-600">Connected and healthy</p>
                </div>
              </div>
              <span class="px-4 py-2 bg-green-200 text-green-800 rounded-full text-sm font-semibold">Active</span>
            </div>

            <!-- Authentication Status -->
            <div class="flex items-center justify-between p-6 bg-linear-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.29 7.78-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900">Authentication</h4>
                  <p class="text-sm text-gray-600">System running</p>
                </div>
              </div>
              <span class="px-4 py-2 bg-green-200 text-green-800 rounded-full text-sm font-semibold">Active</span>
            </div>

            <!-- Authentication Status -->
            <div class="flex items-center justify-between p-6 bg-linear-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl">
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.29 7.78-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900">Authentication</h4>
                  <p class="text-sm text-gray-600">System running</p>
                </div>
              </div>
              <span class="px-4 py-2 bg-green-200 text-green-800 rounded-full text-sm font-semibold">Active</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'

const activeTab = ref('quick-access')
const dashboardData = ref({})
const loading = ref(false)

onMounted(async () => {
  await loadDashboard()
})

const loadDashboard = async () => {
  loading.value = true
  try {
    const response = await adminAPI.getDashboard()
    dashboardData.value = response.data.data
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
}
</script>
