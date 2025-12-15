<!-- 📁 resources/js/views/auth/RegisterChoosePage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-indigo-600 mb-2">Telemedicine</h1>
        <p class="text-gray-600">RSUD dr. R. Soedarsono</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-8">Pilih Tipe Akun</h2>

        <!-- Pasien Option -->
        <router-link
          to="/register/pasien"
          class="block mb-4 p-6 border-2 border-gray-200 rounded-lg hover:border-blue-600 hover:bg-blue-50 transition duration-200"
        >
          <div class="flex items-center">
            <div class="text-4xl mr-4">👤</div>
            <div>
              <h3 class="text-xl font-bold text-gray-800">Pasien</h3>
              <p class="text-sm text-gray-600">Daftar sebagai pasien untuk konsultasi dengan dokter</p>
            </div>
          </div>
        </router-link>

        <!-- Dokter Option -->
        <router-link
          to="/register/dokter"
          class="block p-6 border-2 border-gray-200 rounded-lg hover:border-green-600 hover:bg-green-50 transition duration-200"
        >
          <div class="flex items-center">
            <div class="text-4xl mr-4">👨‍⚕️</div>
            <div>
              <h3 class="text-xl font-bold text-gray-800">Dokter</h3>
              <p class="text-sm text-gray-600">Daftar sebagai dokter untuk memberikan layanan konsultasi</p>
            </div>
          </div>
        </router-link>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-300"></div>
          <span class="px-3 text-gray-500 text-sm">atau</span>
          <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <!-- Login Link -->
        <p class="text-center text-gray-600">
          Sudah punya akun?
          <router-link to="/login" class="text-indigo-600 font-medium hover:underline">
            Login di sini
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
