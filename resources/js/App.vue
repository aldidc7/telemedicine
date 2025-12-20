<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Loading indicator saat auth initializing -->
    <div v-if="!authStore.initialized" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin w-12 h-12 border-4 border-gray-300 border-t-indigo-600 rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600 text-sm">Memuat...</p>
      </div>
    </div>

    <!-- Profile Completion Modal - Block Access Until Complete -->
    <ProfileCompletionModal ref="profileCompletionModal" />

    <!-- Logout Confirmation Modal -->
    <LogoutModal ref="logoutModal" />

    <!-- Informed Consent Dialog - Show jika consent belum accepted -->
    <ConsentDialog 
      v-if="authStore.isAuthenticated && authStore.consentRequired && !authStore.hasConsent"
      :is-open="showConsentDialog"
      @consent-complete="handleConsentComplete"
    />

    <!-- Navbar - Authenticated -->
    <Navbar 
      v-if="authStore.isAuthenticated && authStore.initialized"
      @logout="showLogoutModal"
    />
    <!-- Navbar - Unauthenticated (Landing) -->
    <LandingNavbar v-else-if="!authStore.isAuthenticated && authStore.initialized" />

    <!-- WebSocket Status Indicator -->
    <div v-if="authStore.isAuthenticated && authStore.initialized" class="fixed top-4 left-4 z-40">
      <WebSocketStatus />
    </div>

    <!-- Real-time Notifications -->
    <RealtimeNotifications v-if="authStore.isAuthenticated && authStore.initialized" />

    <!-- Main Content -->
    <main v-if="authStore.initialized" :class="['transition-all', authStore.isAuthenticated ? 'p-4 md:p-8' : '']">
      <div :class="[authStore.isAuthenticated ? 'max-w-7xl mx-auto' : '']">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import LandingNavbar from '@/components/LandingNavbar.vue'
import WebSocketStatus from '@/components/WebSocketStatus.vue'
import RealtimeNotifications from '@/components/RealtimeNotifications.vue'
import ConsentDialog from '@/components/ConsentDialog.vue'
import ProfileCompletionModal from '@/components/ProfileCompletionModal.vue'
import LogoutModal from '@/components/LogoutModal.vue'

const authStore = useAuthStore()
const router = useRouter()
const profileCompletionModal = ref(null)
const logoutModal = ref(null)

const showConsentDialog = computed(() => {
  return authStore.isAuthenticated && authStore.consentRequired && !authStore.hasConsent
})

const handleConsentComplete = async () => {
  // Consent telah diterima, reload user data untuk update consent status
  try {
    await authStore.checkConsentStatus()
  } catch (err) {
    console.error('Error refreshing consent status:', err)
  }
}

const showLogoutModal = () => {
  if (logoutModal.value) {
    logoutModal.value.show()
  }
}

onMounted(async () => {
  // Check consent status jika user sudah login
  if (authStore.isAuthenticated) {
    await authStore.checkConsentStatus()
  }
})
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