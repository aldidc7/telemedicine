<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-10">
        <div class="flex items-center justify-between gap-3 mb-2">
          <div class="flex items-center gap-3">
            <svg class="w-11 h-11 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h1 class="text-4xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
              Analitik Lanjutan
            </h1>
          </div>
          <div class="flex items-center gap-3">
            <!-- Auto Refresh Toggle -->
            <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-xl border border-gray-200">
              <button
                @click="toggleAutoRefresh"
                :class="[
                  'p-1 rounded-lg transition',
                  autoRefreshEnabled 
                    ? 'bg-green-100 text-green-600' 
                    : 'bg-gray-100 text-gray-600'
                ]"
                title="Toggle auto-refresh"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
              </button>
              <select
                v-model.number="autoRefreshInterval"
                @change="updateRefreshInterval(autoRefreshInterval)"
                :disabled="!autoRefreshEnabled"
                class="bg-transparent border-0 text-sm font-medium outline-none cursor-pointer disabled:opacity-50"
              >
                <option :value="15">15s</option>
                <option :value="30">30s</option>
                <option :value="60">1m</option>
                <option :value="300">5m</option>
              </select>
              <span class="text-xs text-gray-500">{{ formattedLastUpdated }}</span>
            </div>
            
            <!-- Refresh Button -->
            <button
              @click="refreshAnalytics"
              :disabled="updateStatus === 'updating'"
              class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-bold py-2 px-4 rounded-xl transition flex items-center gap-2"
            >
              <i :class="['fas', updateStatus === 'updating' ? 'fa-spinner animate-spin' : 'fa-arrow-rotate-right']"></i>
              Perbarui
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <p class="text-gray-600">Wawasan real-time dan metrik kinerja</p>
          <!-- Status Indicator -->
          <div v-if="updateStatus === 'error'" class="text-sm text-red-600 font-medium flex items-center gap-2">
            <i class="fas fa-triangle-exclamation"></i>
            Pembaruan terakhir gagal - mencoba ulang...
          </div>
          <div v-else-if="autoRefreshEnabled" class="flex items-center gap-2 text-sm text-gray-500">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            Pembaruan otomatis aktif
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-flex flex-col items-center gap-4">
          <div class="animate-spin">
            <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
          </div>
          <p class="text-gray-600 font-semibold">Memuat analitik...</p>
        </div>
      </div>

      <div v-else class="space-y-10">
        <!-- Consultation Metrics KPI -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Metrik Konsultasi
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <KpiCard
              label="Total Konsultasi"
              :value="consultationMetrics.total"
              icon="fa-circle-dot"
              color="blue"
            />
            <KpiCard
              label="Sesi Aktif"
              :value="consultationMetrics.active"
              icon="fa-circle-check"
              color="green"
            />
            <KpiCard
              label="Selesai"
              :value="consultationMetrics.completed"
              icon="fa-check"
              color="emerald"
            />
            <KpiCard
              label="Tingkat Penyelesaian"
              :value="`${(consultationMetrics.completion_rate || 0).toFixed(1)}%`"
              icon="fa-chart-line"
              color="purple"
            />
          </div>
        </section>

        <!-- Doctor Performance -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h-3.5A3.5 3.5 0 0 0 7 13.5v.5a3.5 3.5 0 0 0 3.5 3.5h3.5m0-7h3.5A3.5 3.5 0 0 1 21 13.5v.5a3.5 3.5 0 0 1-3.5 3.5h-3.5m0-7v7m0-7H7m0 7h10.5" />
            </svg>
            Peringkat Kinerja Dokter
          </h2>
          <DoctorPerformanceTable :doctors="doctorPerformance" />
        </section>

        <!-- Health Trends -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Tren Kesehatan
          </h2>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Health Issues -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
              <h3 class="text-xl font-bold text-gray-900 mb-6">Masalah Kesehatan Utama</h3>
              <div class="space-y-3">
                <div v-for="(count, issue) in topHealthIssues" :key="issue" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                  <span class="font-medium text-gray-700">{{ issue }}</span>
                  <span class="font-bold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full text-sm">{{ count }}</span>
                </div>
              </div>
            </div>

            <!-- Patient Demographics -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
              <h3 class="text-xl font-bold text-gray-900 mb-6">Demografi Pasien</h3>
              <div class="space-y-4">
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                  <p class="text-sm text-gray-600">Total Pasien</p>
                  <p class="text-3xl font-bold text-gray-900">{{ healthTrends.total_patients }}</p>
                </div>
                <div class="border-l-4 border-green-500 pl-4 py-2">
                  <p class="text-sm text-gray-600">Baru Bulan Ini</p>
                  <p class="text-3xl font-bold text-gray-900">{{ healthTrends.new_patients_this_month }}</p>
                </div>
                <div class="border-l-4 border-purple-500 pl-4 py-2">
                  <p class="text-sm text-gray-600">Tingkat Retensi</p>
                  <p class="text-3xl font-bold text-gray-900">{{ (healthTrends.retention_rate || 0).toFixed(1) }}%</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Revenue Analytics -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Analitik Pendapatan
          </h2>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <KpiCard
              label="Total Pendapatan"
              :value="`Rp${((revenueData.total_revenue || 0) / 1000).toFixed(1)}k`"
              icon="fa-money-bill"
              color="green"
            />
            <KpiCard
              label="Pendapatan Dibayar"
              :value="`Rp${((revenueData.paid_revenue || 0) / 1000).toFixed(1)}k`"
              icon="fa-check"
              color="emerald"
            />
            <KpiCard
              label="Tingkat Pembayaran"
              :value="`${(revenueData.payment_completion_rate || 0).toFixed(1)}%`"
              icon="fa-chart-pie"
              color="blue"
            />
          </div>

          <!-- Revenue by Doctor -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Dokter dengan Pendapatan Tertinggi</h3>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Nama Dokter</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Konsultasi</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Total Pendapatan</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Rata-rata/Konsultasi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="doctor in revenueByDoctor" :key="doctor.doctor_id" class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ doctor.doctor_name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ doctor.consultations }}</td>
                    <td class="px-6 py-4 font-bold text-green-600">Rp{{ (doctor.total_revenue || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.') }}</td>
                    <td class="px-6 py-4 text-gray-600">Rp{{ (doctor.avg_per_consultation || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Date Range Analytics -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Analitik Rentang Tanggal Kustom
          </h2>
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai</label>
                <input 
                  v-model="dateRangeStart" 
                  type="date" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Akhir</label>
                <input 
                  v-model="dateRangeEnd" 
                  type="date" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
              </div>
              <div class="flex items-end">
                <button 
                  @click="fetchDateRangeAnalytics"
                  class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition"
                >
                  Muat Analitik
                </button>
              </div>
            </div>
            <div v-if="dateRangeData" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="dateRangeData.consultations" class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                  <h4 class="font-bold text-blue-900 mb-3">Konsultasi</h4>
                  <div class="space-y-2 text-sm text-blue-800">
                    <p v-for="day in dateRangeData.consultations" :key="day.date">
                      <span class="font-semibold">{{ day.date }}:</span> {{ day.total }} ({{ day.completed }} selesai)
                    </p>
                  </div>
                </div>
                <div v-if="dateRangeData.revenue" class="bg-green-50 p-4 rounded-lg border border-green-200">
                  <h4 class="font-bold text-green-900 mb-3">Pendapatan</h4>
                  <div class="space-y-2 text-sm text-green-800">
                    <p v-for="day in dateRangeData.revenue" :key="day.date">
                      <span class="font-semibold">{{ day.date }}:</span> Rp{{ (day.total || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.') }} ({{ day.count }} konsultasi)
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import KpiCard from '@/components/analytics/KpiCard.vue'
import DoctorPerformanceTable from '@/components/analytics/DoctorPerformanceTable.vue'
import client from '@/api/client'

const authStore = useAuthStore()
const loading = ref(true)
const consultationMetrics = ref({
  total: 0,
  active: 0,
  completed: 0,
  completion_rate: 0
})
const doctorPerformance = ref([])
const healthTrends = ref({})
const revenueData = ref({})
const revenueByDoctor = ref([])
const dateRangeStart = ref('')
const dateRangeEnd = ref('')
const dateRangeData = ref(null)
const topHealthIssues = ref({})

// Component lifecycle tracking
const isMounted = ref(false)

// Request cancellation
let abortController = null

// Real-time update settings
const autoRefreshEnabled = ref(true)
const autoRefreshInterval = ref(30) // seconds
const lastUpdated = ref(new Date())
const updateStatus = ref('idle') // idle, updating, error
let autoRefreshTimer = null
let dataCheckInterval = null

const formattedLastUpdated = computed(() => {
  const now = new Date()
  const diff = Math.floor((now - lastUpdated.value) / 1000)
  
  if (diff < 60) return `${diff}s ago`
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  return `${Math.floor(diff / 3600)}h ago`
})

onMounted(() => {
  isMounted.value = true
  abortController = new AbortController()
  fetchAnalytics()
  initializeAutoRefresh()
})

onUnmounted(() => {
  isMounted.value = false
  if (abortController) abortController.abort()
  stopAutoRefresh()
})

const fetchAnalytics = async () => {
  try {
    if (!isMounted.value) return
    
    // Cancel previous request if still pending
    if (abortController) abortController.abort()
    abortController = new AbortController()
    
    updateStatus.value = 'updating'
    const response = await client.get('/analytics/overview', {
      signal: abortController.signal
    })
    
    if (!isMounted.value) return
    
    const data = response.data?.data || response.data || {}
    
    if (!isMounted.value) return
    
    // Safely assign with defaults
    consultationMetrics.value = {
      ...consultationMetrics.value,
      ...(data.consultation_metrics || {})
    }
    doctorPerformance.value = data.doctor_performance || []
    healthTrends.value = data.health_trends || {}
    revenueData.value = data.revenue || {}
    revenueByDoctor.value = data.revenue?.revenue_by_doctor || []
    topHealthIssues.value = data.health_trends?.top_health_issues || {}
    
    if (isMounted.value) {
      lastUpdated.value = new Date()
      updateStatus.value = 'idle'
    }
  } catch (error) {
    // Ignore abort errors (expected when component unmounts)
    if (error.name === 'AbortError') return
    
    if (!isMounted.value) return
    
    console.error('Failed to fetch analytics:', error)
    updateStatus.value = 'error'
    
    setTimeout(() => { 
      if (isMounted.value) {
        updateStatus.value = 'idle'
      }
    }, 3000)
  } finally {
    if (isMounted.value) {
      loading.value = false
    }
  }
}

const refreshAnalytics = async () => {
  try {
    await fetchAnalytics()
  } catch (error) {
    console.error('Failed to refresh analytics:', error)
  }
}

const initializeAutoRefresh = () => {
  // Primary auto-refresh timer
  if (autoRefreshTimer) clearInterval(autoRefreshTimer)
  
  autoRefreshTimer = setInterval(() => {
    // Check if component is still mounted before fetching
    if (isMounted.value && autoRefreshEnabled.value) {
      fetchAnalytics()
    }
  }, autoRefreshInterval.value * 1000)
}

const stopAutoRefresh = () => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer)
    autoRefreshTimer = null
  }
  if (dataCheckInterval) {
    clearInterval(dataCheckInterval)
    dataCheckInterval = null
  }
}

const toggleAutoRefresh = () => {
  autoRefreshEnabled.value = !autoRefreshEnabled.value
  if (autoRefreshEnabled.value) {
    initializeAutoRefresh()
  } else {
    stopAutoRefresh()
  }
}

const updateRefreshInterval = (newInterval) => {
  autoRefreshInterval.value = newInterval
  initializeAutoRefresh()
}

const fetchDateRangeAnalytics = async () => {
  if (!dateRangeStart.value || !dateRangeEnd.value) return
  
  try {
    if (!isMounted.value) return
    
    const response = await client.get('/analytics/range', {
      signal: abortController?.signal,
      params: {
        start_date: dateRangeStart.value,
        end_date: dateRangeEnd.value,
        metrics: 'consultations,revenue'
      }
    })
    
    if (!isMounted.value) return
    
    dateRangeData.value = response.data.data
  } catch (error) {
    if (error.name === 'AbortError') return
    if (!isMounted.value) return
    console.error('Failed to fetch date range analytics:', error)
  }
}
</script>
