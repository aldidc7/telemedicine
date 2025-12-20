<template>
  <div class="invoice-page">
    <div class="page-header mb-8">
      <h1 class="text-4xl font-bold text-gray-900">Invoice Anda</h1>
      <p class="text-gray-600 mt-2">Kelola dan lihat semua invoice dari pembayaran Anda</p>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
      <!-- Main Content -->
      <div class="lg:col-span-3">
        <router-view />
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Quick Links -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
          <h3 class="font-bold text-gray-900 mb-4">Akses Cepat</h3>
          <div class="space-y-2">
            <router-link
              to="/invoices"
              class="block px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg transition text-sm font-medium"
            >
              Semua Invoice
            </router-link>
            <router-link
              to="/payment-history"
              class="block px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium"
            >
              Riwayat Pembayaran
            </router-link>
            <router-link
              to="/payment"
              class="block px-4 py-2 bg-green-100 hover:bg-green-200 text-green-800 rounded-lg transition text-sm font-medium"
            >
              Buat Pembayaran
            </router-link>
          </div>
        </div>

        <!-- Invoice Stats -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
          <h3 class="font-bold text-gray-900 mb-4">Statistik</h3>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Total Invoice</span>
              <span class="font-semibold text-gray-900">{{ stats.total }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Terbayar</span>
              <span class="font-semibold text-green-600">{{ stats.paid }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Pending</span>
              <span class="font-semibold text-yellow-600">{{ stats.pending }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Jatuh Tempo</span>
              <span class="font-semibold text-red-600">{{ stats.overdue }}</span>
            </div>
          </div>
        </div>

        <!-- Invoice Info -->
        <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
          <h3 class="font-bold text-blue-900 mb-3">Informasi Invoice</h3>
          <ul class="space-y-2 text-sm text-blue-800">
            <li class="flex items-start gap-2">
              <span>📄</span>
              <span>Setiap pembayaran otomatis menghasilkan invoice</span>
            </li>
            <li class="flex items-start gap-2">
              <span>📧</span>
              <span>Invoice dikirim ke email Anda secara otomatis</span>
            </li>
            <li class="flex items-start gap-2">
              <span>🔒</span>
              <span>Semua data terenkripsi dan aman</span>
            </li>
            <li class="flex items-start gap-2">
              <span>📥</span>
              <span>Download PDF kapan saja untuk laporan pribadi</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const invoices = ref([])
const loading = ref(true)

const stats = computed(() => {
  return {
    total: invoices.value.length,
    paid: invoices.value.filter(i => i.status === 'paid').length,
    pending: invoices.value.filter(i => i.status === 'sent').length,
    overdue: invoices.value.filter(i => i.status === 'overdue').length,
  }
})

const fetchInvoices = async () => {
  try {
    const response = await fetch('/api/v1/invoices', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) throw new Error('Gagal memuat invoice')

    const data = await response.json()
    invoices.value = data.data || []
  } catch (error) {
    console.error('Error fetching invoices:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchInvoices)
</script>

<style scoped>
.invoice-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

@media (max-width: 768px) {
  .invoice-page {
    padding: 1rem;
  }
}
</style>
