<template>
  <div>
    <nav class="sticky top-0 z-40 bg-white shadow-md border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <button
            @click="goHome"
            class="flex items-center space-x-2 font-bold text-xl bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent hover:from-indigo-700 hover:to-purple-700 transition cursor-pointer border-none p-0"
          >
            <div class="w-8 h-8 bg-linear-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m0 0V3a2 2 0 00-2-2h-2a2 2 0 00-2 2v2z" />
              </svg>
            </div>
            <span>Telemedicine</span>
          </button>

          <!-- Menu Desktop -->
          <div v-if="authStore.isAuthenticated" class="hidden md:flex items-center space-x-1">
            <!-- Pasien Menu -->
            <template v-if="authStore.isPasien">
              <router-link
                to="/dashboard"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition', 
                  isActive('/dashboard') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Dashboard
              </router-link>
              <router-link
                to="/cari-dokter"
                @click="showUserMenu = false; showMobileMenu = false"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/cari-dokter') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Cari Dokter
              </router-link>
              <router-link
                to="/konsultasi"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/konsultasi') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Konsultasi
              </router-link>
              <router-link
                to="/profile"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/profile') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Profil
              </router-link>
            </template>

            <!-- Dokter Menu -->
            <template v-else-if="authStore.isDokter">
              <router-link
                to="/dokter/dashboard"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/dokter/dashboard') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Dashboard
              </router-link>
              <router-link
                to="/dokter/konsultasi"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/dokter/konsultasi') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Konsultasi
              </router-link>
            </template>

            <!-- Admin Menu -->
            <template v-else-if="authStore.isAdmin">
              <router-link
                to="/admin/dashboard"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/admin/dashboard') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Dashboard
              </router-link>
              <router-link
                to="/admin/pasien"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/admin/pasien') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Pasien
              </router-link>
              <router-link
                to="/admin/dokter"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/admin/dokter') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Dokter
              </router-link>
              <router-link
                to="/admin/log"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/admin/log') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Log
              </router-link>
              <router-link
                to="/admin/statistik"
                :class="['px-4 py-2 rounded-lg text-sm font-semibold transition',
                  isActive('/admin/statistik') ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
              >
                Statistik
              </router-link>
            </template>
          </div>

          <!-- Availability Status Badge (Dokter Only - Display Only) -->
          <div v-if="authStore.isDokter" class="hidden md:flex items-center">
            <div :class="['px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2',
              isDocterAvailable 
                ? 'bg-green-100 text-green-700' 
                : 'bg-gray-100 text-gray-700'
            ]">
              <span class="text-lg">{{ isDocterAvailable ? '🟢' : '🔴' }}</span>
              <span>{{ isDocterAvailable ? 'Online' : 'Offline' }}</span>
            </div>
          </div>

          <!-- User Menu -->
          <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4 relative" data-user-menu>
            <!-- User Avatar & Name -->
            <div class="hidden md:flex items-center space-x-3 border-l border-gray-200 pl-4">
              <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500">
                  {{ authStore.isPasien ? 'Pasien' : authStore.isDokter ? 'Dokter' : 'Admin' }}
                </p>
              </div>
              <button
                @click="showUserMenu = !showUserMenu"
                class="w-10 h-10 rounded-full bg-linear-to-br from-indigo-500 to-purple-600 text-white font-bold flex items-center justify-center hover:shadow-lg transition active:scale-95 ring-2 ring-indigo-200 relative"
              >
                {{ getInitial(authStore.user?.name) }}
                <!-- Status Badge for Doctors -->
                <span v-if="authStore.isDokter" :class="['absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white',
                  isDocterAvailable ? 'bg-green-500 animate-pulse' : 'bg-gray-400'
                ]"></span>
              </button>
            </div>

            <!-- User Dropdown Menu - Enhanced -->
            <div v-if="showUserMenu" class="absolute top-16 right-0 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50">
              <!-- User Info Header -->
              <div class="px-4 py-3 border-b border-gray-100 bg-linear-to-r from-indigo-50 to-purple-50">
                <p class="text-sm font-bold text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-600 mt-1 flex items-center gap-1.5">
                  <svg v-if="authStore.isPasien" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12a4 4 0 110-8 4 4 0 010 8z"/><path d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <svg v-else-if="authStore.isDokter" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                  </svg>
                  <svg v-else class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                  <span>{{ authStore.isPasien ? 'Pasien' : authStore.isDokter ? 'Dokter' : 'Admin' }}</span>
                </p>
              </div>

              <!-- Menu Items -->
              <button
                @click="goToProfile"
                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 transition flex items-center gap-3 font-medium"
              >
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Lihat Profil</span>
              </button>

              <button
                @click="goToSettings"
                class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 transition flex items-center gap-3 font-medium"
              >
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Pengaturan</span>
              </button>

              <!-- Divider -->
              <div class="border-t border-gray-100 my-2"></div>

              <!-- Logout Button -->
              <button
                @click="openLogoutModal"
                class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-3 font-medium rounded-lg mx-1"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Logout</span>
              </button>
            </div>

            <!-- Mobile Menu Button -->
            <button
              @click="showMobileMenu = !showMobileMenu"
              class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>

          <!-- No Auth Menu -->
          <div v-else class="hidden md:flex items-center space-x-4">
            <router-link
              to="/login"
              class="text-sm font-semibold text-gray-700 hover:text-indigo-600 transition"
            >
              Login
            </router-link>
            <router-link
              to="/register"
              class="px-4 py-2 bg-linear-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg transition text-sm"
            >
              Daftar
            </router-link>
            
            <!-- Mobile Menu Button -->
            <button
              @click="showMobileMenu = !showMobileMenu"
              class="md:hidden text-gray-700 hover:text-indigo-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="showMobileMenu" class="md:hidden pb-4 space-y-2 border-t border-gray-100 pt-4">
          <template v-if="authStore.isAuthenticated">
            <template v-if="authStore.isPasien">
              <router-link to="/dashboard" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Dashboard
              </router-link>
              <router-link to="/cari-dokter" @click="showMobileMenu = false" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Cari Dokter
              </router-link>
              <router-link to="/konsultasi" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Konsultasi
              </router-link>
              <router-link to="/profile" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Profil
              </router-link>
            </template>
            <template v-else-if="authStore.isDokter">
              <router-link to="/dokter/dashboard" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Dashboard
              </router-link>
              <router-link to="/dokter/konsultasi" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Konsultasi
              </router-link>
              <router-link to="/dokter/profile" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Profil
              </router-link>
            </template>
            <template v-else-if="authStore.isAdmin">
              <router-link to="/admin/dashboard" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Dashboard
              </router-link>
              <router-link to="/admin/pasien" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Pasien
              </router-link>
              <router-link to="/admin/dokter" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Dokter
              </router-link>
              <router-link to="/admin/log" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Log
              </router-link>
              <router-link to="/admin/statistik" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
                Statistik
              </router-link>
            </template>
            <div class="border-t border-gray-100 my-2"></div>
            <button
              @click="openLogoutModal"
              class="w-full text-left px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
              <span>Logout</span>
            </button>
          </template>
          <template v-else>
            <router-link to="/login" class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-indigo-50 rounded transition">
              Login
            </router-link>
            <router-link to="/register" class="block px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 rounded transition">
              Daftar
            </router-link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Logout Confirmation Modal -->
    <Transition name="modal-fade">
      <div v-if="showLogoutModal" class="fixed inset-0 backdrop-blur-md flex items-center justify-center z-50">
        <Transition name="modal-scale">
          <div class="bg-white rounded-3xl p-8 max-w-sm shadow-2xl">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
              <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
              </div>
            </div>

            <!-- Title & Message -->
            <h3 class="text-2xl font-black text-gray-900 text-center mb-3">Yakin ingin logout?</h3>
            <p class="text-gray-600 text-center mb-8">Anda akan kembali ke halaman login dan session akan dihapus.</p>

            <!-- Buttons -->
            <div class="flex gap-4">
              <button
                @click="showLogoutModal = false"
                class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 font-bold rounded-xl transition"
              >
                Batal
              </button>
              <button
                @click="handleLogout"
                :disabled="isLoggingOut"
                class="flex-1 px-4 py-3 bg-linear-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:opacity-50 text-white font-bold rounded-xl transition flex items-center justify-center gap-2"
              >
                <svg v-if="isLoggingOut" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDokterAvailability } from '@/stores/dokterAvailability'
