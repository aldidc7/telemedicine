<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-10">
        <h1 class="text-5xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
          Telemedicine
        </h1>
        <p class="text-gray-500 text-sm">Verifikasi Kode OTP</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Kode OTP</h2>
        <p class="text-gray-600 text-sm mb-6">Masukkan kode 6-digit yang dikirim ke WhatsApp Anda</p>

        <!-- Phone Number Display -->
        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
          <p class="text-sm text-indigo-900">
            <strong>Kode dikirim ke nomor WhatsApp:</strong><br>
            <span class="text-indigo-700 font-mono">{{ phoneNumberDisplay }}</span>
          </p>
        </div>

        <!-- Alert Error -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
          <p class="text-red-700 text-sm font-medium">{{ error }}</p>
        </div>

        <!-- Alert Info -->
        <div v-if="remainingTime" class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
          <p class="text-yellow-700 text-sm font-medium">
            Kode berlaku selama <strong>{{ remainingTime }} menit</strong>
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleVerify" class="space-y-5" v-if="!success">
          <!-- OTP Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Kode OTP</label>
            <input
              v-model="form.otp"
              type="text"
              inputmode="numeric"
              maxlength="6"
              required
              placeholder="000000"
              class="w-full px-4 py-3 text-center text-2xl font-mono tracking-widest border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              @input="sanitizeOtp"
            />
            <p class="text-xs text-gray-500 mt-2">Masukkan 6 digit tanpa spasi</p>
          </div>

          <!-- Submit Button -->
          <button
            :disabled="isLoading || form.otp.length !== 6"
            type="submit"
            class="w-full bg-linear-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-bold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed mt-6"
          >
            {{ isLoading ? 'Memverifikasi...' : 'Verifikasi Kode' }}
          </button>
        </form>

        <!-- Success Message -->
        <div v-if="success" class="text-center">
          <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
            <p class="text-green-700 text-sm font-medium">Kode berhasil diverifikasi!</p>
          </div>
          <p class="text-gray-600 text-sm">Anda akan dialihkan ke halaman reset password...</p>
        </div>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-200"></div>
          <span class="px-3 text-gray-400 text-sm">atau</span>
          <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <!-- Resend Code Section -->
        <div class="space-y-3">
          <p class="text-center text-gray-600 text-sm">
            <span v-if="canResend">
              Tidak menerima kode?
              <button
                @click="handleResendOtp"
                :disabled="isResending"
                type="button"
                class="text-indigo-600 font-bold hover:text-indigo-700 transition disabled:opacity-50"
              >
                Kirim ulang
              </button>
            </span>
            <span v-else class="text-gray-500">
              Kirim ulang dalam <strong>{{ resendCountdown }} detik</strong>
            </span>
          </p>

          <!-- Back Link -->
          <p class="text-center text-gray-600 text-sm">
            Ingin coba metode lain?
            <RouterLink to="/forgot-password" class="text-amber-600 font-bold hover:text-amber-700 transition">
              Kembali
            </RouterLink>
          </p>
        </div>
      </div>

      <!-- Info Box -->
      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 mx-4">
        <p class="text-xs text-blue-700">
          <strong>Keamanan:</strong> Jangan bagikan kode OTP ini dengan siapa pun. Telemedicine tidak akan pernah meminta kode Anda melalui chat atau telepon.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { RouterLink } from 'vue-router'
import { authApi } from '@/api/auth'

const router = useRouter()
const route = useRoute()
const isLoading = ref(false)
const isResending = ref(false)
const error = ref(null)
const success = ref(null)
const canResend = ref(true)
const resendCountdown = ref(0)
const phoneNumber = ref('')
const remainingTime = ref(30)

const form = ref({
  phone: '',
  otp: ''
})

const phoneNumberDisplay = computed(() => {
  if (!phoneNumber.value) return '••••••••••'
  const phone = phoneNumber.value.replace(/\D/g, '')
  if (phone.length > 0) {
    // Format: +62 888 **** 3781 (tampilkan awal dan 4 digit terakhir)
    const prefix = phone.slice(0, 3)  // 888
    const last4 = phone.slice(-4)      // 3781
    return `+62 ${prefix} **** ${last4}`
  }
  return '••••••••••'
})

const sanitizeOtp = () => {
  // Hanya izinkan angka
  form.value.otp = form.value.otp.replace(/\D/g, '').slice(0, 6)
}

const handleVerify = async () => {
  if (form.value.otp.length !== 6) {
    error.value = 'Kode OTP harus 6 digit'
    return
  }

  isLoading.value = true
  error.value = null

  try {
    const response = await authApi.verifyOtp({
      phone: form.value.phone,
      otp: form.value.otp
    })

    success.value = true

    // Store reset token in session/localStorage
    if (response.data?.reset_token) {
      sessionStorage.setItem('reset_token', response.data.reset_token)
    }

    // Redirect ke reset password page dengan token
    setTimeout(() => {
      router.push('/reset-password')
    }, 2000)
  } catch (err) {
    const errorMsg = err.response?.data?.message || err.response?.data?.pesan || 'Verifikasi OTP gagal'
    if (err.response?.status === 422) {
      error.value = 'Kode OTP salah atau sudah expired'
    } else if (err.response?.status === 429) {
      error.value = 'Terlalu banyak percobaan. Silakan coba lagi nanti.'
    } else {
      error.value = errorMsg
    }
    console.error('OTP verification error:', err)
  } finally {
    isLoading.value = false
  }
}

const handleResendOtp = async () => {
  isResending.value = true
  error.value = null

  try {
    await authApi.resendOtp({
      phone: form.value.phone
    })

    error.value = null
    canResend.value = false
    resendCountdown.value = 60
    remainingTime.value = 30

    // Countdown untuk resend
    const interval = setInterval(() => {
      resendCountdown.value--
      if (resendCountdown.value <= 0) {
        clearInterval(interval)
        canResend.value = true
      }
    }, 1000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal mengirim ulang kode'
    console.error('Resend OTP error:', err)
  } finally {
    isResending.value = false
  }
}

onMounted(() => {
  // Get phone dari route params atau query
  const phone = route.query.phone || route.params.phone || ''
  form.value.phone = phone
  phoneNumber.value = phone

  // Start countdown timer untuk remaining time
  const interval = setInterval(() => {
    remainingTime.value--
    if (remainingTime.value <= 0) {
      clearInterval(interval)
      error.value = 'Kode OTP sudah expired. Silakan minta kode baru.'
    }
  }, 60000) // Update setiap 1 menit (60000ms)

  // Cleanup interval pada unmount
  return () => clearInterval(interval)
})
</script>
