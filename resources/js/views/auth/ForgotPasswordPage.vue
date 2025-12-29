<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-10">
        <h1 class="text-5xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
          Telemedicine
        </h1>
        <p class="text-gray-500 text-sm">Lupa Password?</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Reset Password</h2>
        <p class="text-gray-600 text-sm mb-6">Pilih metode untuk menerima kode reset password</p>

        <!-- Alert Error -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
          <p class="text-red-700 text-sm font-medium">{{ error }}</p>
        </div>

        <!-- Alert Success -->
        <div v-if="success" class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
          <p class="text-green-700 text-sm font-medium">{{ success }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5" v-if="!success">
          <!-- Method Selection -->
          <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm font-semibold text-gray-900">Pilih Metode Pengiriman:</p>
            
            <label class="flex items-center gap-3 cursor-pointer hover:bg-white p-3 rounded-lg transition">
              <input v-model="form.method" type="radio" value="email" class="w-4 h-4 accent-indigo-600">
              <span class="text-sm font-medium text-gray-700">Email</span>
            </label>
            
            <label class="flex items-center gap-3 cursor-pointer hover:bg-white p-3 rounded-lg transition">
              <input v-model="form.method" type="radio" value="whatsapp" class="w-4 h-4 accent-indigo-600">
              <span class="text-sm font-medium text-gray-700">WhatsApp</span>
            </label>
          </div>

          <!-- Email Input -->
          <div v-if="form.method === 'email'">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan email Anda"
            />
          </div>

          <!-- Phone Input for WhatsApp -->
          <div v-if="form.method === 'whatsapp'">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Nomor WhatsApp</label>
            <input
              v-model="form.phone"
              type="tel"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Contoh: 08123456789 atau +628123456789"
            />
            <p class="text-xs text-gray-500 mt-2">Nomor harus berawalan 08 atau +62</p>
          </div>

          <!-- Submit Button -->
          <button
            :disabled="isLoading || (form.method === 'email' && !form.email) || (form.method === 'whatsapp' && !form.phone)"
            type="submit"
            class="w-full bg-linear-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-bold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed mt-6"
          >
            {{ isLoading ? 'Mengirim...' : 'Kirim Kode Reset' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-200"></div>
          <span class="px-3 text-gray-400 text-sm">atau</span>
          <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <!-- Back to Login -->
        <p class="text-center text-gray-600 text-sm">
          Ingat password Anda?
          <RouterLink to="/login" class="text-indigo-600 font-bold hover:text-indigo-700 transition">
            Masuk di sini
          </RouterLink>
        </p>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-xs text-blue-700">
            <strong>💡 Info:</strong>
            <span v-if="form.method === 'email'">Link reset password akan dikirim ke email Anda. Link berlaku selama 2 jam.</span>
            <span v-else>Kode reset password akan dikirim ke WhatsApp Anda. Kode berlaku selama 30 menit.</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { RouterLink } from 'vue-router'
import { authApi } from '@/api/auth'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const isLoading = ref(false)
const error = ref(null)
const success = ref(null)

const form = ref({
  email: '',
  phone: '',
  method: 'email'
})

const validatePhoneNumber = (phone) => {
  const cleaned = phone.replace(/\D/g, '')
  // Must be 10-13 digits for Indonesian phone
  return cleaned.length >= 10 && cleaned.length <= 13
}

const handleSubmit = async () => {
  isLoading.value = true
  error.value = null
  success.value = null

  // Validate based on method
  if (form.value.method === 'email' && !form.value.email) {
    error.value = 'Silakan masukkan email Anda'
    isLoading.value = false
    return
  }

  if (form.value.method === 'whatsapp' && !form.value.phone) {
    error.value = 'Silakan masukkan nomor WhatsApp Anda'
    isLoading.value = false
    return
  }

  if (form.value.method === 'whatsapp' && !validatePhoneNumber(form.value.phone)) {
    error.value = 'Nomor WhatsApp tidak valid. Gunakan format 08xx atau +628xx'
    isLoading.value = false
    return
  }

  try {
    // Prepare payload based on method
    const payload = {
      method: form.value.method
    }
    
    if (form.value.method === 'email') {
      payload.email = form.value.email
    } else {
      payload.phone = form.value.phone
      payload.email = form.value.email || '' // Optional untuk WhatsApp
    }

    await authApi.forgotPassword(payload)
    
    if (form.value.method === 'email') {
      success.value = `Link reset password telah dikirim ke ${form.value.email}. Silakan cek email Anda (jangan lupa folder spam). Link berlaku selama 2 jam.`
    } else {
      success.value = `Kode reset password telah dikirim ke WhatsApp Anda. Kode berlaku selama 30 menit. Periksa pesan WhatsApp Anda.`
      // Redirect ke verify OTP page setelah 2 detik
      setTimeout(() => {
        router.push(`/verify-otp?phone=${form.value.phone}`)
      }, 2000)
      return
    }
    form.value.email = ''
    form.value.phone = ''
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.pesan || 'Gagal mengirim reset password'
    console.error('Forgot password error:', err)
  } finally {
    isLoading.value = false
  }
}
</script>
