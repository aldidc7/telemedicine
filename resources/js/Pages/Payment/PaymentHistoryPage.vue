<template>
  <div class="payment-history-page">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Riwayat Pembayaran</h1>
      <p class="text-gray-600 mt-2">Lihat dan kelola semua pembayaran Anda</p>
    </div>

    <!-- Payment History Component -->
    <PaymentHistory />
  </div>
</template>

<script setup>
import PaymentHistory from '@/components/Payment/PaymentHistory.vue'
</script>

<style scoped>
.payment-history-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

@media (max-width: 768px) {
  .payment-history-page {
    padding: 1rem;
  }
}
</style>
