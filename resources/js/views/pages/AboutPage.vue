<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 to-white">
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-5xl font-black mb-4">Tentang Telemedicine</h1>
        <p class="text-xl text-indigo-100">Membawa kesehatan lebih dekat kepada Anda</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-16">
      <!-- Mission & Vision -->
      <section class="mb-16">
        <div class="grid md:grid-cols-2 gap-8">
          <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              Visi Kami
            </h2>
            <p class="text-gray-700 leading-relaxed">
              Menjadi platform telemedicine terdepan di Indonesia yang memberikan akses kesehatan berkualitas 
              tinggi kepada semua kalangan masyarakat, kapan saja dan dimana saja.
            </p>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Misi Kami
            </h2>
            <p class="text-gray-700 leading-relaxed">
              Menyediakan layanan konsultasi kesehatan online yang aman, terpercaya, dan terjangkau dengan 
              menghubungkan pasien dengan dokter profesional bersertifikat.
            </p>
          </div>
        </div>
      </section>

      <!-- Values -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center">Nilai-Nilai Kami</h2>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="bg-linear-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center text-white text-xl mb-4">
              🏥
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Integritas</h3>
            <p class="text-gray-700 text-sm">
              Kami berkomitmen pada integritas tertinggi dalam setiap aspek bisnis kami dan dalam hubungan 
              dengan pasien dan dokter.
            </p>
          </div>

          <div class="bg-linear-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center text-white text-xl mb-4">
              ❤️
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Kepedulian</h3>
            <p class="text-gray-700 text-sm">
              Kesehatan dan kesejahteraan pasien adalah prioritas utama kami dalam setiap keputusan dan 
              tindakan yang kami ambil.
            </p>
          </div>

          <div class="bg-linear-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-white text-xl mb-4">
              🚀
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Inovasi</h3>
            <p class="text-gray-700 text-sm">
              Kami terus berinovasi untuk memberikan pengalaman terbaik dan teknologi terkini dalam 
              layanan telemedicine.
            </p>
          </div>

          <div class="bg-linear-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
            <div class="w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center text-white text-xl mb-4">
              🔒
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Keamanan</h3>
            <p class="text-gray-700 text-sm">
              Privasi dan keamanan data pasien adalah tanggung jawab kami yang paling serius dengan 
              enkripsi tingkat enterprise.
            </p>
          </div>

          <div class="bg-linear-to-br from-pink-50 to-pink-100 rounded-xl p-6 border border-pink-200">
            <div class="w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center text-white text-xl mb-4">
              🤝
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Kolaborasi</h3>
            <p class="text-gray-700 text-sm">
              Kami percaya pada kekuatan kolaborasi antara pasien, dokter, dan tim kami untuk hasil 
              kesehatan yang optimal.
            </p>
          </div>

          <div class="bg-linear-to-br from-cyan-50 to-cyan-100 rounded-xl p-6 border border-cyan-200">
            <div class="w-12 h-12 bg-cyan-600 rounded-lg flex items-center justify-center text-white text-xl mb-4">
              ⭐
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Keunggulan</h3>
            <p class="text-gray-700 text-sm">
              Kami berkomitmen untuk memberikan layanan berkualitas tinggi dan pengalaman terbaik bagi 
              semua pengguna kami.
            </p>
          </div>
        </div>
      </section>

      <!-- History & Achievements -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-10">Pencapaian Kami</h2>
        <div class="space-y-6">
          <div class="flex gap-6 items-start">
            <div class="shrink-0 w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
              <span class="text-2xl">📱</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">10,000+ Pengguna Aktif</h3>
              <p class="text-gray-700">Dipercaya oleh ribuan pasien dan ratusan dokter profesional di seluruh Indonesia.</p>
            </div>
          </div>

          <div class="flex gap-6 items-start">
            <div class="shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <span class="text-2xl">✅</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">50,000+ Konsultasi Berhasil</h3>
              <p class="text-gray-700">Telah memfasilitasi lebih dari 50,000 konsultasi medis online yang sukses dan memuaskan.</p>
            </div>
          </div>

          <div class="flex gap-6 items-start">
            <div class="shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <span class="text-2xl">⭐</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Rating 4.8/5 Bintang</h3>
              <p class="text-gray-700">Tingkat kepuasan pengguna yang sangat tinggi dengan rating konsisten di atas 4.8 bintang.</p>
            </div>
          </div>

          <div class="flex gap-6 items-start">
            <div class="shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <span class="text-2xl">🏥</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Kerjasama dengan Rumah Sakit Terkemuka</h3>
              <p class="text-gray-700">Bermitra dengan rumah sakit dan klinik terkemuka untuk memberikan layanan terbaik.</p>
            </div>
          </div>

          <div class="flex gap-6 items-start">
            <div class="shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <span class="text-2xl">🔒</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Sertifikasi Keamanan Internasional</h3>
              <p class="text-gray-700">Memenuhi standar keamanan data internasional dan regulasi perlindungan data pribadi.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Team Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center">Tim Kami</h2>
        <p class="text-gray-700 text-center mb-10 max-w-2xl mx-auto">
          Tim profesional kami terdiri dari dokter, teknisi, dan spesialis kesehatan digital yang berdedikasi 
          untuk memberikan layanan terbaik bagi Anda.
        </p>
        <div class="grid md:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl shadow hover:shadow-lg transition text-center p-6">
            <div class="w-20 h-20 bg-linear-to-br from-indigo-400 to-purple-500 rounded-full mx-auto mb-4"></div>
            <h3 class="font-bold text-gray-900">Dr. Angguni Ramadhani</h3>
            <p class="text-sm text-gray-600">CEO & Founder</p>
          </div>

          <div class="bg-white rounded-xl shadow hover:shadow-lg transition text-center p-6">
            <div class="w-20 h-20 bg-linear-to-br from-green-400 to-blue-500 rounded-full mx-auto mb-4"></div>
            <h3 class="font-bold text-gray-900">Tim Medis</h3>
            <p class="text-sm text-gray-600">Dokter Profesional</p>
          </div>

          <div class="bg-white rounded-xl shadow hover:shadow-lg transition text-center p-6">
            <div class="w-20 h-20 bg-linear-to-br from-orange-400 to-pink-500 rounded-full mx-auto mb-4"></div>
            <h3 class="font-bold text-gray-900">Tim Teknologi</h3>
            <p class="text-sm text-gray-600">Engineer & Developer</p>
          </div>

          <div class="bg-white rounded-xl shadow hover:shadow-lg transition text-center p-6">
            <div class="w-20 h-20 bg-linear-to-br from-cyan-400 to-blue-600 rounded-full mx-auto mb-4"></div>
            <h3 class="font-bold text-gray-900">Tim Support</h3>
            <p class="text-sm text-gray-600">Customer Service</p>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="bg-linear-to-r from-indigo-600 to-purple-600 rounded-2xl p-12 text-white text-center">
        <h2 class="text-3xl font-bold mb-4">Bergabunglah dengan Kami</h2>
        <p class="text-lg text-indigo-100 mb-8 max-w-2xl mx-auto">
          Mulai konsultasi dengan dokter profesional hari ini dan rasakan perbedaannya.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link to="/register" class="px-8 py-3 bg-white text-indigo-600 font-bold rounded-lg hover:bg-gray-100 transition">
            Daftar Sekarang
          </router-link>
          <router-link to="/contact" class="px-8 py-3 border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:bg-opacity-10 transition">
            Hubungi Kami
          </router-link>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { defineComponent } from 'vue'
</script>
