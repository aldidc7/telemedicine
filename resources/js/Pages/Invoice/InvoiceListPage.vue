<template>
  <div class="invoice-list-page">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Daftar Invoice</h1>
      <p class="text-gray-600 mt-2">Kelola semua invoice pembayaran Anda</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 mb-6 shadow-sm border border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Cari Invoice</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="No invoice, tanggal..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="sent">Terkirim</option>
            <option value="paid">Terbayar</option>
            <option value="overdue">Jatuh Tempo</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button
            @click="applyFilters"
            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium"
          >
            Terapkan
          </button>
          <button
            @click="resetFilters"
            class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-96">
      <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Invoice List -->
    <div v-else-if="invoices.length" class="space-y-4">
      <div
        v-for="invoice in invoices"
        :key="invoice.id"
        class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition"
      >
        <div class="flex items-start justify-between mb-4">
          <div>
            <router-link
              :to="`/invoices/${invoice.id}`"
              class="text-xl font-bold text-blue-600 hover:underline"
            >
              {{ invoice.invoice_number }}
            </router-link>
            <p class="text-sm text-gray-600">
              {{ formatDate(invoice.invoice_date) }}
            </p>
          </div>
          <span
            class="px-4 py-2 rounded-full text-sm font-semibold"
            :class="getStatusBadgeClass(invoice.status)"
          >
            {{ getStatusLabel(invoice.status) }}
          </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-200">
          <div>
            <p class="text-xs text-gray-600 mb-1">Jumlah</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(invoice.total_amount) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-600 mb-1">Jatuh Tempo</p>
            <p class="text-sm font-medium text-gray-900">{{ formatDate(invoice.due_date) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-600 mb-1">Pajak</p>
            <p class="text-sm font-medium text-gray-900">{{ formatCurrency(invoice.tax_amount) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-600 mb-1">Terbayar Pada</p>
            <p class="text-sm font-medium text-gray-900">
              {{ invoice.paid_at ? formatDate(invoice.paid_at) : '-' }}
            </p>
          </div>
        </div>

        <div class="flex gap-2">
          <router-link
            :to="`/invoices/${invoice.id}`"
            class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg text-sm font-medium transition"
          >
            Lihat Detail
          </router-link>
          <button
            @click="downloadPDF(invoice)"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg text-sm font-medium transition"
          >
            📥 Download PDF
          </button>
          <button
            @click="sendEmail(invoice)"
            class="px-4 py-2 bg-green-100 hover:bg-green-200 text-green-800 rounded-lg text-sm font-medium transition"
          >
            📧 Kirim Email
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-6">
        <button
          @click="previousPage"
          :disabled="currentPage === 1"
          class="px-3 py-1 border border-gray-300 rounded disabled:opacity-50"
        >
          Sebelumnya
        </button>
        <span class="px-3 py-1">Halaman {{ currentPage }} dari {{ totalPages }}</span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border border-gray-300 rounded disabled:opacity-50"
        >
          Berikutnya
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg p-12 text-center border border-gray-200">
      <p class="text-gray-600 mb-4">Belum ada invoice</p>
      <router-link
        to="/payment"
        class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium"
      >
        Buat Pembayaran
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { formatCurrency, formatDate } from '@/utils/formatters'

const loading = ref(false)
const invoices = ref([])
const currentPage = ref(1)
const total = ref(0)

const filters = ref({
  search: '',
  status: '',
})

const totalPages = computed(() => Math.ceil(total.value / 15))

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    sent: 'Terkirim',
    paid: 'Terbayar',
    overdue: 'Jatuh Tempo',
    cancelled: 'Dibatalkan',
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchInvoices = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams({
      page: currentPage.value,
      limit: 15,
    })

    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.status) params.append('status', filters.value.status)

    const response = await fetch(`/api/v1/invoices?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) throw new Error('Gagal memuat invoice')

    const data = await response.json()
    invoices.value = data.data || []
    total.value = data.meta?.total || 0
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchInvoices()
}

const resetFilters = () => {
  filters.value = { search: '', status: '' }
  currentPage.value = 1
  fetchInvoices()
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchInvoices()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    fetchInvoices()
  }
}

const downloadPDF = async (invoice) => {
  try {
    const response = await fetch(`/api/v1/invoices/${invoice.id}/download`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) throw new Error('Download failed')

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${invoice.invoice_number}.pdf`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  } catch (error) {
    alert('Gagal download PDF: ' + error.message)
  }
}

const sendEmail = async (invoice) => {
  try {
    const response = await fetch(`/api/v1/invoices/${invoice.id}/send`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      }
    })

    if (!response.ok) throw new Error('Gagal mengirim')

    alert('Invoice berhasil dikirim')
    await fetchInvoices()
  } catch (error) {
    alert('Error: ' + error.message)
  }
}

onMounted(fetchInvoices)
</script>

<style scoped>
.invoice-list-page {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

@media (max-width: 768px) {
  .invoice-list-page {
    padding: 1rem;
  }
}
</style>
