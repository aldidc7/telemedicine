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
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Kebijakan Privasi</h1>
        <p class="text-gray-500 text-sm">Terakhir diperbarui: {{ lastUpdated }}</p>
        <div class="h-1 w-20 bg-indigo-600 mt-4 rounded"></div>
      </div>

      <!-- Content -->
      <div class="bg-white rounded-xl shadow-md p-8 md:p-12 space-y-10 text-gray-700 leading-relaxed">
        <!-- Introduction -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Pendahuluan</h2>
          <p>
            Platform Telemedicine RSUD dr. R. Soedarsono ("kami", "Platform") berkomitmen untuk melindungi privasi Anda. 
            Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, mengungkapkan, dan melindungi 
            informasi Anda.
          </p>
        </section>

        <!-- Section 1 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Informasi yang Kami Kumpulkan</h2>
          <div class="space-y-3">
            <div>
              <p class="font-semibold mb-2">1.1 Informasi Pribadi</p>
              <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Nama lengkap</li>
                <li>Email</li>
                <li>Nomor telepon</li>
                <li>Tanggal lahir</li>
                <li>Jenis kelamin</li>
                <li>NIK (untuk pasien)</li>
                <li>Alamat</li>
                <li>Nomor SIP (untuk dokter)</li>
              </ul>
            </div>

            <div>
              <p class="font-semibold mb-2">1.2 Informasi Medis</p>
              <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Riwayat konsultasi</li>
                <li>Rekam medis elektronik</li>
                <li>Resep dan pengobatan</li>
                <li>Hasil laboratorium dan tes medis</li>
                <li>Catatan dokter</li>
              </ul>
            </div>

            <div>
              <p class="font-semibold mb-2">1.3 Informasi Teknis</p>
              <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Alamat IP</li>
                <li>Tipe dan versi browser</li>
                <li>Sistem operasi</li>
                <li>Halaman yang dikunjungi</li>
                <li>Waktu dan durasi kunjungan</li>
              </ul>
            </div>

            <div>
              <p class="font-semibold mb-2">1.4 Informasi Pembayaran</p>
              <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Metode pembayaran (tidak disimpan nomor kartu lengkap)</li>
                <li>Riwayat transaksi</li>
                <li>Faktur dan tanda terima</li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Section 2 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Cara Kami Menggunakan Informasi</h2>
          <p class="mb-3">Kami menggunakan informasi yang dikumpulkan untuk:</p>
          <ul class="list-disc list-inside space-y-2 ml-2">
            <li>Menyediakan, memelihara, dan meningkatkan layanan Platform</li>
            <li>Memfasilitasi konsultasi medis antara pasien dan dokter</li>
            <li>Memproses pembayaran dan mengelola transaksi finansial</li>
            <li>Mengirim pemberitahuan, pembaruan, dan informasi layanan</li>
            <li>Merespons pertanyaan dan mendukung pengguna</li>
            <li>Melakukan analisis dan riset untuk meningkatkan Platform</li>
            <li>Mematuhi persyaratan hukum dan peraturan yang berlaku</li>
            <li>Mencegah fraud, abuse, dan aktivitas ilegal</li>
            <li>Memenuhi kewajiban medis dan kesehatan masyarakat</li>
          </ul>
        </section>

        <!-- Section 3 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Keamanan Data</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">3.1 Enkripsi:</span>
              Semua data sensitif dienkripsi menggunakan protokol SSL/TLS tingkat industri.
            </p>
            <p>
              <span class="font-semibold">3.2 Akses Terbatas:</span>
              Hanya staf yang berwenang yang memiliki akses ke informasi pribadi dan medis Anda.
            </p>
            <p>
              <span class="font-semibold">3.3 Backup & Recovery:</span>
              Data Anda dicadangkan secara berkala untuk mencegah kehilangan data.
            </p>
            <p>
              <span class="font-semibold">3.4 Audit Keamanan:</span>
              Kami melakukan audit keamanan reguler dan pembaruan sistem untuk melindungi data Anda.
            </p>
          </div>
        </section>

        <!-- Section 4 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Pembagian Informasi</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">4.1 Dengan Dokter:</span>
              Informasi medis Anda dibagikan dengan dokter yang Anda konsultasi untuk tujuan penyediaan layanan kesehatan.
            </p>
            <p>
              <span class="font-semibold">4.2 Dengan Profesional Kesehatan Lain:</span>
              Data medis dapat dibagikan dengan profesional kesehatan lain (apoteker, laboratorium) atas persetujuan Anda 
              untuk keperluan perawatan kesehatan.
            </p>
            <p>
              <span class="font-semibold">4.3 Tidak Dijual Kepada Pihak Ketiga:</span>
              Kami tidak menjual, menyewakan, atau membagikan informasi pribadi Anda kepada penyedia pihak ketiga untuk 
              tujuan pemasaran.
            </p>
            <p>
              <span class="font-semibold">4.4 Kepatuhan Hukum:</span>
              Kami dapat mengungkapkan informasi jika diwajibkan oleh hukum atau untuk melindungi hak dan keselamatan kami.
            </p>
          </div>
        </section>

        <!-- Section 5 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Hak Anda</h2>
          <p class="mb-3">Anda memiliki hak untuk:</p>
          <ul class="list-disc list-inside space-y-2 ml-2">
            <li>Mengakses dan meninjau informasi pribadi Anda</li>
            <li>Meminta koreksi atau pembaruan informasi yang tidak akurat</li>
            <li>Menghapus akun Anda dan data terkait (dengan batasan hukum)</li>
            <li>Menarik persetujuan untuk pemrosesan data tertentu</li>
            <li>Menerima data Anda dalam format terstruktur</li>
            <li>Membatasi penggunaan data Anda</li>
          </ul>
        </section>

        <!-- Section 6 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Retensi Data</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">6.1 Rekam Medis:</span>
              Rekam medis disimpan sesuai dengan persyaratan hukum kesehatan (minimal 5 tahun setelah konsultasi terakhir).
            </p>
            <p>
              <span class="font-semibold">6.2 Data Akun:</span>
              Data akun disimpan selama akun aktif. Setelah penghapusan akun, data akan dihapus dalam 30 hari kecuali 
              diperlukan untuk kepatuhan hukum.
            </p>
            <p>
              <span class="font-semibold">6.3 Data Teknis:</span>
              Log dan data teknis disimpan selama 12 bulan untuk keamanan dan troubleshooting.
            </p>
          </div>
        </section>

        <!-- Section 7 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Cookie & Teknologi Pelacakan</h2>
          <p>
            Kami menggunakan cookie dan teknologi pelacakan serupa untuk meningkatkan pengalaman pengguna, 
            menganalisis penggunaan Platform, dan menampilkan konten yang dipersonalisasi. Anda dapat mengatur 
            preferensi cookie di browser Anda.
          </p>
        </section>

        <!-- Section 8 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Pihak Ketiga & Link Eksternal</h2>
          <p>
            Platform kami dapat berisi link ke situs pihak ketiga. Kami tidak bertanggung jawab atas praktik privasi 
            situs eksternal. Kami menyarankan Anda untuk meninjau kebijakan privasi mereka sebelum membagikan informasi.
          </p>
        </section>

        <!-- Section 9 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Privasi Anak-anak</h2>
          <p>
            Platform tidak dimaksudkan untuk anak di bawah 18 tahun tanpa persetujuan orang tua. Kami tidak dengan sengaja 
            mengumpulkan informasi dari anak di bawah usia ini. Jika kami menyadari pengumpulan informasi dari anak di bawah usia ini, 
            kami akan segera menghapusnya.
          </p>
        </section>

        <!-- Section 10 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Perubahan Kebijakan</h2>
          <p>
            Kami dapat memperbarui kebijakan privasi ini kapan saja. Kami akan memberi tahu Anda tentang perubahan signifikan 
            melalui email atau pemberitahuan di Platform.
          </p>
        </section>

        <!-- Section 11 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Hubungi Kami</h2>
          <p class="mb-4">
            Jika Anda memiliki pertanyaan atau kekhawatiran tentang kebijakan privasi kami, silakan hubungi:
          </p>
          <div class="space-y-2 text-sm">
            <p><span class="font-semibold">Email:</span> angguny03@gmail.com</p>
            <p><span class="font-semibold">WhatsApp:</span> 08885773781</p>
            <p><span class="font-semibold">Alamat:</span> JL KH Al Masyhuri Abdulloh, Sambirejo, Pasuruan, Jawa Timur</p>
          </div>
        </section>

        <!-- Section 12 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">12. Kepatuhan Hukum</h2>
          <p>
            Kebijakan privasi ini mematuhi undang-undang perlindungan data Indonesia, termasuk Undang-Undang Nomor 8 Tahun 1997 
            tentang Dokumen Perusahaan dan peraturan kesehatan yang relevan.
          </p>
        </section>
      </div>

      <!-- Back Button -->
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

const lastUpdated = ref('27 Desember 2025')
</script>
