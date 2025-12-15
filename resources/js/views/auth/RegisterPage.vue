<!-- 📁 resources/js/views/auth/RegisterPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-10">
        <h1 class="text-5xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
          Telemedicine
        </h1>
        <p class="text-gray-500 text-sm">Daftar Akun {{ userType === 'pasien' ? 'Pasien' : 'Dokter' }}</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Buat Akun {{ userType === 'pasien' ? 'Pasien' : 'Dokter' }}</h2>
        <p class="text-gray-600 text-sm mb-6">Isi data di bawah untuk mendaftar</p>

        <!-- Alert Error -->
        <div v-if="error" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
          <p class="text-red-700 text-sm font-medium">{{ error }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="space-y-5">
          <!-- Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan nama lengkap Anda"
            />
          </div>

          <!-- Email -->
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

          <!-- NIK (Pasien) -->
          <div v-if="userType === 'pasien'">
            <label class="block text-sm font-semibold text-gray-900 mb-2">NIK (16 digit)</label>
            <input
              v-model="form.nik"
              type="text"
              required
              pattern="\d{16}"
              maxlength="16"
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan NIK 16 digit"
            />
          </div>

          <!-- SIP/Spesialisasi (Dokter) -->
          <div v-if="userType === 'dokter'">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Nomor SIP (Surat Ijin Praktik)</label>
            <input
              v-model="form.sip"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan nomor SIP"
            />
          </div>

          <!-- Spesialisasi (Dokter) -->
          <div v-if="userType === 'dokter'">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Spesialisasi</label>
            <select
              v-model="form.specialization"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
            >
              <option value="">Pilih spesialisasi</option>
              <option value="Umum">Dokter Umum</option>
              <option value="Jantung">Jantung</option>
              <option value="Paru">Paru</option>
              <option value="Gigi">Gigi</option>
              <option value="Mata">Mata</option>
              <option value="Saraf">Saraf</option>
              <option value="Kulit">Kulit</option>
            </select>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Nomor Telepon</label>
            <input
              v-model="form.phone"
              type="tel"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
              placeholder="Masukkan nomor telepon"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white pr-10"
                placeholder="Minimal 8 karakter"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition"
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

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Konfirmasi Password</label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50 hover:bg-white"
                placeholder="Ulangi password Anda"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition"
              >
                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753 4.753m7.538 1.25a3 3 0 00-5.396-1.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start gap-3">
            <input
              v-model="form.agree"
              type="checkbox"
              id="agree"
              class="mt-1 w-4 h-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500"
              required
            />
            <label for="agree" class="text-sm text-gray-600">
              Saya setuju dengan <span class="font-semibold">Syarat & Ketentuan</span> dan <span class="font-semibold">Kebijakan Privasi</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            :disabled="isLoading"
            type="submit"
            class="w-full bg-linear-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-bold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed mt-6"
          >
            {{ isLoading ? 'Sedang mendaftar...' : 'Daftar' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-200"></div>
          <span class="px-3 text-gray-400 text-sm">atau</span>
          <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <!-- Login Link -->
        <p class="text-center text-gray-600 text-sm">
          Sudah punya akun?
          <RouterLink to="/login" class="text-indigo-600 font-bold hover:text-indigo-700 transition">
            Masuk di sini
          </RouterLink>
        </p>

        <!-- Back -->
        <router-link
          to="/register"
          class="block text-center text-gray-500 hover:text-gray-700 text-xs mt-4 transition"
        >
          ← Kembali
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { RouterLink } from 'vue-router'

const router = useRouter()
const route = useRoute()
const isLoading = ref(false)
const error = ref(null)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const userType = computed(() => {
  return route.path.includes('pasien') ? 'pasien' : 'dokter'
})

const form = ref({
  name: '',
  email: '',
  nik: '',
  sip: '',
  phone: '',
  specialization: '',
  password: '',
  password_confirmation: '',
  agree: false
})

const handleRegister = async () => {
  error.value = null

  // Validation
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Password tidak sesuai'
    return
  }

  if (form.value.password.length < 8) {
    error.value = 'Password minimal 8 karakter'
    return
  }

  if (!form.value.agree) {
    error.value = 'Anda harus menyetujui syarat & ketentuan'
    return
  }

  isLoading.value = true

  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    }

    if (userType.value === 'pasien') {
      payload.nik = form.value.nik
      payload.role = 'pasien'
    } else {
      payload.sip = form.value.sip
      payload.specialization = form.value.specialization
      payload.role = 'dokter'
    }

    const response = await fetch(`/api/v1/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: JSON.stringify(payload)
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.message || 'Registrasi gagal'
      return
    }

    // Redirect to login
    router.push('/login')
  } catch (err) {
    error.value = err.message || 'Terjadi kesalahan, coba lagi'
    console.error('Register error:', err)
  } finally {
    isLoading.value = false
  }
}
</script>
