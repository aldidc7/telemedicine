<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Loading indicator saat auth initializing -->
    <div v-if="!authStore.initialized" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin w-12 h-12 border-4 border-gray-300 border-t-indigo-600 rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600 text-sm">Memuat...</p>
      </div>
    </div>

    <!-- Navbar - Authenticated -->
    <Navbar v-if="authStore.isAuthenticated && authStore.initialized" />
    <!-- Navbar - Unauthenticated (Landing) -->
    <LandingNavbar v-else-if="!authStore.isAuthenticated && authStore.initialized" />

    <!-- Main Content -->
    <main v-if="authStore.initialized" :class="['transition-all', authStore.isAuthenticated ? 'p-4 md:p-8' : '']">
      <div :class="[authStore.isAuthenticated ? 'max-w-7xl mx-auto' : '']">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import LandingNavbar from '@/components/LandingNavbar.vue'

const authStore = useAuthStore()
const router = useRouter()

// Initialization sudah di handle oleh router guard dengan timeout
</script>

<style scoped>
#app {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
</style>