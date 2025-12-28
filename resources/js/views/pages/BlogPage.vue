<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 to-white">
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white py-16 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl font-black mb-4">Blog & Artikel</h1>
        <p class="text-xl text-indigo-100">Informasi kesehatan dan tips terkini dari para ahli</p>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="max-w-4xl mx-auto px-4 py-12">
      <div class="flex flex-col sm:flex-row gap-4 mb-10">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari artikel..."
          class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        />
        <select
          v-model="selectedCategory"
          class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        >
          <option value="">Semua Kategori</option>
          <option value="kesehatan">Kesehatan Umum</option>
          <option value="gaya-hidup">Gaya Hidup</option>
          <option value="teknologi">Teknologi Medis</option>
          <option value="tips">Tips & Trik</option>
        </select>
      </div>

      <!-- Blog Posts Grid -->
      <div v-if="filteredPosts.length > 0" class="grid md:grid-cols-2 gap-8 mb-12">
        <article
          v-for="post in filteredPosts"
          :key="post.id"
          class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden group cursor-pointer"
        >
          <!-- Featured Image -->
          <div class="h-48 bg-linear-to-br from-indigo-400 to-purple-500 overflow-hidden relative">
            <div class="w-full h-full flex items-center justify-center text-4xl group-hover:scale-110 transition duration-300">
              {{ post.icon }}
            </div>
          </div>

          <!-- Content -->
          <div class="p-6">
            <!-- Category & Date -->
            <div class="flex items-center justify-between mb-3">
              <span class="text-xs font-bold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">
                {{ post.category }}
              </span>
              <span class="text-xs text-gray-500">{{ formatDate(post.date) }}</span>
            </div>

            <!-- Title -->
            <h2 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-indigo-600 transition">
              {{ post.title }}
            </h2>

            <!-- Description -->
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
              {{ post.description }}
            </p>

            <!-- Author & Read More -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                <span class="text-xs text-gray-600">{{ post.author }}</span>
              </div>
              <span class="text-indigo-600 font-semibold text-sm group-hover:translate-x-1 transition">
                Baca →
              </span>
            </div>
          </div>
        </article>
      </div>

      <!-- No Results -->
      <div v-else class="text-center py-12">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak ada artikel ditemukan</h3>
        <p class="text-gray-600">Coba ubah filter pencarian Anda</p>
      </div>

      <!-- Load More / Pagination -->
      <div class="text-center pt-8 border-t border-gray-200">
        <button class="px-8 py-3 border-2 border-indigo-600 text-indigo-600 font-bold rounded-lg hover:bg-indigo-50 transition">
          Muat Lebih Banyak
        </button>
      </div>
    </div>

    <!-- Newsletter CTA -->
    <div class="bg-indigo-50 py-12 px-4 mt-12">
      <div class="max-w-2xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Jangan Lewatkan Artikel Terbaru</h2>
        <p class="text-gray-700 mb-6">Daftarkan email Anda untuk menerima tips kesehatan dan artikel terbaru langsung ke inbox Anda.</p>
        <div class="flex gap-2 max-w-md mx-auto">
          <input
            type="email"
            placeholder="Email Anda"
            class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
          <button class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition">
            Daftar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedCategory = ref('')

const blogPosts = ref([
  {
    id: 1,
    title: 'Pentingnya Konsultasi Rutin dengan Dokter',
    description: 'Pemeriksaan kesehatan rutin dapat membantu mendeteksi penyakit lebih awal dan mencegah komplikasi yang lebih serius.',
    category: 'kesehatan',
    author: 'Dr. Angguni Ramadhani',
    date: '2025-12-20',
    icon: '🏥'
  },
  {
    id: 2,
    title: 'Tips Menjaga Kesehatan Mental di Era Digital',
    description: 'Kesehatan mental sama pentingnya dengan kesehatan fisik. Pelajari cara menjaganya di tengah kesibukan digital.',
    category: 'gaya-hidup',
    author: 'Dr. Siti Nurhaliza',
    date: '2025-12-18',
    icon: '🧠'
  },
  {
    id: 3,
    title: 'Panduan Lengkap Telemedicine untuk Pemula',
    description: 'Tidak tahu cara menggunakan telemedicine? Baca panduan lengkap kami untuk memulai konsultasi online dengan mudah.',
    category: 'teknologi',
    author: 'Tim Editorial',
    date: '2025-12-15',
    icon: '💻'
  },
  {
    id: 4,
    title: '5 Kebiasaan Sehat yang Mudah Dilakukan Sehari-hari',
    description: 'Mulai dari hal kecil, berikut adalah 5 kebiasaan sehat yang bisa Anda lakukan setiap hari untuk hidup lebih sehat.',
    category: 'tips',
    author: 'Dr. Budi Santoso',
    date: '2025-12-12',
    icon: '💪'
  },
  {
    id: 5,
    title: 'Manfaat Teknologi AI dalam Diagnosis Medis',
    description: 'Teknologi kecerdasan buatan kini membantu dokter dalam melakukan diagnosis yang lebih akurat dan cepat.',
    category: 'teknologi',
    author: 'Prof. Dr. Ahmad Wijaya',
    date: '2025-12-10',
    icon: '🤖'
  },
  {
    id: 6,
    title: 'Cara Mengatur Pola Makan Sehat saat Bekerja dari Rumah',
    description: 'Bekerja dari rumah sering membuat pola makan berantakan. Berikut tips agar tetap menjaga nutrisi.',
    category: 'gaya-hidup',
    author: 'Ahli Gizi Maria',
    date: '2025-12-08',
    icon: '🥗'
  }
])

const filteredPosts = computed(() => {
  return blogPosts.value.filter(post => {
    const matchesSearch = post.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         post.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !selectedCategory.value || post.category === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
