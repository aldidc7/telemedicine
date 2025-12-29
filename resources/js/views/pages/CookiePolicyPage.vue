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
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Kebijakan Cookie</h1>
        <p class="text-gray-500 text-sm">Terakhir diperbarui: {{ lastUpdated }}</p>
        <div class="h-1 w-20 bg-indigo-600 mt-4 rounded"></div>
      </div>

      <!-- Content -->
      <div class="bg-white rounded-xl shadow-md p-8 md:p-12 space-y-10 text-gray-700 leading-relaxed">
        <!-- Introduction -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Apa Itu Cookie?</h2>
          <p>
            Cookie adalah file kecil yang disimpan di perangkat Anda oleh browser web saat Anda mengunjungi website. 
            Platform Telemedicine menggunakan cookie untuk meningkatkan pengalaman pengguna dan memberikan layanan yang lebih baik.
          </p>
        </section>

        <!-- Section 1 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">1.</span> Jenis Cookie yang Kami Gunakan</h2>
          <div class="space-y-4">
            <div>
              <p class="font-semibold mb-2">1.1 Cookie Esensial</p>
              <p>Cookie yang diperlukan untuk fungsi dasar website seperti autentikasi, keamanan, dan navigasi.</p>
            </div>
            <div>
              <p class="font-semibold mb-2">1.2 Cookie Fungsionalitas</p>
              <p>Cookie yang mengingat preferensi Anda seperti bahasa, tema, dan pengaturan yang dipilih.</p>
            </div>
            <div>
              <p class="font-semibold mb-2">1.3 Cookie Analitik</p>
              <p>Cookie yang membantu kami memahami bagaimana pengguna berinteraksi dengan website untuk perbaikan berkelanjutan.</p>
            </div>
          </div>
        </section>

        <!-- Section 2 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">2.</span> Bagaimana Kami Menggunakan Cookie</h2>
          <ul class="list-disc list-inside space-y-2 ml-2">
            <li>Menjaga sesi login Anda tetap aktif</li>
            <li>Mengingat preferensi dan pengaturan Anda</li>
            <li>Melacak penggunaan website untuk analitik</li>
            <li>Meningkatkan keamanan dan mencegah fraud</li>
            <li>Memberikan konten yang dipersonalisasi</li>
          </ul>
        </section>

        <!-- Section 3 -->
        <section class="pb-8 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">3.</span> Kontrol Cookie Anda</h2>
          <p class="mb-3">
            Anda dapat mengontrol cookie melalui pengaturan browser Anda. Sebagian besar browser memungkinkan Anda untuk:
          </p>
          <ul class="list-disc list-inside space-y-2 ml-2">
            <li>Melihat cookie apa yang telah disimpan</li>
            <li>Menghapus cookie tertentu atau semua cookie</li>
            <li>Memblokir cookie dari website tertentu</li>
            <li>Menerima pemberitahuan sebelum cookie disimpan</li>
          </ul>
          <p class="mt-4 text-sm text-gray-600">
            Namun, menonaktifkan cookie mungkin memengaruhi fungsionalitas website dan layanan kami.
          </p>
        </section>

        <!-- Section 4 -->
        <section class="pb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3"><span class="text-indigo-600">4.</span> Perubahan Kebijakan</h2>
          <p>
            Kami dapat memperbarui kebijakan cookie ini dari waktu ke waktu. Perubahan signifikan akan dikomunikasikan kepada Anda melalui 
            website atau email. Penggunaan berkelanjutan atas website kami setelah perubahan berarti Anda menerima kebijakan yang diperbarui.
          </p>
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
