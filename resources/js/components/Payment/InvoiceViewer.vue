<template>
  <div class="invoice-viewer-container">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-96">
      <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Invoice Document -->
    <div v-else-if="invoice" class="bg-white">
      <!-- Header Actions -->
      <div class="flex justify-between items-center mb-6 pb-6 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-800">{{ invoice.invoice_number }}</h1>
        <div class="flex gap-3">
          <button
            @click="downloadPDF"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Download PDF
          </button>
          <button
            @click="sendEmail"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Kirim Email
          </button>
        </div>
      </div>

      <!-- Invoice Status Badge -->
      <div class="mb-6 flex items-center gap-2">
        <span
          class="px-3 py-1 rounded-full text-sm font-semibold"
          :class="{
            'bg-yellow-100 text-yellow-800': invoice.status === 'draft',
            'bg-blue-100 text-blue-800': invoice.status === 'sent',
            'bg-red-100 text-red-800': invoice.status === 'overdue',
            'bg-green-100 text-green-800': invoice.status === 'paid',
            'bg-gray-100 text-gray-800': invoice.status === 'cancelled',
          }"
        >
          {{ getStatusLabel(invoice.status) }}
        </span>
        <span class="text-sm text-gray-600">
          Dibuat: {{ formatDate(invoice.invoice_date) }}
          <span v-if="invoice.due_date"> | Jatuh Tempo: {{ formatDate(invoice.due_date) }}</span>
        </span>
      </div>

      <!-- Invoice Content -->
      <div class="border-t-2 border-b-2 border-gray-300 py-8 mb-8">
        <!-- Company Info (Left) & Customer Info (Right) -->
        <div class="grid grid-cols-2 gap-8 mb-8">
          <div>
            <h3 class="font-bold text-lg text-gray-900 mb-4">Dari:</h3>
            <p class="font-semibold text-gray-800">PT Telemedicine Indonesia</p>
            <p class="text-gray-600">Jl. Merdeka No. 123</p>
            <p class="text-gray-600">Jakarta, Indonesia</p>
            <p class="text-gray-600">Email: billing@telemedicine.id</p>
            <p class="text-gray-600">Phone: +62-21-1234-5678</p>
          </div>

          <div>
            <h3 class="font-bold text-lg text-gray-900 mb-4">Kepada:</h3>
            <p class="font-semibold text-gray-800">{{ invoice.user_name }}</p>
            <p class="text-gray-600">{{ invoice.user_email }}</p>
            <p class="text-gray-600">{{ invoice.user_phone }}</p>
            <p class="text-gray-600">{{ invoice.user_address }}</p>
          </div>
        </div>

        <!-- Invoice Details -->
        <div class="grid grid-cols-2 gap-8 text-sm">
          <div>
            <p class="text-gray-600">No. Invoice</p>
            <p class="font-bold text-gray-900">{{ invoice.invoice_number }}</p>
          </div>
          <div class="text-right">
            <p class="text-gray-600">Tanggal Invoice</p>
            <p class="font-bold text-gray-900">{{ formatDate(invoice.invoice_date) }}</p>
          </div>
          <div>
            <p class="text-gray-600">Tanggal Jatuh Tempo</p>
            <p class="font-bold text-gray-900">{{ formatDate(invoice.due_date) }}</p>
          </div>
          <div class="text-right">
            <p class="text-gray-600">Tanggal Terbayar</p>
            <p class="font-bold text-gray-900">
              {{ invoice.paid_at ? formatDate(invoice.paid_at) : '-' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Line Items Table -->
      <div class="mb-8">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-gray-100 border-b-2 border-gray-300">
              <th class="text-left py-3 px-4 font-semibold text-gray-900">Deskripsi</th>
              <th class="text-center py-3 px-4 font-semibold text-gray-900 w-20">Qty</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-900 w-32">Harga Satuan</th>
              <th class="text-right py-3 px-4 font-semibold text-gray-900 w-32">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in invoice.items" :key="index" class="border-b border-gray-200">
              <td class="py-3 px-4 text-gray-900">{{ item.description }}</td>
              <td class="py-3 px-4 text-center text-gray-900">{{ item.quantity }}</td>
              <td class="py-3 px-4 text-right text-gray-900">{{ formatCurrency(item.unit_price) }}</td>
              <td class="py-3 px-4 text-right text-gray-900 font-semibold">{{ formatCurrency(item.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals Section -->
      <div class="flex justify-end mb-8">
        <div class="w-80 space-y-2">
          <div class="flex justify-between text-gray-700 border-b border-gray-200 pb-2">
            <span>Subtotal:</span>
            <span class="font-semibold">{{ formatCurrency(invoice.subtotal) }}</span>
          </div>

          <div class="flex justify-between text-gray-700 border-b border-gray-200 pb-2">
            <span>Pajak:</span>
            <span class="font-semibold">{{ formatCurrency(invoice.tax_amount) }}</span>
          </div>

          <div v-if="invoice.discount_amount" class="flex justify-between text-gray-700 border-b border-gray-200 pb-2">
            <span>Diskon:</span>
            <span class="font-semibold text-red-600">-{{ formatCurrency(invoice.discount_amount) }}</span>
          </div>

          <div class="flex justify-between text-lg font-bold text-gray-900 bg-gray-100 p-3 rounded">
            <span>Total:</span>
            <span>{{ formatCurrency(invoice.total_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Tax Details (if available) -->
      <div v-if="taxDetails.length" class="mb-8">
        <h3 class="font-bold text-gray-900 mb-4">Rincian Pajak</h3>
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-300">
              <th class="text-left py-2 px-3 font-semibold text-gray-900">Jenis Pajak</th>
              <th class="text-right py-2 px-3 font-semibold text-gray-900">Rate</th>
              <th class="text-right py-2 px-3 font-semibold text-gray-900">Dasar Pengenaan</th>
              <th class="text-right py-2 px-3 font-semibold text-gray-900">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tax in taxDetails" :key="tax.tax_type" class="border-b border-gray-200">
              <td class="py-2 px-3 text-gray-900">{{ tax.tax_type_label }}</td>
              <td class="py-2 px-3 text-right text-gray-900">{{ tax.tax_rate }}%</td>
              <td class="py-2 px-3 text-right text-gray-900">{{ formatCurrency(tax.base_amount) }}</td>
              <td class="py-2 px-3 text-right text-gray-900 font-semibold">{{ formatCurrency(tax.tax_amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Notes -->
      <div v-if="invoice.notes" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
        <h3 class="font-bold text-blue-900 mb-2">Catatan:</h3>
        <p class="text-blue-800">{{ invoice.notes }}</p>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-600">
        <p>Terima kasih atas bisnis Anda!</p>
        <p class="mt-2">Pertanyaan? Hubungi support@telemedicine.id atau (021) 1234-5678</p>
        <p class="mt-4 text-xs text-gray-500">Invoice ini dibuat secara otomatis oleh sistem</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="bg-red-50 border border-red-200 rounded-lg p-6 text-red-800">
      <p class="font-semibold">Gagal memuat invoice</p>
      <p class="mt-2">{{ error }}</p>
      <router-link to="/invoices" class="mt-4 inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
        Kembali ke Daftar Invoice
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency, formatDate } from '@/utils/formatters'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const invoice = ref(null)

const taxDetails = computed(() => {
  return invoice.value?.taxes || []
})

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    sent: 'Terkirim',
    overdue: 'Jatuh Tempo',
    paid: 'Terbayar',
    cancelled: 'Dibatalkan',
  }
  return labels[status] || status
}

const fetchInvoice = async () => {
  try {
    loading.value = true
    const response = await fetch(`/api/v1/invoices/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) {
      throw new Error('Gagal memuat invoice')
    }

    const data = await response.json()
    invoice.value = data.data
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const downloadPDF = async () => {
  try {
    const response = await fetch(`/api/v1/invoices/${route.params.id}/download`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) throw new Error('Download failed')

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${invoice.value.invoice_number}.pdf`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  } catch (error) {
    alert('Gagal download PDF: ' + error.message)
  }
}

const sendEmail = async () => {
  try {
    const response = await fetch(`/api/v1/invoices/${route.params.id}/send`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      }
    })

    if (!response.ok) throw new Error('Gagal mengirim email')

    alert('Invoice telah dikirim ke email Anda')
    await fetchInvoice() // Refresh to update sent_at timestamp
  } catch (error) {
    alert('Gagal mengirim email: ' + error.message)
  }
}

onMounted(fetchInvoice)
</script>

<style scoped>
.invoice-viewer-container {
  max-width: 900px;
  margin: 2rem auto;
  padding: 2rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .invoice-viewer-container {
    padding: 1rem;
  }
}

@media print {
  .flex.justify-between {
    display: flex;
    justify-content: space-between;
  }
  
  button {
    display: none;
  }
}
</style>
