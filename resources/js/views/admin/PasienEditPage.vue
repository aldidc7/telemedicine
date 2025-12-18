<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <button
          @click="goBack"
          class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700 mb-4"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Kembali
        </button>
        <h1 class="text-3xl font-bold text-gray-900">Edit Profil Pasien</h1>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <LoadingSpinner />
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <ErrorAlert :message="errorMessage" />
    </div>

    <!-- Form -->
    <div v-else-if="pasien" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-md p-6">
        <!-- Success Message -->
        <SuccessAlert v-if="successMessage" :message="successMessage" />

        <!-- Error Message -->
        <ErrorAlert v-if="formError" :message="formError" />

        <!-- Form Section -->
        <div class="space-y-6">
          <!-- Nama Pasien -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pasien</label>
            <input
              v-model="form.nama"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan nama pasien"
              required
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan email"
              required
            />
          </div>

          <!-- No. Telepon -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon</label>
            <input
              v-model="form.no_telepon"
              type="tel"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan nomor telepon"
            />
          </div>

          <!-- Tanggal Lahir -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
            <input
              v-model="form.tanggal_lahir"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            />
          </div>

          <!-- Jenis Kelamin -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
            <select
              v-model="form.jenis_kelamin"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
              <option value="">Pilih Jenis Kelamin</option>
              <option value="M">Laki-laki</option>
              <option value="F">Perempuan</option>
            </select>
          </div>

          <!-- Alamat -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
            <textarea
              v-model="form.alamat"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan alamat"
            />
          </div>

          <!-- Tinggi Badan -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tinggi Badan (cm)</label>
            <input
              v-model.number="form.tinggi_badan"
              type="number"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan tinggi badan"
            />
          </div>

          <!-- Berat Badan -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Berat Badan (kg)</label>
            <input
              v-model.number="form.berat_badan"
              type="number"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan berat badan"
            />
          </div>

          <!-- Riwayat Medis -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Riwayat Medis</label>
            <textarea
              v-model="form.riwayat_medis"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan riwayat medis"
            />
          </div>

          <!-- Alergi -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Alergi (pisahkan dengan koma)</label>
            <input
              v-model="form.alergi"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Contoh: Penicillin, Latex"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select
              v-model="form.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex gap-3 justify-between">
          <button
            @click="goBack"
            type="button"
            class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition"
          >
            Batal
          </button>
          <button
            :disabled="isSaving"
            type="submit"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg v-if="isSaving" class="w-5 h-5 animate-spin" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
            </svg>
            {{ isSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { pasienAPI } from '@/api/pasien'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import ErrorAlert from '@/components/ErrorAlert.vue'
import SuccessAlert from '@/components/SuccessAlert.vue'
import ErrorHandler from '@/utils/ErrorHandler'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const isSaving = ref(false)
const errorMessage = ref(null)
const successMessage = ref(null)
const formError = ref(null)
const pasienId = route.params.id

const pasien = ref(null)
const form = reactive({
  nama: '',
  email: '',
  no_telepon: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  alamat: '',
  tinggi_badan: 0,
  berat_badan: 0,
  riwayat_medis: '',
  alergi: '',
  status: 'active'
})

/**
 * Load patient data
 */
const loadPasien = async () => {
  loading.value = true
  errorMessage.value = null
  
  try {
    const response = await pasienAPI.getById(pasienId)
    pasien.value = response.data
    
    // Populate form
    form.nama = response.data.pengguna?.name || ''
    form.email = response.data.pengguna?.email || ''
    form.no_telepon = response.data.no_telepon || ''
    form.tanggal_lahir = response.data.tanggal_lahir || ''
    form.jenis_kelamin = response.data.jenis_kelamin || ''
    form.alamat = response.data.alamat || ''
    form.tinggi_badan = response.data.tinggi_badan || 0
    form.berat_badan = response.data.berat_badan || 0
    form.riwayat_medis = response.data.riwayat_medis || ''
    form.alergi = response.data.alergi || ''
    form.status = response.data.status || 'active'
  } catch (error) {
    errorMessage.value = ErrorHandler.getUserMessage(error)
  } finally {
    loading.value = false
  }
}

/**
 * Handle form submission
 */
const handleSubmit = async () => {
  isSaving.value = true
  formError.value = null
  successMessage.value = null
  
  try {
    await pasienAPI.update(pasienId, form)
    successMessage.value = 'Profil pasien berhasil diperbarui'
    
    // Redirect after 1.5 seconds
    setTimeout(() => {
      router.push(`/admin/pasien/${pasienId}`)
    }, 1500)
  } catch (error) {
    formError.value = ErrorHandler.getUserMessage(error)
  } finally {
    isSaving.value = false
  }
}

/**
 * Navigate back
 */
const goBack = () => {
  router.push('/admin/pasien')
}

onMounted(() => {
  loadPasien()
})
</script>
