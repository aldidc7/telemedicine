<template>
  <div class="success-page">
    <div class="success-container">
      <!-- Success Icon -->
      <div class="mb-8">
        <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center animate-pulse">
          <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
        </div>
      </div>

      <!-- Success Message -->
      <h1 class="text-4xl font-bold text-gray-900 text-center mb-2">
        Pembayaran Berhasil!
      </h1>
      <p class="text-lg text-gray-600 text-center mb-8">
        Terima kasih telah melakukan pembayaran. Invoice telah dikirim ke email Anda.
      </p>

      <!-- Payment Details -->
      <div v-if="payment" class="bg-white rounded-lg border border-gray-200 p-8 mb-8">
        <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-200">
          <div>
            <p class="text-sm text-gray-600 mb-1">Nomor Pembayaran</p>
            <p class="text-xl font-bold text-gray-900">{{ payment.transaction_id }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600 mb-1">Tanggal</p>
            <p class="text-xl font-bold text-gray-900">{{ formatDate(payment.created_at) }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm text-gray-600 mb-1">Jumlah Pembayaran</p>
            <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</p>
            <p class="text-xs text-gray-500 mt-1">+ {{ formatCurrency(getTaxAmount(payment.amount)) }} pajak</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600 mb-1">Metode Pembayaran</p>
            <p class="text-xl font-semibold text-gray-900">{{ getMethodLabel(payment.payment_method) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ payment.payment_method }}</p>
          </div>
        </div>
      </div>

      <!-- Next Steps -->
      <div class="bg-blue-50 rounded-lg border border-blue-200 p-6 mb-8">
        <h3 class="font-bold text-blue-900 mb-4">Langkah Selanjutnya</h3>
        <ol class="space-y-3 text-blue-800">
          <li class="flex items-start gap-3">
            <span class="shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
            <span>Cek email Anda untuk invoice dan bukti pembayaran</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
            <span>Jika konsultasi dengan dokter, jadwal akan ditentukan dalam 24 jam</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
            <span>Anda akan menerima notifikasi tentang jadwal konsultasi</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
            <span>Bergabunglah dengan video call tepat waktu untuk konsultasi</span>
          </li>
        </ol>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4 justify-center mb-8">
        <router-link
          v-if="payment?.invoice_id"
          :to="`/invoices/${payment.invoice_id}`"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition"
        >
          Lihat Invoice
        </router-link>

        <router-link
          to="/payment-history"
          class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition"
        >
          Riwayat Pembayaran
        </router-link>

        <router-link
          to="/consultations"
          class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition"
        >
          Konsultasi Saya
        </router-link>
      </div>

      <!-- Contact Support -->
      <div class="text-center">
        <p class="text-gray-600 mb-2">
          Ada pertanyaan? 
          <a href="mailto:support@telemedicine.id" class="text-blue-600 hover:underline font-medium">
            Hubungi Support
          </a>
        </p>
        <p class="text-gray-600">
          Atau telepon: <a href="tel:+6212123456" class="text-blue-600 hover:underline font-medium">
            (021) 2123-456
          </a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePaymentStore } from '@/stores/paymentStore'
import { formatCurrency, formatDate } from '@/utils/formatters'

const router = useRouter()
const paymentStore = usePaymentStore()

const payment = ref(null)

const getTaxAmount = (amount) => {
  return Math.round(amount * 0.26)
}

const getMethodLabel = (method) => {
  const labels = {
    stripe: 'Stripe (Kartu Kredit)',
    gcash: 'GCash',
    bank_transfer: 'Transfer Bank',
    e_wallet: 'E-Wallet',
  }
  return labels[method] || method
}

onMounted(() => {
  // Get payment from store (set during payment creation)
  payment.value = paymentStore.lastPayment

  // If no payment in store, try to load from route params
  if (!payment.value && router.currentRoute.value.query.payment_id) {
    // Could fetch from API if needed
  }

  // Auto-redirect to home after 10 seconds if needed
  // setTimeout(() => {
  //   router.push('/dashboard')
  // }, 10000)
})
</script>

<style scoped>
.success-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.success-container {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  max-width: 600px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .success-page {
    padding: 1rem;
  }

  .success-container {
    padding: 1.5rem;
  }
}
</style>
