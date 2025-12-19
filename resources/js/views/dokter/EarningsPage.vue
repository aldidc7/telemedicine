<template>
  <div class="min-h-screen bg-linear-to-br from-blue-50 to-indigo-100 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Analitik Penghasilan</h1>
      <p class="text-gray-600">Pantau penghasilan dan statistik konsultasi Anda</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <!-- Total Earnings -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Total Penghasilan</p>
            <p class="text-3xl font-bold text-green-600 mt-2">Rp {{ formatCurrency(totalEarnings) }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- This Month -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Bulan Ini</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">Rp {{ formatCurrency(thisMonthEarnings) }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Completed Consultations -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Konsultasi Selesai</p>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ totalConsultations }}</p>
          </div>
          <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Average Rating -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Rating Rata-rata</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ averageRating.toFixed(1) }} ⭐</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Earnings Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Tren Penghasilan (12 Bulan)</h2>
        <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
          <p class="text-gray-400">Chart akan ditampilkan di sini</p>
        </div>
      </div>

      <!-- Consultation Status -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Status Konsultasi</h2>
        <div class="space-y-4">
          <div>
            <div class="flex justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Selesai</span>
              <span class="text-sm font-bold text-gray-900">{{ completedCount }} ({{ ((completedCount / totalConsultations) * 100).toFixed(0) }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-green-500 h-2 rounded-full" :style="{ width: ((completedCount / totalConsultations) * 100) + '%' }"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Pending</span>
              <span class="text-sm font-bold text-gray-900">{{ pendingCount }} ({{ ((pendingCount / totalConsultations) * 100).toFixed(0) }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-yellow-500 h-2 rounded-full" :style="{ width: ((pendingCount / totalConsultations) * 100) + '%' }"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Dibatalkan</span>
              <span class="text-sm font-bold text-gray-900">{{ cancelledCount }} ({{ ((cancelledCount / totalConsultations) * 100).toFixed(0) }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-red-500 h-2 rounded-full" :style="{ width: ((cancelledCount / totalConsultations) * 100) + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Pembayaran Terakhir</h2>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 font-semibold text-gray-700">Tanggal</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-700">Pasien</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-700">Jumlah</th>
              <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in recentPayments" :key="payment.id" class="border-b border-gray-100 hover:bg-gray-50">
              <td class="py-3 px-4 text-sm text-gray-700">{{ formatDate(payment.created_at) }}</td>
              <td class="py-3 px-4 text-sm text-gray-700">{{ payment.patient_name }}</td>
              <td class="py-3 px-4 text-sm font-semibold text-gray-900">Rp {{ formatCurrency(payment.amount) }}</td>
              <td class="py-3 px-4 text-sm">
                <span :class="getStatusClass(payment.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ payment.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const totalEarnings = ref(0)
const thisMonthEarnings = ref(0)
const totalConsultations = ref(0)
const averageRating = ref(0)
const completedCount = ref(0)
const pendingCount = ref(0)
const cancelledCount = ref(0)
const recentPayments = ref([])

onMounted(() => {
  // Load data from API
  loadAnalytics()
})

const loadAnalytics = async () => {
  try {
    // TODO: Replace with actual API call
    totalEarnings.value = 50000000
    thisMonthEarnings.value = 5000000
    totalConsultations.value = 145
    averageRating.value = 4.8
    completedCount.value = 120
    pendingCount.value = 15
    cancelledCount.value = 10
    
    recentPayments.value = [
      { id: 1, patient_name: 'Budi Santoso', amount: 75000, status: 'Completed', created_at: new Date() },
      { id: 2, patient_name: 'Siti Nurhaliza', amount: 75000, status: 'Completed', created_at: new Date() },
      { id: 3, patient_name: 'Ahmad Zaki', amount: 75000, status: 'Pending', created_at: new Date() },
    ]
  } catch (error) {
    console.error('Error loading analytics:', error)
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const getStatusClass = (status) => {
  const classes = {
    'Completed': 'bg-green-100 text-green-800',
    'Pending': 'bg-yellow-100 text-yellow-800',
    'Failed': 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
