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
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Syarat & Ketentuan</h1>
        <p class="text-gray-500 text-sm">Terakhir diperbarui: {{ lastUpdated }}</p>
        <div class="h-1 w-20 bg-indigo-600 mt-4 rounded"></div>
      </div>

      <!-- Content -->
      <div class="bg-white rounded-xl shadow-md p-8 md:p-12 space-y-10 text-gray-700 leading-relaxed">
        <!-- Section 1 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Penerimaan Syarat</h2>
          <p>
            Dengan mengakses dan menggunakan platform Telemedicine RSUD dr. R. Soedarsono ("Platform"), Anda menerima 
            dan setuju untuk terikat oleh syarat dan ketentuan ini. Jika Anda tidak setuju dengan bagian mana pun dari 
            syarat ini, maka Anda tidak boleh menggunakan Platform.
          </p>
        </section>

        <!-- Section 2 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Pendaftaran & Akun</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">2.1 Pendaftaran Akun:</span>
              Untuk menggunakan Platform, Anda harus mendaftar dengan memberikan informasi yang akurat, lengkap, dan 
              terkini. Anda bertanggung jawab atas informasi yang Anda berikan.
            </p>
            <p>
              <span class="font-semibold">2.2 Keamanan Akun:</span>
              Anda bertanggung jawab untuk menjaga kerahasiaan password dan informasi akun Anda. Semua aktivitas yang 
              terjadi di akun Anda adalah tanggung jawab Anda.
            </p>
            <p>
              <span class="font-semibold">2.3 Verifikasi Data:</span>
              Platform berhak melakukan verifikasi data pribadi Anda, termasuk NIK, SIP, dan dokumen pendukung lainnya.
            </p>
          </div>
        </section>

        <!-- Section 3 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Ketentuan Penggunaan</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">3.1 Penggunaan Sah:</span>
              Anda setuju untuk menggunakan Platform hanya untuk tujuan yang sah dan tidak melakukan aktivitas yang 
              merugikan, mengganggu, atau melanggar hukum.
            </p>
            <p>
              <span class="font-semibold">3.2 Larangan Aktivitas:</span>
              Dilarang menggunakan Platform untuk:
            </p>
            <ul class="list-disc list-inside ml-4 space-y-2">
              <li>Mengirim konten yang melanggar hak cipta atau kekayaan intelektual</li>
              <li>Mengakses sistem tanpa otorisasi (hacking)</li>
              <li>Mengirim spam, malware, atau virus</li>
              <li>Melakukan penipuan atau misrepresentasi</li>
              <li>Mengganggu layanan atau pengalaman pengguna lain</li>
              <li>Mengumpulkan data pribadi pengguna tanpa izin</li>
            </ul>
          </div>
        </section>

        <!-- Section 4 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Layanan Medis & Konsultasi</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">4.1 Bukan Pengganti Medis Darurat:</span>
              Layanan telemedicine kami bukan pengganti untuk perawatan medis darurat. Jika mengalami keadaan darurat, 
              hubungi layanan emergensi lokal segera.
            </p>
            <p>
              <span class="font-semibold">4.2 Keputusan Medis Profesional:</span>
              Semua keputusan medis harus dibuat berdasarkan penilaian profesional dokter yang berkualifikasi.
            </p>
            <p>
              <span class="font-semibold">4.3 Tanggung Jawab Dokter:</span>
              Dokter yang terdaftar harus memiliki lisensi praktik (SIP) yang valid dan beroperasi sesuai dengan 
              standar etika medis profesional.
            </p>
            <p>
              <span class="font-semibold">4.4 Rekam Medis Elektronik:</span>
              Data medis Anda disimpan secara elektronik dan dilindungi sesuai dengan regulasi privasi yang berlaku.
            </p>
          </div>
        </section>

        <!-- Section 5 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Pembayaran & Biaya</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">5.1 Biaya Konsultasi:</span>
              Biaya konsultasi ditampilkan sebelum Anda melakukan pembayaran. Anda setuju untuk membayar biaya yang 
              ditunjukkan.
            </p>
            <p>
              <span class="font-semibold">5.2 Metode Pembayaran:</span>
              Platform menerima berbagai metode pembayaran. Semua transaksi dienkripsi dan aman.
            </p>
            <p>
              <span class="font-semibold">5.3 Kebijakan Pembatalan:</span>
              Pembatalan dapat dilakukan paling lambat 1 jam sebelum jadwal konsultasi untuk mendapatkan pengembalian 
              dana penuh.
            </p>
            <p>
              <span class="font-semibold">5.4 Tidak Ada Biaya Tersembunyi:</span>
              Semua biaya ditampilkan dengan jelas. Anda tidak akan dikenakan biaya tambahan yang tidak disetujui.
            </p>
          </div>
        </section>

        <!-- Section 6 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Privasi & Keamanan Data</h2>
          <p>
            Penggunaan dan perlindungan data pribadi Anda diatur dalam 
            <router-link to="/privacy" class="text-indigo-600 font-semibold hover:underline">Kebijakan Privasi</router-link> 
            kami. Dengan menggunakan Platform, Anda setuju dengan praktik privasi kami.
          </p>
        </section>

        <!-- Section 7 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Batasan Tanggung Jawab</h2>
          <div class="space-y-3">
            <p>
              <span class="font-semibold">7.1 Sesuai Keadaan:</span>
              Platform disediakan "sebagaimana adanya". Kami tidak memberikan jaminan bahwa Platform akan bebas dari 
              gangguan atau kesalahan.
            </p>
            <p>
              <span class="font-semibold">7.2 Pembatasan Kewajiban:</span>
              Kami tidak bertanggung jawab atas kerugian tidak langsung, insidental, atau konsekuensial yang timbul 
              dari penggunaan Platform.
            </p>
            <p>
              <span class="font-semibold">7.3 Konten Pihak Ketiga:</span>
              Kami tidak bertanggung jawab atas konten, layanan, atau produk yang disediakan oleh pihak ketiga.
            </p>
          </div>
        </section>

        <!-- Section 8 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Penghentian Akses</h2>
          <p>
            Kami berhak untuk menghentikan atau menangguhkan akses Anda ke Platform jika Anda melanggar syarat ini 
            atau melakukan aktivitas yang merugikan Platform atau pengguna lain.
          </p>
        </section>

        <!-- Section 9 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Perubahan Syarat</h2>
          <p>
            Kami dapat mengubah syarat dan ketentuan ini kapan saja. Perubahan akan efektif sejak tanggal posting. 
            Penggunaan Platform setelah perubahan berarti Anda menerima syarat yang diperbarui.
          </p>
        </section>

        <!-- Section 10 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Hukum & Yurisdiksi</h2>
          <p>
            Syarat dan ketentuan ini diatur oleh hukum Republik Indonesia. Setiap perselisihan akan diselesaikan 
            melalui pengadilan yang berwenang di Indonesia.
          </p>
        </section>

        <!-- Section 11 -->
        <section>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Hubungi Kami</h2>
          <p>
            Jika Anda memiliki pertanyaan tentang syarat dan ketentuan ini, silakan hubungi kami:
          </p>
          <div class="mt-4 space-y-2 text-sm">
            <p><span class="font-semibold">Email:</span> angguny03@gmail.com</p>
            <p><span class="font-semibold">WhatsApp:</span> 08885773781</p>
            <p><span class="font-semibold">Alamat:</span> JL KH Al Masyhuri Abdulloh, Sambirejo, Pasuruan, Jawa Timur</p>
          </div>
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
import { useRouter } from 'vue-router'

const router = useRouter()
const lastUpdated = ref('27 Desember 2025')
</script>
