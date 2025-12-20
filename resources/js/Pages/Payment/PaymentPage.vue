<template>
  <div class="payment-page">
    <div class="page-header mb-8">
      <h1 class="text-4xl font-bold text-gray-900">Pembayaran Konsultasi</h1>
      <p class="text-gray-600 mt-2">Selesaikan pembayaran untuk konsultasi medis Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Payment Form (Main) -->
      <div class="lg:col-span-2">
        <PaymentForm />
      </div>

      <!-- Sidebar Info -->
      <div class="space-y-6">
        <!-- Info Card -->
        <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
          <h3 class="font-bold text-blue-900 mb-4">Informasi Pembayaran</h3>
          <ul class="space-y-3 text-sm text-blue-800">
            <li class="flex items-start gap-2">
              <span class="text-xl leading-none">✓</span>
              <span>Pembayaran Anda 100% aman dan terenkripsi</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-xl leading-none">✓</span>
              <span>Proses verifikasi otomatis untuk semua metode</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-xl leading-none">✓</span>
              <span>Invoice otomatis dikirim ke email Anda</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-xl leading-none">✓</span>
              <span>Dukung pembayaran cicilan untuk beberapa metode</span>
            </li>
          </ul>
        </div>

        <!-- Tax Info -->
        <div class="bg-amber-50 rounded-lg border border-amber-200 p-6">
          <h3 class="font-bold text-amber-900 mb-4">Rincian Pajak</h3>
          <div class="space-y-2 text-sm text-amber-800">
            <p class="flex justify-between">
              <span>PPh (15%):</span>
              <span class="font-medium">Otomatis dihitung</span>
            </p>
            <p class="flex justify-between">
              <span>PPN (11%):</span>
              <span class="font-medium">Otomatis dihitung</span>
            </p>
            <p class="flex justify-between text-amber-900 font-bold pt-2 border-t border-amber-200">
              <span>Total Pajak:</span>
              <span>26% dari amount</span>
            </p>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-green-50 rounded-lg border border-green-200 p-6">
          <h3 class="font-bold text-green-900 mb-4">Metode Pembayaran</h3>
          <ul class="space-y-2 text-sm text-green-800">
            <li>💳 Kartu Kredit (Visa, Mastercard, Amex)</li>
            <li>📱 GCash (untuk pengguna Filipina)</li>
            <li>🏦 Transfer Bank BCA/Mandiri</li>
            <li>👛 E-Wallet (OVO, Dana, LinkAja)</li>
          </ul>
        </div>

        <!-- FAQ -->
        <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
          <h3 class="font-bold text-gray-900 mb-4">Pertanyaan Umum</h3>
          <div class="space-y-3 text-sm text-gray-700">
            <details class="group">
              <summary class="font-medium cursor-pointer hover:text-blue-600">
                Berapa lama proses verifikasi?
              </summary>
              <p class="mt-2 text-gray-600 pl-4">Sebagian besar pembayaran terverifikasi dalam 5-10 menit</p>
            </details>
            <details class="group">
              <summary class="font-medium cursor-pointer hover:text-blue-600">
                Apakah ada biaya tambahan?
              </summary>
              <p class="mt-2 text-gray-600 pl-4">Tidak, hanya pajak yang ditampilkan di form pembayaran</p>
            </details>
            <details class="group">
              <summary class="font-medium cursor-pointer hover:text-blue-600">
                Bagaimana jika pembayaran gagal?
              </summary>
              <p class="mt-2 text-gray-600 pl-4">Hubungi support kami dan kami akan membantu retry</p>
            </details>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import PaymentForm from '@/components/Payment/PaymentForm.vue'
</script>

<style scoped>
.payment-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

@media (max-width: 768px) {
  .payment-page {
    padding: 1rem;
  }
}
</style>
