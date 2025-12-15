<template>
  <nav class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <router-link
          to="/"
          class="flex items-center space-x-2 font-bold text-xl bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent hover:from-indigo-700 hover:to-purple-700 transition"
        >
          <div class="w-8 h-8 bg-linear-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m0 0V3a2 2 0 00-2-2h-2a2 2 0 00-2 2v2z" />
            </svg>
          </div>
          <span>Telemedicine</span>
        </router-link>

        <!-- Menu Desktop -->
        <div class="hidden md:flex items-center space-x-4">
          <router-link
            to="/"
            class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-indigo-600 transition"
          >
            Beranda
          </router-link>
          <router-link
            to="/login"
            class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-indigo-600 transition"
          >
            Masuk
          </router-link>
          <router-link
            to="/register/pasien"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-md hover:shadow-lg"
          >
            Daftar
          </router-link>
        </div>

        <!-- Menu Mobile -->
        <div class="md:hidden flex items-center space-x-2">
          <router-link
            to="/login"
            class="px-3 py-2 text-sm font-semibold text-gray-700 hover:text-indigo-600"
          >
            Masuk
          </router-link>
          <router-link
            to="/register/pasien"
            class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold"
          >
            Daftar
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>
