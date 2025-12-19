<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-12 text-center">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">Bantuan & FAQ</h1>
      <p class="text-lg text-gray-600">Temukan jawaban atas pertanyaan umum Anda</p>
    </div>

    <!-- Search -->
    <div class="max-w-2xl mx-auto mb-12">
      <div class="relative">
        <input v-model="searchQuery" type="text" placeholder="Cari pertanyaan..." class="w-full px-6 py-4 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 text-lg">
        <svg class="absolute right-4 top-4 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Categories -->
    <div class="max-w-5xl mx-auto mb-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <button 
          @click="selectedCategory = ''"
          :class="[selectedCategory === '' ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 hover:shadow-lg']"
          class="p-4 rounded-lg shadow transition-all font-medium">
          Semua
        </button>
        <button 
          v-for="category in categories" 
          :key="category"
          @click="selectedCategory = category"
          :class="[selectedCategory === category ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 hover:shadow-lg']"
          class="p-4 rounded-lg shadow transition-all font-medium">
          {{ category }}
        </button>
      </div>
    </div>

    <!-- FAQ Items -->
    <div class="max-w-3xl mx-auto space-y-4">
      <div v-if="filteredFaqs.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-600 text-lg">Tidak ada hasil untuk pencarian Anda</p>
      </div>

      <div v-for="faq in filteredFaqs" :key="faq.id" class="bg-white rounded-lg shadow">
        <button 
          @click="toggleFaq(faq.id)"
          class="w-full p-6 flex justify-between items-start hover:bg-gray-50 transition-colors">
          <div class="text-left flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ faq.question }}</h3>
            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">{{ faq.category }}</span>
          </div>
          <svg 
            :class="[expandedFaq === faq.id ? 'rotate-180' : '']"
            class="w-6 h-6 text-blue-600 transition-transform ml-4 flex-shrink-0" 
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </button>

        <div v-if="expandedFaq === faq.id" class="border-t border-gray-200 p-6 bg-gray-50">
          <p class="text-gray-700 leading-relaxed">{{ faq.answer }}</p>
          <div v-if="faq.related" class="mt-4 pt-4 border-t border-gray-300">
            <p class="text-sm font-medium text-gray-600 mb-2">Pertanyaan terkait:</p>
            <ul class="space-y-2">
              <li v-for="related in faq.related" :key="related" class="text-sm text-blue-600 hover:text-blue-800 cursor-pointer">
                → {{ related }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Section -->
    <div class="max-w-3xl mx-auto mt-16 bg-white rounded-lg shadow p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Masih Butuh Bantuan?</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 mb-2">Email</h3>
          <a href="mailto:support@telemedicine.local" class="text-blue-600 hover:text-blue-800">support@telemedicine.local</a>
        </div>

        <div class="text-center">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 mb-2">Telepon</h3>
          <a href="tel:+6212345678" class="text-blue-600 hover:text-blue-800">+62 123 456 78</a>
        </div>

        <div class="text-center">
          <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 mb-2">Live Chat</h3>
          <button class="text-blue-600 hover:text-blue-800 font-medium">Mulai chat sekarang</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedCategory = ref('')
const expandedFaq = ref(null)

const categories = ['Akun', 'Konsultasi', 'Pembayaran', 'Teknis']

const faqs = [
  {
    id: 1,
    category: 'Akun',
    question: 'Bagaimana cara membuat akun baru?',
    answer: 'Klik tombol "Daftar" di halaman login, pilih role Anda (Pasien atau Dokter), kemudian isi data pribadi Anda. Setelah itu, verifikasi email Anda melalui link yang dikirimkan.',
    related: ['Bagaimana cara reset password?', 'Bagaimana cara mengubah email?'],
  },
  {
    id: 2,
    category: 'Akun',
    question: 'Bagaimana cara reset password?',
    answer: 'Klik "Lupa Password" di halaman login, masukkan email Anda, dan ikuti instruksi di email untuk membuat password baru.',
    related: ['Bagaimana cara membuat akun baru?'],
  },
  {
    id: 3,
    category: 'Konsultasi',
    question: 'Bagaimana cara membuat konsultasi baru?',
    answer: 'Masuk ke akun Anda, klik "Cari Dokter", pilih dokter yang sesuai dengan kebutuhan Anda, klik "Konsultasi", kemudian jelaskan keluhan Anda.',
    related: ['Berapa lama dokter merespons?'],
  },
  {
    id: 4,
    category: 'Konsultasi',
    question: 'Berapa lama dokter merespons?',
    answer: 'Dokter biasanya merespons dalam waktu 5-30 menit tergantung ketersediaan mereka. Anda akan menerima notifikasi saat dokter merespons.',
    related: ['Bagaimana cara membuat konsultasi baru?'],
  },
  {
    id: 5,
    category: 'Pembayaran',
    question: 'Metode pembayaran apa yang tersedia?',
    answer: 'Kami menerima transfer bank, e-wallet (GCash, OVO, DANA), dan kartu kredit. Semua pembayaran diproses secara aman.',
    related: ['Apakah ada biaya tambahan?', 'Bagaimana cara membatalkan pembayaran?'],
  },
  {
    id: 6,
    category: 'Pembayaran',
    question: 'Apakah ada biaya tambahan?',
    answer: 'Harga yang ditampilkan sudah termasuk semua biaya. Tidak ada biaya tersembunyi. Biaya admin dari penyedia pembayaran sudah kami tanggung.',
    related: ['Metode pembayaran apa yang tersedia?'],
  },
  {
    id: 7,
    category: 'Teknis',
    question: 'Aplikasi tidak bisa diakses, apa yang harus dilakukan?',
    answer: '1. Coba refresh halaman browser Anda\n2. Hapus cache browser\n3. Coba gunakan browser berbeda\n4. Hubungi support jika masalah berlanjut',
    related: [],
  },
  {
    id: 8,
    category: 'Teknis',
    question: 'Video call tidak berfungsi, bagaimana caranya?',
    answer: 'Pastikan: 1) Kamera dan mikrofon terhubung, 2) Browser Anda support WebRTC, 3) Koneksi internet stabil, 4) Izinkan akses kamera/mikrofon',
    related: [],
  },
]

const filteredFaqs = computed(() => {
  return faqs.filter(faq => {
    const matchesSearch = searchQuery.value === '' || 
      faq.question.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      faq.answer.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = selectedCategory.value === '' || faq.category === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

const toggleFaq = (id) => {
  expandedFaq.value = expandedFaq.value === id ? null : id
}
</script>