import { dokterAPI } from '@/api/dokter'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const dokterAvailabilityStore = useDokterAvailability()
const showMobileMenu = ref(false)
const showUserMenu = ref(false)
const showLogoutModal = ref(false)
const isLoggingOut = ref(false)
const toggleLoading = computed(() => dokterAvailabilityStore.isLoading)
const isDocterAvailable = computed(() => dokterAvailabilityStore.isAvailable)

// Close menu when clicking outside
const handleClickOutside = (e) => {
  const userMenuElement = document.querySelector('[data-user-menu]')
  if (userMenuElement && !userMenuElement.contains(e.target)) {
    showUserMenu.value = false
  }
}

// Check if doctor is available on mount
const checkDocterAvailability = async () => {
  if (!authStore.isDokter) return
  await dokterAvailabilityStore.fetchAvailability()
}

const isActive = (path) => {
  return route.path.startsWith(path)
}

const getInitial = (name) => {
  return name ? name.charAt(0).toUpperCase() : '?'
}

const openLogoutModal = () => {
  showUserMenu.value = false
  showMobileMenu.value = false
  showLogoutModal.value = true
}

const handleLogout = async () => {
  isLoggingOut.value = true
  try {
    await authStore.logout()
    showLogoutModal.value = false
    router.push('/')
  } catch (error) {
    console.error('Error during logout:', error)
    isLoggingOut.value = false
  }
}

const goToProfile = () => {
  showUserMenu.value = false
  if (authStore.isDokter) {
    router.push('/dokter/profile')
  } else {
    router.push('/profile')
  }
}

const goToSettings = () => {
  showUserMenu.value = false
  if (authStore.isDokter) {
    router.push('/dokter/settings')
  } else if (authStore.isPasien) {
    router.push('/settings')
  } else if (authStore.isAdmin) {
    router.push('/admin/settings')
  }
}

const goHome = () => {
  router.push('/')
  window.scrollTo(0, 0)
}

onMounted(() => {
  // Cek ketersediaan dokter - non-blocking
  // Delay sedikit agar tidak mengganggu initial render
  setTimeout(() => {
    checkDocterAvailability()
  }, 500)
  
  // Add global click listener for closing user menu
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  // Remove event listener when component unmounts
  document.removeEventListener('click', handleClickOutside)
})
</script>
<style scoped>
/* Modal Fade Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Modal Scale Transitions */
.modal-scale-enter-active,
.modal-scale-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-scale-enter-from,
.modal-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>