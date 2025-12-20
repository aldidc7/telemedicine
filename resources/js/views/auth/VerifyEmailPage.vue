<template>
  <div class="min-h-screen bg-linear-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <!-- Status: Waiting for verification -->
        <div v-if="!verificationStep" class="text-center">
          <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Verifikasi Email</h1>
            <p class="text-gray-600 text-sm">Silakan cek email Anda untuk link verifikasi</p>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-900 text-sm">
              <strong>{{ email }}</strong><br>
              Link verifikasi akan expire dalam 24 jam
            </p>
          </div>

          <!-- Tips -->
          <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-gray-900 mb-3 text-sm">💡 Tips:</h3>
            <ul class="text-xs text-gray-600 space-y-2">
              <li>✓ Cek folder Spam jika tidak menemukan email</li>
              <li>✓ Link verifikasi berlaku 24 jam</li>
              <li>✓ Gunakan browser yang sama untuk verifikasi</li>
            </ul>
          </div>

          <!-- Button: Resend Email -->
          <button
            @click="resendEmail"
            :disabled="isResending || resendCountdown > 0"
            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed mb-3"
          >
            {{ resendCountdown > 0 ? `Kirim Ulang (${resendCountdown}s)` : isResending ? 'Mengirim...' : 'Kirim Ulang Email' }}
          </button>

          <!-- Button: Already Verified -->
          <button
            @click="verificationStep = 'submit-token'"
            class="w-full border-2 border-indigo-600 text-indigo-600 py-3 rounded-lg font-semibold hover:bg-indigo-50 transition"
          >
            Saya Sudah Verify, Lanjut →
          </button>

          <!-- Back to Login -->
          <router-link
            to="/login"
            class="block text-center text-gray-500 hover:text-gray-700 text-xs mt-4 transition"
          >
            ← Kembali ke login
          </router-link>
        </div>

        <!-- Step 2: Submit Token Manually -->
        <div v-else-if="verificationStep === 'submit-token'" class="text-center">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Masukkan Token Verifikasi</h2>
          <p class="text-gray-600 text-sm mb-6">Paste token dari link email Anda</p>

          <form @submit.prevent="submitToken" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Token Verifikasi</label>
              <input
                v-model="token"
                type="text"
                required
                placeholder="Paste token dari email"
                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50"
              />
              <p class="text-xs text-gray-500 mt-2">Token adalah string panjang dari link verifikasi email</p>
            </div>

            <!-- Error -->
            <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-700 text-sm font-medium">{{ error }}</p>
            </div>

            <!-- Loading -->
            <button
              :disabled="isSubmitting"
              type="submit"
              class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isSubmitting ? 'Verifikasi...' : 'Verifikasi Email' }}
            </button>

            <button
              type="button"
              @click="verificationStep = null"
              class="w-full border-2 border-gray-200 text-gray-600 py-3 rounded-lg font-semibold hover:border-gray-300 transition"
            >
              ← Kembali
            </button>
          </form>
        </div>

        <!-- Success -->
        <div v-else-if="verificationStep === 'success'" class="text-center">
          <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Email Terverifikasi!</h2>
            <p class="text-gray-600 mb-6">Anda sekarang bisa login ke akun Anda</p>
          </div>

          <router-link
            to="/login"
            class="block w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition text-center"
          >
            Lanjut ke Login →
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authApi } from '@/api/auth'

const router = useRouter()
const route = useRoute()

const email = ref('')
const token = ref('')
const error = ref(null)
const isSubmitting = ref(false)
const isResending = ref(false)
const resendCountdown = ref(0)
const verificationStep = ref(null)

// Get email dari query params
onMounted(() => {
  email.value = route.query.email || ''
  
  // Check if token is provided
  if (route.query.token) {
    token.value = route.query.token
    verificationStep.value = 'submit-token'
    submitToken()
  }
})

// Countdown timer
const startResendCountdown = () => {
  resendCountdown.value = 60
  const interval = setInterval(() => {
    resendCountdown.value--
    if (resendCountdown.value <= 0) {
      clearInterval(interval)
    }
  }, 1000)
}

// Resend verification email
const resendEmail = async () => {
  if (!email.value) {
    error.value = 'Email tidak ditemukan'
    return
  }

  isResending.value = true
  error.value = null

  try {
    const response = await authApi.resendVerification({ email: email.value })
    startResendCountdown()
    // Show success toast or message
    console.log('Email resent successfully')
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal mengirim email. Coba lagi.'
    console.error('Resend error:', err)
  } finally {
    isResending.value = false
  }
}

// Submit token
const submitToken = async () => {
  if (!token.value) {
    error.value = 'Token tidak boleh kosong'
    return
  }

  isSubmitting.value = true
  error.value = null

  try {
    await authApi.verifyEmailConfirm({ token: token.value })
    verificationStep.value = 'success'
    
    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Token tidak valid atau sudah expired'
    console.error('Verification error:', err)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
/* Add any additional styles here if needed */
</style>
