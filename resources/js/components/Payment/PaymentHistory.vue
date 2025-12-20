<template>
  <div class="payment-history-container">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Riwayat Pembayaran</h1>
      <p class="text-gray-600">Lihat dan kelola semua pembayaran Anda</p>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg p-6 mb-6 shadow-sm border border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="No invoice, tanggal..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <!-- Payment Method Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Metode</label>
          <select
            v-model="filters.payment_method"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Metode</option>
            <option value="stripe">Stripe</option>
            <option value="gcash">GCash</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="e_wallet">E-Wallet</option>
          </select>
        </div>

        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
          <select
            v-model="filters.dateRange"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Periode</option>
            <option value="7">7 Hari Terakhir</option>
            <option value="30">30 Hari Terakhir</option>
            <option value="90">90 Hari Terakhir</option>
            <option value="365">1 Tahun Terakhir</option>
          </select>
        </div>
      </div>

      <!-- Apply Filters -->
      <div class="mt-4 flex gap-2">
        <button
          @click="applyFilters"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium"
        >
          Terapkan Filter
        </button>
        <button
          @click="resetFilters"
          class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <p class="text-sm text-gray-600 mb-1">Total Pembayaran</p>
        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalPaid) }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ stats.totalTransactions }} transaksi</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <p class="text-sm text-gray-600 mb-1">Pending</p>
        <p class="text-2xl font-bold text-yellow-600">{{ formatCurrency(stats.totalPending) }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ stats.pendingCount }} pembayaran</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <p class="text-sm text-gray-600 mb-1">Processing</p>
        <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats.totalProcessing) }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ stats.processingCount }} pembayaran</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <p class="text-sm text-gray-600 mb-1">Failed/Refunded</p>
        <p class="text-2xl font-bold text-red-600">{{ formatCurrency(stats.totalFailed) }}</p>
        <p class="text-xs text-gray-500 mt-2">{{ stats.failedCount }} pembayaran</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-96">
      <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Payment Table -->
    <div v-else-if="payments.length" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold text-gray-900">Tanggal</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-900">Invoice</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-900">Jumlah</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-900">Metode</th>
            <th class="px-6 py-3 text-center font-semibold text-gray-900">Status</th>
            <th class="px-6 py-3 text-center font-semibold text-gray-900">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 text-gray-900">
              <div>
                <p class="font-medium">{{ formatDate(payment.created_at) }}</p>
                <p class="text-xs text-gray-500">{{ formatTime(payment.created_at) }}</p>
              </div>
            </td>

            <td class="px-6 py-4 text-gray-900">
              <router-link
                v-if="payment.invoice"
                :to="`/invoices/${payment.invoice.id}`"
                class="text-blue-600 hover:underline font-medium"
              >
                {{ payment.invoice.invoice_number }}
              </router-link>
              <span v-else class="text-gray-500">-</span>
            </td>

            <td class="px-6 py-4 text-gray-900">
              <div>
                <p class="font-semibold">{{ formatCurrency(payment.amount) }}</p>
                <p class="text-xs text-gray-500">+ {{ formatCurrency(getTaxAmount(payment)) }} pajak</p>
              </div>
            </td>

            <td class="px-6 py-4 text-gray-900">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                :class="getMethodBadgeClass(payment.payment_method)"
              >
                {{ getMethodLabel(payment.payment_method) }}
              </span>
            </td>

            <td class="px-6 py-4 text-center">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                :class="getStatusBadgeClass(payment.status)"
              >
                {{ getStatusLabel(payment.status) }}
              </span>
            </td>

            <td class="px-6 py-4 text-center">
              <div class="flex justify-center gap-2">
                <router-link
                  v-if="payment.invoice"
                  :to="`/invoices/${payment.invoice.id}`"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  title="Lihat Invoice"
                >
                  Lihat
                </router-link>

                <button
                  v-if="payment.status === 'pending'"
                  @click="openRefundDialog(payment)"
                  class="text-amber-600 hover:text-amber-800 text-sm font-medium"
                  title="Batalkan Pembayaran"
                >
                  Batalkan
                </button>

                <button
                  v-if="['completed', 'failed'].includes(payment.status)"
                  @click="openRefundDialog(payment)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                  title="Minta Refund"
                >
                  Refund
                </button>

                <button
                  v-if="payment.failed_reason"
                  @click="showErrorDetails(payment)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                  title="Lihat Detail Error"
                >
                  Error
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
        <p class="text-sm text-gray-600">
          Menampilkan {{ payments.length }} dari {{ total }} pembayaran
        </p>
        <div class="flex gap-2">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50"
          >
            Sebelumnya
          </button>
          <span class="px-3 py-1 text-sm text-gray-600">
            Halaman {{ currentPage }} dari {{ totalPages }}
          </span>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50"
          >
            Berikutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg p-12 text-center border border-gray-200">
      <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Pembayaran</h3>
      <p class="text-gray-600 mb-6">Anda belum membuat pembayaran apapun</p>
      <router-link
        to="/payment"
        class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium"
      >
        Buat Pembayaran
      </router-link>
    </div>

    <!-- Refund Dialog -->
    <div v-if="showRefundDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">
          {{ selectedPayment?.status === 'completed' ? 'Minta Refund' : 'Batalkan Pembayaran' }}
        </h3>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Jumlah Refund (IDR)
          </label>
          <input
            v-model.number="refundAmount"
            type="number"
            :max="selectedPayment?.amount"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
          <p class="text-xs text-gray-500 mt-1">
            Maksimal: {{ formatCurrency(selectedPayment?.amount) }}
          </p>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Alasan
          </label>
          <textarea
            v-model="refundReason"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 h-24"
            placeholder="Jelaskan alasan refund..."
          ></textarea>
        </div>

        <div class="flex gap-2">
          <button
            @click="cancelRefund"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
          >
            Batal
          </button>
          <button
            @click="submitRefund"
            :disabled="refundLoading"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white rounded-lg font-medium"
          >
            {{ refundLoading ? 'Memproses...' : 'Konfirmasi' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Error Details Dialog -->
    <div v-if="showErrorDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-red-600 mb-4">Detail Error Pembayaran</h3>
        <p class="text-gray-900 mb-6 whitespace-pre-wrap">{{ selectedPayment?.failed_reason }}</p>
        <button
          @click="showErrorDialog = false"
          class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { formatCurrency, formatDate, formatTime } from '@/utils/formatters'

const router = useRouter()

const loading = ref(false)
const payments = ref([])
const total = ref(0)
const currentPage = ref(1)

const filters = ref({
  search: '',
  status: '',
  payment_method: '',
  dateRange: '',
})

const showRefundDialog = ref(false)
const showErrorDialog = ref(false)
const selectedPayment = ref(null)
const refundAmount = ref(0)
const refundReason = ref('')
const refundLoading = ref(false)

const stats = computed(() => {
  return {
    totalPaid: payments.value
      .filter(p => p.status === 'completed')
      .reduce((sum, p) => sum + p.amount, 0),
    totalPending: payments.value
      .filter(p => p.status === 'pending')
      .reduce((sum, p) => sum + p.amount, 0),
    totalProcessing: payments.value
      .filter(p => p.status === 'processing')
      .reduce((sum, p) => sum + p.amount, 0),
    totalFailed: payments.value
      .filter(p => ['failed', 'refunded'].includes(p.status))
      .reduce((sum, p) => sum + p.amount, 0),
    totalTransactions: payments.value.length,
    pendingCount: payments.value.filter(p => p.status === 'pending').length,
    processingCount: payments.value.filter(p => p.status === 'processing').length,
    failedCount: payments.value.filter(p => ['failed', 'refunded'].includes(p.status)).length,
  }
})

const totalPages = computed(() => Math.ceil(total.value / 15))

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Selesai',
    failed: 'Gagal',
    refunded: 'Refunded',
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-orange-100 text-orange-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getMethodLabel = (method) => {
  const labels = {
    stripe: 'Stripe (Kartu)',
    gcash: 'GCash',
    bank_transfer: 'Transfer Bank',
    e_wallet: 'E-Wallet',
  }
  return labels[method] || method
}

const getMethodBadgeClass = (method) => {
  const classes = {
    stripe: 'bg-purple-100 text-purple-800',
    gcash: 'bg-blue-100 text-blue-800',
    bank_transfer: 'bg-green-100 text-green-800',
    e_wallet: 'bg-orange-100 text-orange-800',
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

const getTaxAmount = (payment) => {
  return Math.round(payment.amount * 0.26)
}

const fetchPayments = async () => {
  try {
    loading.value = true
    const queryParams = new URLSearchParams({
      page: currentPage.value,
      limit: 15,
    })

    if (filters.value.search) {
      queryParams.append('search', filters.value.search)
    }
    if (filters.value.status) {
      queryParams.append('status', filters.value.status)
    }
    if (filters.value.payment_method) {
      queryParams.append('payment_method', filters.value.payment_method)
    }
    if (filters.value.dateRange) {
      queryParams.append('date_range', filters.value.dateRange)
    }

    const response = await fetch(`/api/v1/payments/history?${queryParams}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) throw new Error('Gagal memuat pembayaran')

    const data = await response.json()
    payments.value = data.data || []
    total.value = data.meta?.total || 0
  } catch (error) {
    console.error('Error fetching payments:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchPayments()
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    payment_method: '',
    dateRange: '',
  }
  currentPage.value = 1
  fetchPayments()
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchPayments()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    fetchPayments()
  }
}

const openRefundDialog = (payment) => {
  selectedPayment.value = payment
  refundAmount.value = payment.amount
  refundReason.value = ''
  showRefundDialog.value = true
}

const cancelRefund = () => {
  showRefundDialog.value = false
  selectedPayment.value = null
  refundAmount.value = 0
  refundReason.value = ''
}

const submitRefund = async () => {
  try {
    refundLoading.value = true

    const response = await fetch(`/api/v1/payments/${selectedPayment.value.id}/refund`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        amount: refundAmount.value,
        reason: refundReason.value,
      })
    })

    if (!response.ok) throw new Error('Gagal memproses refund')

    alert('Refund berhasil diajukan. Anda akan menerima notifikasi setelah diproses.')
    showRefundDialog.value = false
    await fetchPayments()
  } catch (error) {
    alert('Error: ' + error.message)
  } finally {
    refundLoading.value = false
  }
}

const showErrorDetails = (payment) => {
  selectedPayment.value = payment
  showErrorDialog.value = true
}

onMounted(fetchPayments)
</script>

<style scoped>
.payment-history-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

@media (max-width: 768px) {
  .payment-history-container {
    padding: 1rem;
  }
}
</style>
