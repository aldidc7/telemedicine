<!-- 📁 resources/js/views/NotFoundPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <!-- Animated Illustration -->
      <div class="mb-8 flex justify-center">
        <div class="relative w-40 h-40">
          <svg
            class="w-full h-full text-indigo-200 animate-pulse"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1"
              d="M9.172 16.172a4 4 0 015.656 0M9 10a4 4 0 018 0"
            />
            <circle cx="12" cy="12" r="10" stroke-width="1" />
          </svg>
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-6xl font-black text-indigo-600">404</span>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="text-center space-y-4 mb-8">
        <h1 class="text-4xl font-black text-gray-900">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-600 text-lg">Maaf, halaman yang Anda cari tidak tersedia. Mungkin sudah dihapus atau URL-nya tidak valid.</p>
      </div>

      <!-- Suggested Links -->
      <div class="space-y-3 mb-8">
        <router-link
          to="/"
          class="block w-full px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 text-white rounded-2xl hover:shadow-lg font-bold transition text-center"
        >
          🏠 Kembali ke Beranda
        </router-link>
        <router-link
          to="/cari-dokter"
          class="block w-full px-6 py-3 bg-white text-indigo-600 rounded-2xl hover:shadow-lg font-bold transition text-center border-2 border-indigo-200"
        >
          🔍 Cari Dokter
        </router-link>
      </div>

      <!-- Help Text -->
      <p class="text-center text-sm text-gray-500">
        Butuh bantuan? <router-link to="/settings" class="text-indigo-600 font-bold hover:underline">Hubungi Dukungan</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
