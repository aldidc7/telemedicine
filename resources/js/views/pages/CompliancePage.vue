<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 to-slate-100 py-12 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Back Button -->
      <button @click="$router.back()" class="mb-6 inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali
      </button>
      
      <!-- Header -->
      <div class="mb-12">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Compliance & Keamanan</h1>
        <p class="text-gray-500 text-sm">Terakhir diperbarui: {{ lastUpdated }}</p>
        <div class="h-1 w-20 bg-indigo-600 mt-4 rounded"></div>
      </div>

      <!-- Content -->
      <div class="bg-white rounded-xl shadow-md p-8 md:p-12 space-y-10 text-gray-700 leading-relaxed">
        <!-- Introduction -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Komitmen Keamanan & Compliance</h2>
          <p>
            Platform Telemedicine berkomitmen untuk menjaga standar keamanan dan compliance tertinggi dalam industri kesehatan digital. 
            Kami mematuhi regulasi internasional dan lokal untuk melindungi data dan privasi pengguna kami.
          </p>
        </section>

        <!-- Section 1 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">1.</span> Standar Keamanan</h2>
          <div class="space-y-3">
            <div>
              <p class="font-semibold mb-1">1.1 Enkripsi Data</p>
              <p>Semua data sensitif dienkripsi menggunakan standar SSL/TLS 256-bit untuk transmisi dan penyimpanan.</p>
            </div>
            <div>
              <p class="font-semibold mb-1">1.2 Autentikasi Dua Faktor</p>
              <p>Mendukung autentikasi multi-faktor untuk proteksi akun yang lebih kuat.</p>
            </div>
            <div>
              <p class="font-semibold mb-1">1.3 Akses Terbatas</p>
              <p>Hanya staf yang berwenang dapat mengakses data sensitif dengan audit trail lengkap.</p>
            </div>
          </div>
        </section>

        <!-- Section 2 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">2.</span> Kepatuhan Regulasi</h2>
          <div class="space-y-3">
            <div>
              <p class="font-semibold mb-1">2.1 HIPAA Equivalency</p>
              <p>Platform dirancang mengikuti standar HIPAA untuk melindungi informasi kesehatan yang terlindungi (PHI).</p>
            </div>
            <div>
              <p class="font-semibold mb-1">2.2 Undang-Undang Kesehatan Indonesia No. 36/2009</p>
              <p>Mematuhi regulasi kesehatan nasional Indonesia untuk pelayanan kesehatan digital.</p>
            </div>
            <div>
              <p class="font-semibold mb-1">2.3 JCI Standards</p>
              <p>Mengikuti standar Joint Commission International untuk kualitas dan keamanan pelayanan kesehatan.</p>
            </div>
            <div>
              <p class="font-semibold mb-1">2.4 Data Retention</p>
              <p>Menyimpan data medis sesuai dengan regulasi nasional (7-10 tahun) dan menghapus dengan aman setelahnya.</p>
            </div>
          </div>
        </section>

        <!-- Section 3 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">3.</span> Audit & Monitoring</h2>
          <ul class="list-disc list-inside space-y-2 ml-2">
            <li>Audit keamanan reguler oleh pihak ketiga independen</li>
            <li>Monitoring 24/7 untuk aktivitas mencurigakan</li>
            <li>Logging lengkap semua akses ke data sensitif</li>
            <li>Testing penetrasi berkala untuk identifikasi kerentanan</li>
            <li>Incident response plan yang telah disiapkan</li>
          </ul>
        </section>

        <!-- Section 4 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">4.</span> Hak Pasien</h2>
          <p class="mb-3">Dalam kerangka compliance kami, pasien memiliki hak untuk:</p>
          <ul class="list-disc list-inside space-y-2 ml-2">
            <li>Mengakses dan meninjau data medis mereka</li>
            <li>Meminta salinan rekam medis elektronik</li>
            <li>Mengoreksi informasi yang tidak akurat</li>
            <li>Meminta penghapusan data (dengan batasan hukum)</li>
            <li>Menerima notifikasi jika ada pelanggaran keamanan</li>
          </ul>
        </section>

        <!-- Section 5 -->
        <section class="pb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">5.</span> Hubungi Kami</h2>
          <p class="mb-3">Untuk pertanyaan terkait keamanan dan compliance, silakan hubungi:</p>
          <div class="bg-indigo-50 rounded-lg p-4 space-y-2">
            <p><span class="font-semibold">Email:</span> compliance@telemedicine.id</p>
            <p><span class="font-semibold">WhatsApp:</span> +62 888 5773 781</p>
            <p><span class="font-semibold">Alamat:</span> JL KH Al Masyhuri Abdulloh, Sambirejo, Pasuruan, Jawa Timur</p>
          </div>
        </section>
      </div>

      <!-- Back Button Bottom -->
      <div class="mt-12 text-center">
        <router-link to="/register" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Kembali ke Registrasi
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const lastUpdated = ref(new Date().toLocaleDateString('id-ID', {
  year: 'numeric',
  month: 'long',
  day: 'numeric'
}))
</script>
