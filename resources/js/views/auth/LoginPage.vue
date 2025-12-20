<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-10">
        <h1 class="text-5xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
          Telemedicine
        </h1>
        <p class="text-gray-500 text-sm">Platform Konsultasi Kesehatan Digital</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Masuk ke Akun Anda</h2>
        <p class="text-gray-600 text-sm mb-6">Silakan login untuk melanjutkan</p>

        <!-- Alert Error -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-red-700 text-sm font-medium">{{ error }}</p>
              <p v-if="emailNotVerified" class="text-red-600 text-xs mt-2">
                Email Anda belum diverifikasi. Silakan cek inbox email Anda untuk link verifikasi.
              </p>
            </div>
          </div>
        </div>

        <!-- Email Verification Required Alert -->
        <div v-if="emailNotVerified" class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
          <p class="text-yellow-700 text-sm font-medium mb-3">Email Belum Diverifikasi</p>
          <p class="text-yellow-600 text-xs mb-4">
            Anda telah login tetapi email belum diverifikasi. Silakan verifikasi email Anda untuk mengakses fitur lengkap.
          </p>
          <button
            @click="redirectToEmailVerification"
            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm underline"
          >
            Verifikasi Email Sekarang →
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Login Input (Email atau NIK) -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Email atau NIK</label>
            <input
              v-model="form.identifier"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan email atau NIK Anda"
            />
            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
              <svg class="w-3 h-3 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
              </svg>
              Pasien: gunakan NIK (16 digit) atau email | Dokter: gunakan email
            </p>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white pr-10"
                placeholder="Masukkan password Anda"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition"
                :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m7.538 1.25a3 3 0 00-5.396-1.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            :disabled="isLoading"
            type="submit"
            class="w-full bg-linear-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-bold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed mt-6"
          >
            {{ isLoading ? 'Sedang login...' : 'Masuk' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-200"></div>
          <span class="px-3 text-gray-400 text-sm">atau</span>
          <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <!-- Register Link -->
        <p class="text-center text-gray-600 text-sm">
          Belum punya akun?
          <RouterLink to="/register" class="text-indigo-600 font-bold hover:text-indigo-700 transition">
            Daftar sekarang
          </RouterLink>
        </p>

        <!-- Back Home -->
        <router-link
          to="/"
          class="block text-center text-gray-500 hover:text-gray-700 text-xs mt-4 transition"
        >
          ← Kembali ke halaman awal
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'

const router = useRouter()
const authStore = useAuthStore()
const isLoading = ref(false)
const error = ref(null)
const emailNotVerified = ref(false)
const showPassword = ref(false)
const userEmail = ref('')

const form = ref({
  identifier: '',
  password: ''
})

const handleLogin = async () => {
  isLoading.value = true
  error.value = null
  emailNotVerified.value = false

  try {
    await authStore.login(form.value.identifier, form.value.password)
    userEmail.value = form.value.identifier
    
    // Check if email verification is required (for dokter/admin)
    if (!authStore.user?.email_verified_at && (authStore.isDokter || authStore.isAdmin)) {
      emailNotVerified.value = true
      error.value = 'Email Anda belum diverifikasi. Silakan verifikasi email terlebih dahulu.'
      return
    }

    // Check if consent is required
    if (authStore.consentRequired && authStore.user?.role !== 'admin') {
      router.push('/konsultasi/informed-consent')
      return
    }
    
    // Redirect berdasarkan role
    if (authStore.isDokter) {
      router.push('/dokter/dashboard')
    } else if (authStore.isAdmin) {
      router.push('/admin/dashboard')
    } else {
      router.push('/dashboard')
    }
  } catch (err) {
    const errorCode = err.response?.data?.code
    const errorMessage = err.response?.data?.message || err.response?.data?.pesan

    // Handle specific error codes
    if (errorCode === 'EMAIL_NOT_VERIFIED') {
      emailNotVerified.value = true
      error.value = 'Email belum diverifikasi. Silakan cek email Anda untuk link verifikasi.'
      userEmail.value = form.value.identifier
    } else {
      error.value = errorMessage || err.message || 'Login gagal, coba lagi'
    }
    
    console.error('Login error:', err)
  } finally {
    isLoading.value = false
  }
}

const redirectToEmailVerification = () => {
  router.push({
    name: 'verify-email',
    query: { email: userEmail.value }
  })
}
</script>
