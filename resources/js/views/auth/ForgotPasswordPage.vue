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
        <p class="text-gray-600 text-sm mb-6">Masukkan email Anda untuk menerima link reset password</p>

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
          <!-- Email Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan email Anda"
            />
          </div>

          <!-- Submit Button -->
          <button
            :disabled="isLoading"
            type="submit"
            class="w-full bg-linear-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-bold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed mt-6"
          >
            {{ isLoading ? 'Mengirim...' : 'Kirim Link Reset' }}
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
            <strong>💡 Info:</strong> Link reset password akan dikirim ke email Anda. Link berlaku selama 30 menit.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { authApi } from '@/api/auth'

const isLoading = ref(false)
const error = ref(null)
const success = ref(null)

const form = ref({
  email: ''
})

const handleSubmit = async () => {
  isLoading.value = true
  error.value = null
  success.value = null

  try {
    await authApi.forgotPassword({
      email: form.value.email
    })
    
    success.value = `Link reset password telah dikirim ke ${form.value.email}. Silakan cek email Anda (jangan lupa folder spam).`
    form.value.email = ''
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.pesan || 'Gagal mengirim link reset'
    console.error('Forgot password error:', err)
  } finally {
    isLoading.value = false
  }
}
</script>
