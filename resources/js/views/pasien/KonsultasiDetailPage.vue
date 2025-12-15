<!-- 📁 resources/js/views/pasien/KonsultasiDetailPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Back Button - Enhanced -->
      <router-link to="/konsultasi" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-bold mb-8 transition group">
        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Daftar</span>
      </router-link>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Not Found -->
      <div v-else-if="!konsultasi" class="text-center py-12">
        <p class="text-gray-500 text-lg">Konsultasi tidak ditemukan</p>
      </div>

      <!-- Detail Content -->
      <div v-else class="space-y-6">
        <!-- Header Card - Enhanced -->
        <div class="bg-linear-to-br from-indigo-50 via-white to-purple-50 rounded-3xl p-8 shadow-lg border border-indigo-100 overflow-hidden relative">
          <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-200 opacity-10 rounded-full"></div>
          <div class="relative z-10">
            <div class="flex items-start justify-between gap-6 mb-8 flex-col md:flex-row">
              <div class="flex items-start gap-6 flex-1 w-full">
                <div class="w-20 h-20 rounded-3xl bg-linear-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-3xl shadow-xl shrink-0 ring-4 ring-indigo-200">
                  {{ konsultasi.dokter?.pengguna?.name?.charAt(0).toUpperCase() || 'D' }}
                </div>
                <div class="flex-1">
                  <h1 class="text-4xl font-black text-gray-900">Dr. {{ konsultasi.dokter?.pengguna?.name }}</h1>
                  <p class="text-indigo-600 font-bold text-lg mt-2">{{ konsultasi.dokter?.specialization || 'Dokter Umum' }}</p>
                  <!-- Availability Status -->
                  <div :class="['inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-full text-sm font-bold',
                    konsultasi.dokter?.is_available || konsultasi.dokter?.tersedia
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-700'
                  ]">
                    <span class="text-lg">{{ konsultasi.dokter?.is_available || konsultasi.dokter?.tersedia ? '🟢' : '🔴' }}</span>
                    <span>{{ konsultasi.dokter?.is_available || konsultasi.dokter?.tersedia ? 'Online' : 'Offline' }}</span>
                  </div>
                  <div class="flex flex-wrap gap-2 mt-4">
                    <span class="text-xs px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full font-semibold">⭐ Spesialis</span>
                    <span class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-full font-semibold">✓ Terverifikasi</span>
                  </div>
                </div>
              </div>
              <span :class="['text-sm px-5 py-2.5 rounded-full font-bold shrink-0 whitespace-nowrap',
                konsultasi.status === 'closed' ? 'bg-green-100 text-green-700' :
                konsultasi.status === 'active' ? 'bg-blue-100 text-blue-700' :
                konsultasi.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                'bg-red-100 text-red-700'
              ]">
                {{ konsultasi.status === 'closed' ? '✓ Selesai' : 
                   konsultasi.status === 'active' ? '⚡ Aktif' :
                   konsultasi.status === 'pending' ? '⏱ Menunggu' : '✕ Ditolak' }}
              </span>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="bg-white bg-opacity-70 backdrop-blur-sm rounded-2xl p-5 border border-indigo-100 hover:shadow-md transition">
                <p class="text-gray-600 font-bold text-xs uppercase tracking-widest">Jenis Layanan</p>
                <p class="text-gray-900 font-bold text-lg mt-3">
                  {{ konsultasi.tipe_layanan === 'video' ? '📹 Video Call' : 
                     konsultasi.tipe_layanan === 'chat' ? '💬 Chat Text' : '📞 Telepon' }}
                </p>
              </div>
              <div class="bg-white bg-opacity-70 backdrop-blur-sm rounded-2xl p-5 border border-indigo-100 hover:shadow-md transition">
                <p class="text-gray-600 font-bold text-xs uppercase tracking-widest">Tanggal Dibuat</p>
                <p class="text-gray-900 font-bold text-lg mt-3">{{ new Date(konsultasi.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
              </div>
              <div class="bg-white bg-opacity-70 backdrop-blur-sm rounded-2xl p-5 border border-indigo-100 hover:shadow-md transition">
                <p class="text-gray-600 font-bold text-xs uppercase tracking-widest">Waktu Tunggu</p>
                <p class="text-gray-900 font-bold text-lg mt-3">{{ Math.floor((Date.now() - new Date(konsultasi.created_at)) / 3600000) }}h</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Complaint Card - Enhanced -->
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-14 h-14 bg-linear-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-3xl font-black text-gray-900">Detail Keluhan</h2>
          </div>
          
          <div class="space-y-6">
            <div class="bg-linear-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border-2 border-indigo-100">
              <p class="text-indigo-600 font-bold text-xs uppercase tracking-widest mb-3">📌 Jenis Keluhan</p>
              <p class="text-gray-900 font-bold text-xl leading-relaxed">{{ konsultasi.complaint_type || konsultasi.jenis_keluhan }}</p>
            </div>
            <div class="bg-linear-to-br from-slate-50 to-gray-50 rounded-2xl p-6 border-2 border-gray-200">
              <p class="text-gray-600 font-bold text-xs uppercase tracking-widest mb-3">📝 Deskripsi Lengkap</p>
              <p class="text-gray-700 font-medium leading-relaxed whitespace-pre-wrap text-base">{{ konsultasi.description || konsultasi.deskripsi }}</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons - Enhanced -->
        <div class="sticky bottom-6 flex gap-4 flex-col sm:flex-row">
          <router-link
            v-if="konsultasi.status === 'active' || konsultasi.status === 'pending'"
            :to="`/chat/${konsultasi.id}`"
            class="flex-1 bg-linear-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white px-6 py-4 rounded-2xl font-bold transition shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>💬 Buka Chat Konsultasi</span>
          </router-link>
          <router-link
            to="/konsultasi"
            class="flex-1 bg-white hover:bg-gray-50 text-gray-900 px-6 py-4 rounded-2xl font-bold transition shadow-md hover:shadow-lg border-2 border-gray-200 hover:border-gray-300 flex items-center justify-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { konsultasiAPI } from '@/api/konsultasi'

const route = useRoute()
const konsultasi = ref(null)
const loading = ref(false)

onMounted(async () => {
  loading.value = true
  try {
    const response = await konsultasiAPI.getDetail(route.params.id)
    konsultasi.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading konsultasi detail:', error)
  } finally {
    loading.value = false
  }
})
</script>
