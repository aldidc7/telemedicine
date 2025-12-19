<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Riwayat Pembayaran</h1>
      <p class="text-gray-600">Kelola dan lihat riwayat semua pembayaran Anda</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 text-sm font-medium">Total Dibayarkan</p>
        <p class="text-3xl font-bold text-green-600 mt-2">Rp {{ formatCurrency(totalPaid) }}</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 text-sm font-medium">Belum Dibayarkan</p>
        <p class="text-3xl font-bold text-orange-600 mt-2">Rp {{ formatCurrency(pendingAmount) }}</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600 text-sm font-medium">Total Konsultasi</p>
        <p class="text-3xl font-bold text-blue-600 mt-2">{{ totalConsultations }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            <option value="">Semua Status</option>
            <option value="completed">Berhasil</option>
            <option value="pending">Menunggu</option>
            <option value="failed">Gagal</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
          <input v-model="fromDate" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Ke Tanggal</label>
          <input v-model="toDate" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>
      </div>
    </div>

    <!-- Payment List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No. Pembayaran</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Dokter</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Tanggal</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Jumlah</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Metode</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ payment.payment_number }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ payment.doctor_name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(payment.created_at) }}</td>
              <td class="px-6 py-4 text-sm font-semibold text-gray-900">Rp {{ formatCurrency(payment.amount) }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ payment.payment_method }}</td>
              <td class="px-6 py-4 text-sm">
                <span :class="getStatusBadge(payment.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ translateStatus(payment.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <button class="text-blue-600 hover:text-blue-800 font-medium">Detail</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty state -->
      <div v-if="payments.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-4 text-gray-600">Belum ada riwayat pembayaran</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="payments.length > 0" class="mt-6 flex items-center justify-between">
      <p class="text-sm text-gray-600">Menampilkan {{ ((currentPage - 1) * perPage) + 1 }} hingga {{ Math.min(currentPage * perPage, totalPayments) }} dari {{ totalPayments }} pembayaran</p>
      <div class="flex gap-2">
        <button :disabled="currentPage === 1" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">Sebelumnya</button>
        <button :disabled="currentPage === totalPages" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">Berikutnya</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const selectedStatus = ref('')
const fromDate = ref('')
const toDate = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalPayments = ref(0)
const totalPages = ref(1)

const totalPaid = ref(0)
const pendingAmount = ref(0)
const totalConsultations = ref(0)

const payments = ref([])

onMounted(() => {
  loadPayments()
})

const loadPayments = async () => {
  try {
    // TODO: Replace with actual API call
    totalPaid.value = 750000
    pendingAmount.value = 150000
    totalConsultations.value = 12
    
    payments.value = [
      {
        id: 1,
        payment_number: 'PAY-2025-001',
        doctor_name: 'Dr. Ahmad Zaki',
        amount: 75000,
        payment_method: 'Transfer Bank',
        status: 'completed',
        created_at: new Date('2025-12-15'),
      },
      {
        id: 2,
        payment_number: 'PAY-2025-002',
        doctor_name: 'Dr. Siti Nurhaliza',
        amount: 75000,
        payment_method: 'E-Wallet',
        status: 'completed',
        created_at: new Date('2025-12-10'),
      },
      {
        id: 3,
        payment_number: 'PAY-2025-003',
        doctor_name: 'Dr. Budi Santoso',
        amount: 75000,
        payment_method: 'Transfer Bank',
        status: 'pending',
        created_at: new Date('2025-12-05'),
      },
    ]
    totalPayments.value = payments.value.length
    totalPages.value = Math.ceil(totalPayments.value / perPage.value)
  } catch (error) {
    console.error('Error loading payments:', error)
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStatusBadge = (status) => {
  const badges = {
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}

const translateStatus = (status) => {
  const translations = {
    'completed': 'Berhasil',
    'pending': 'Menunggu',
    'failed': 'Gagal',
  }
  return translations[status] || status
}
</script>
