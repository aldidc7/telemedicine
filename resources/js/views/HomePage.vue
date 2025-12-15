<template>
  <div class="min-h-screen bg-linear-to-b from-white via-indigo-50 to-gray-50">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
      <div class="text-center mb-16">
        <h1 class="text-5xl md:text-6xl font-black bg-linear-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-6 leading-tight">
          Kesehatan di Ujung Jari Anda
        </h1>
        <p class="text-lg md:text-xl text-gray-700 mb-10 max-w-3xl mx-auto leading-relaxed">
          Platform telemedicine modern yang menghubungkan Anda dengan dokter profesional kapan saja, di mana saja
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <router-link
            to="/register/pasien"
            class="px-8 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-bold shadow-lg hover:shadow-xl"
          >
            Daftar Sebagai Pasien
          </router-link>
          <router-link
            to="/register/dokter"
            class="px-8 py-3 bg-white text-indigo-600 border-2 border-indigo-600 rounded-xl hover:bg-indigo-50 transition font-bold shadow-md hover:shadow-lg"
          >
            Daftar Sebagai Dokter
          </router-link>
        </div>
        <p class="text-gray-600 mt-6 text-sm">Atau <router-link to="/login" class="text-indigo-600 font-semibold hover:text-indigo-700">masuk ke akun Anda</router-link></p>
      </div>

      <!-- Features Section -->
      <div class="grid md:grid-cols-3 gap-8 mb-16">
        <div class="group bg-white rounded-2xl p-8 shadow-md hover:shadow-xl transition border border-gray-100">
          <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Dokter Berpengalaman</h3>
          <p class="text-gray-600 text-sm leading-relaxed">Konsultasi dengan dokter spesialis bersertifikat dan berpengalaman</p>
        </div>

        <div class="group bg-white rounded-2xl p-8 shadow-md hover:shadow-xl transition border border-gray-100">
          <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">24/7 Tersedia</h3>
          <p class="text-gray-600 text-sm leading-relaxed">Akses layanan kesehatan kapan saja, hari atau malam</p>
        </div>

        <div class="group bg-white rounded-2xl p-8 shadow-md hover:shadow-xl transition border border-gray-100">
          <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
            <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Aman & Privat</h3>
          <p class="text-gray-600 text-sm leading-relaxed">Data kesehatan Anda terlindungi dengan enkripsi tingkat enterprise</p>
        </div>
      </div>

      <!-- Benefits Section -->
      <div class="bg-linear-to-r from-indigo-600 to-purple-600 rounded-3xl p-12 shadow-xl mb-16 text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-10 text-center">Mengapa Memilih Kami?</h2>
        <div class="grid md:grid-cols-2 gap-6">
          <div class="flex gap-4">
            <svg class="w-6 h-6 shrink-0 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <div>
              <h4 class="font-bold mb-1">Konsultasi Instan</h4>
              <p class="text-sm text-white text-opacity-90">Chatting dengan dokter dalam hitungan menit</p>
            </div>
          </div>
          <div class="flex gap-4">
            <svg class="w-6 h-6 shrink-0 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <div>
              <h4 class="font-bold mb-1">Resep Digital</h4>
              <p class="text-sm text-white text-opacity-90">Terima resep langsung dari dokter</p>
            </div>
          </div>
          <div class="flex gap-4">
            <svg class="w-6 h-6 shrink-0 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <div>
              <h4 class="font-bold mb-1">Rekam Medis Digital</h4>
              <p class="text-sm text-white text-opacity-90">Riwayat kesehatan tersimpan aman</p>
            </div>
          </div>
          <div class="flex gap-4">
            <svg class="w-6 h-6 shrink-0 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <div>
              <h4 class="font-bold mb-1">Harga Terjangkau</h4>
              <p class="text-sm text-white text-opacity-90">Konsultasi dengan harga kompetitif</p>
            </div>
          </div>
        </div>
      </div>

      <!-- How It Works -->
      <div class="mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-10 text-gray-900">Cara Kerjanya</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center">
            <div class="w-16 h-16 bg-indigo-600 rounded-full text-white font-bold text-xl flex items-center justify-center mx-auto mb-3 shadow-lg">1</div>
            <h4 class="font-bold text-gray-900 mb-1">Daftar</h4>
            <p class="text-gray-600 text-sm">Buat akun dengan mudah</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-purple-600 rounded-full text-white font-bold text-xl flex items-center justify-center mx-auto mb-3 shadow-lg">2</div>
            <h4 class="font-bold text-gray-900 mb-1">Pilih Dokter</h4>
            <p class="text-gray-600 text-sm">Cari spesialisasi Anda</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-pink-600 rounded-full text-white font-bold text-xl flex items-center justify-center mx-auto mb-3 shadow-lg">3</div>
            <h4 class="font-bold text-gray-900 mb-1">Konsultasi</h4>
            <p class="text-gray-600 text-sm">Chat dengan dokter</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-red-600 rounded-full text-white font-bold text-xl flex items-center justify-center mx-auto mb-3 shadow-lg">4</div>
            <h4 class="font-bold text-gray-900 mb-1">Sembuh</h4>
            <p class="text-gray-600 text-sm">Pantau kesehatan Anda</p>
          </div>
        </div>
      </div>      <!-- CTA Section -->
      <div class="text-center bg-linear-to-r from-indigo-600 to-purple-600 rounded-3xl p-12 shadow-xl text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-3">Siap Untuk Kesehatan yang Lebih Baik?</h2>
        <p class="text-base md:text-lg text-white text-opacity-90 mb-8">Bergabunglah dengan ribuan pasien yang telah mempercayai kami</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <router-link
            to="/register/pasien"
            class="px-8 py-3 bg-white text-indigo-600 rounded-xl hover:shadow-lg transition font-bold"
          >
            Daftar Sekarang
          </router-link>
          <router-link
            to="/login"
            class="px-8 py-3 border-2 border-white text-white rounded-xl hover:bg-white hover:bg-opacity-10 transition font-bold"
          >
            Sudah Punya Akun?
          </router-link>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 mt-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <div>
            <h3 class="text-white font-bold text-lg mb-4">Telemedicine</h3>
            <p class="text-sm">Platform kesehatan digital terpercaya untuk konsultasi dengan dokter profesional</p>
          </div>
          <div>
            <h4 class="text-white font-bold mb-4">Produk</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">Untuk Pasien</a></li>
              <li><a href="#" class="hover:text-white transition">Untuk Dokter</a></li>
              <li><a href="#" class="hover:text-white transition">Fitur</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-bold mb-4">Perusahaan</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">Tentang Kami</a></li>
              <li><a href="#" class="hover:text-white transition">Blog</a></li>
              <li><a href="#" class="hover:text-white transition">Karir</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-bold mb-4">Legal</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">Privasi</a></li>
              <li><a href="#" class="hover:text-white transition">Terms</a></li>
              <li><a href="#" class="hover:text-white transition">Kontak</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-sm">
          <p>&copy; 2025 Telemedicine. Semua hak dilindungi.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  document.title = 'Telemedicine - Platform Konsultasi Kesehatan Digital'
})
</script>
