<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl font-black mb-4">Bergabunglah dengan Tim Kami</h1>
        <p class="text-xl text-indigo-100">Kami mencari talenta terbaik untuk bersama-sama merevolusi layanan kesehatan digital</p>
      </div>
    </div>

    <!-- Why Join Us -->
    <div class="max-w-4xl mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-4 text-center">Mengapa Bergabung dengan Kami?</h2>
      <p class="text-gray-600 text-center mb-12">Kami menawarkan lingkungan kerja yang inovatif dan mendukung pertumbuhan karir Anda</p>

      <div class="grid md:grid-cols-3 gap-8 mb-16">
        <!-- Benefit 1 -->
        <div class="text-center p-8 rounded-xl bg-indigo-50 hover:shadow-lg transition">
          <div class="text-5xl mb-4">🚀</div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Inovasi Berkelanjutan</h3>
          <p class="text-gray-700">
            Kami terus berinovasi untuk memberikan solusi kesehatan terbaik dengan teknologi terdepan.
          </p>
        </div>

        <!-- Benefit 2 -->
        <div class="text-center p-8 rounded-xl bg-purple-50 hover:shadow-lg transition">
          <div class="text-5xl mb-4">🎓</div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Pengembangan Karir</h3>
          <p class="text-gray-700">
            Kami menyediakan program pelatihan dan mentor untuk mengembangkan keahlian Anda.
          </p>
        </div>

        <!-- Benefit 3 -->
        <div class="text-center p-8 rounded-xl bg-pink-50 hover:shadow-lg transition">
          <div class="text-5xl mb-4">❤️</div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Tim yang Peduli</h3>
          <p class="text-gray-700">
            Bekerja dengan tim yang saling mendukung dan berbagi visi untuk kesehatan masyarakat.
          </p>
        </div>
      </div>
    </div>

    <!-- Job Openings -->
    <div class="bg-slate-50 py-16 px-4">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-4 text-center">Lowongan Kerja Terbuka</h2>
        <p class="text-gray-600 text-center mb-12">Bergabunglah dengan tim kami dan buat dampak positif untuk kesehatan masyarakat</p>

        <!-- Job Listings -->
        <div class="space-y-6">
          <!-- Job 1 -->
          <div class="bg-white rounded-xl p-6 border-l-4 border-indigo-600 hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Senior Backend Developer</h3>
                <p class="text-gray-600 mb-4">
                  Kami mencari developer berpengalaman untuk mengembangkan infrastruktur backend telemedicine kami. 
                  Anda akan bekerja dengan teknologi modern dan tim yang berdedikasi.
                </p>
                <div class="flex flex-wrap gap-3 mb-4">
                  <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">PHP/Laravel</span>
                  <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">MySQL</span>
                  <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">API Design</span>
                  <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">Jakarta/Remote</span>
                </div>
              </div>
              <button class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
                Lamar Sekarang
              </button>
            </div>
          </div>

          <!-- Job 2 -->
          <div class="bg-white rounded-xl p-6 border-l-4 border-purple-600 hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Frontend Developer (Vue.js)</h3>
                <p class="text-gray-600 mb-4">
                  Bergabunglah dengan tim frontend kami untuk membangun antarmuka pengguna yang intuitif dan responsif. 
                  Anda akan mengembangkan fitur-fitur patient-centric dengan Vue 3.
                </p>
                <div class="flex flex-wrap gap-3 mb-4">
                  <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Vue.js 3</span>
                  <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Tailwind CSS</span>
                  <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">JavaScript</span>
                  <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Jakarta/Remote</span>
                </div>
              </div>
              <button class="px-6 py-2 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                Lamar Sekarang
              </button>
            </div>
          </div>

          <!-- Job 3 -->
          <div class="bg-white rounded-xl p-6 border-l-4 border-pink-600 hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Medical Data Analyst</h3>
                <p class="text-gray-600 mb-4">
                  Kami membutuhkan analis data dengan pemahaman tentang data medis untuk membantu mengoptimalkan layanan kesehatan kami. 
                  Posisi ini menggabungkan analisis data dan healthcare intelligence.
                </p>
                <div class="flex flex-wrap gap-3 mb-4">
                  <span class="text-xs font-semibold text-pink-600 bg-pink-100 px-3 py-1 rounded-full">Data Analysis</span>
                  <span class="text-xs font-semibold text-pink-600 bg-pink-100 px-3 py-1 rounded-full">SQL</span>
                  <span class="text-xs font-semibold text-pink-600 bg-pink-100 px-3 py-1 rounded-full">Healthcare</span>
                  <span class="text-xs font-semibold text-pink-600 bg-pink-100 px-3 py-1 rounded-full">Jakarta</span>
                </div>
              </div>
              <button class="px-6 py-2 bg-pink-600 text-white font-bold rounded-lg hover:bg-pink-700 transition whitespace-nowrap">
                Lamar Sekarang
              </button>
            </div>
          </div>

          <!-- Job 4 -->
          <div class="bg-white rounded-xl p-6 border-l-4 border-green-600 hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">DevOps Engineer</h3>
                <p class="text-gray-600 mb-4">
                  Kami mencari DevOps engineer untuk mengelola infrastruktur cloud dan memastikan keandalan platform kesehatan kami. 
                  Anda akan bekerja dengan Docker, Kubernetes, dan cloud providers.
                </p>
                <div class="flex flex-wrap gap-3 mb-4">
                  <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">Docker</span>
                  <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">Kubernetes</span>
                  <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">AWS/GCP</span>
                  <span class="text-xs font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full">Jakarta/Remote</span>
                </div>
              </div>
              <button class="px-6 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition whitespace-nowrap">
                Lamar Sekarang
              </button>
            </div>
          </div>

          <!-- Job 5 -->
          <div class="bg-white rounded-xl p-6 border-l-4 border-blue-600 hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Product Manager - Healthcare</h3>
                <p class="text-gray-600 mb-4">
                  Sebagai Product Manager, Anda akan memimpin pengembangan fitur-fitur baru yang berdampak pada jutaan pengguna kesehatan. 
                  Peran ini membutuhkan pemahaman mendalam tentang industri healthcare.
                </p>
                <div class="flex flex-wrap gap-3 mb-4">
                  <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Product Strategy</span>
                  <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Healthcare</span>
                  <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">User Research</span>
                  <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Jakarta</span>
                </div>
              </div>
              <button class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                Lamar Sekarang
              </button>
            </div>
          </div>
        </div>

        <!-- View All Openings -->
        <div class="text-center mt-12">
          <p class="text-gray-600 mb-4">Tidak menemukan posisi yang tepat?</p>
          <button class="px-8 py-3 border-2 border-indigo-600 text-indigo-600 font-bold rounded-lg hover:bg-indigo-50 transition">
            Lihat Semua Lowongan Kerja
          </button>
        </div>
      </div>
    </div>

    <!-- Company Culture -->
    <div class="max-w-4xl mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Budaya Perusahaan Kami</h2>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- Culture 1 -->
        <div class="bg-linear-to-br from-indigo-50 to-purple-50 p-8 rounded-xl">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Kolaborasi Tanpa Batas</h3>
          <p class="text-gray-700">
            Tim kami terdiri dari individu berbakat yang bekerja sama untuk mencapai tujuan bersama. 
            Kami percaya bahwa kolaborasi menghasilkan inovasi terbaik.
          </p>
        </div>

        <!-- Culture 2 -->
        <div class="bg-linear-to-br from-pink-50 to-red-50 p-8 rounded-xl">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Fokus pada Dampak</h3>
          <p class="text-gray-700">
            Setiap keputusan yang kami buat selalu mengutamakan dampak positif untuk kesehatan masyarakat dan kepuasan pengguna.
          </p>
        </div>

        <!-- Culture 3 -->
        <div class="bg-linear-to-br from-green-50 to-emerald-50 p-8 rounded-xl">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Pembelajaran Berkelanjutan</h3>
          <p class="text-gray-700">
            Kami mendukung setiap anggota tim untuk terus belajar dan mengembangkan keahlian mereka melalui pelatihan dan mentoring.
          </p>
        </div>

        <!-- Culture 4 -->
        <div class="bg-linear-to-br from-blue-50 to-cyan-50 p-8 rounded-xl">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Keseimbangan Kerja-Hidup</h3>
          <p class="text-gray-700">
            Kami percaya bahwa karyawan yang bahagia dan seimbang adalah karyawan yang produktif. 
            Work-life balance adalah prioritas kami.
          </p>
        </div>
      </div>
    </div>

    <!-- Application Process -->
    <div class="bg-indigo-50 py-16 px-4">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Proses Aplikasi Kami</h2>

        <div class="grid md:grid-cols-4 gap-6">
          <!-- Step 1 -->
          <div class="text-center">
            <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">
              1
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Aplikasi</h3>
            <p class="text-sm text-gray-600">Kirimkan CV dan portfolio Anda</p>
          </div>

          <!-- Step 2 -->
          <div class="text-center">
            <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">
              2
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Wawancara Awal</h3>
            <p class="text-sm text-gray-600">Diskusi dengan HR kami</p>
          </div>

          <!-- Step 3 -->
          <div class="text-center">
            <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">
              3
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Tes Teknis</h3>
            <p class="text-sm text-gray-600">Evaluasi skill dan pengetahuan</p>
          </div>

          <!-- Step 4 -->
          <div class="text-center">
            <div class="w-12 h-12 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">
              4
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Penawaran</h3>
            <p class="text-sm text-gray-600">Bergabunglah dengan tim kami!</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-4xl mx-auto px-4 py-16 text-center">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">Siap untuk Bergabung?</h2>
      <p class="text-lg text-gray-600 mb-8">
        Jangan lewatkan kesempatan untuk menjadi bagian dari revolusi kesehatan digital
      </p>
      <router-link
        to="/careers"
        class="inline-block px-8 py-4 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition"
      >
        Lihat Semua Lowongan
      </router-link>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
</style>
