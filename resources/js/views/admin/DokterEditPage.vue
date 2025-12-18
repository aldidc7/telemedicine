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
        <h1 class="text-3xl font-bold text-gray-900">Edit Profil Dokter</h1>
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
    <div v-else-if="dokter" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-md p-6">
        <!-- Success Message -->
        <SuccessAlert v-if="successMessage" :message="successMessage" />

        <!-- Error Message -->
        <ErrorAlert v-if="formError" :message="formError" />

        <!-- Form Section -->
        <div class="space-y-6">
          <!-- Nama Dokter -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Dokter</label>
            <input
              v-model="form.nama_dokter"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan nama dokter"
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

          <!-- Spesialisasi -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Spesialisasi</label>
            <select
              v-model="form.spesialisasi"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              required
            >
              <option value="">Pilih Spesialisasi</option>
              <option>Umum</option>
              <option>Jantung</option>
              <option>Paru-Paru</option>
              <option>Saraf</option>
              <option>Ginjal</option>
              <option>Mata</option>
              <option>Kulit</option>
              <option>Ortopedi</option>
              <option>Pediatri</option>
              <option>Kebidanan</option>
            </select>
          </div>

          <!-- Tahun Pengalaman -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Pengalaman</label>
            <input
              v-model.number="form.tahun_pengalaman"
              type="number"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan tahun pengalaman"
            />
          </div>

          <!-- No. Registrasi Praktik -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Registrasi Praktik</label>
            <input
              v-model="form.no_registrasi_praktik"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan nomor registrasi praktik"
            />
          </div>

          <!-- Bio -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Biodata</label>
            <textarea
              v-model="form.bio"
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              placeholder="Masukkan biodata dokter"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <div class="flex items-center gap-4">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="form.is_active"
                  :true-value="true"
                  :false-value="false"
                  type="radio"
                  class="w-4 h-4 text-indigo-600"
                />
                <span class="text-gray-700">Aktif</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="form.is_active"
                  :true-value="false"
                  :false-value="true"
                  type="radio"
                  class="w-4 h-4 text-gray-600"
                />
                <span class="text-gray-700">Nonaktif</span>
              </label>
            </div>
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
import { dokterAPI } from '@/api/dokter'
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
const dokterId = route.params.id

const dokter = ref(null)
const form = reactive({
  nama_dokter: '',
  email: '',
  no_telepon: '',
  spesialisasi: '',
  tahun_pengalaman: 0,
  no_registrasi_praktik: '',
  bio: '',
  is_active: true
})

/**
 * Load doctor data
 */
const loadDokter = async () => {
  loading.value = true
  errorMessage.value = null
  
  try {
    const response = await dokterAPI.getById(dokterId)
    dokter.value = response.data
    
    // Populate form
    form.nama_dokter = response.data.nama_dokter || ''
    form.email = response.data.pengguna?.email || ''
    form.no_telepon = response.data.no_telepon || ''
    form.spesialisasi = response.data.spesialisasi || ''
    form.tahun_pengalaman = response.data.tahun_pengalaman || 0
    form.no_registrasi_praktik = response.data.no_registrasi_praktik || ''
    form.bio = response.data.bio || ''
    form.is_active = response.data.is_active || true
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
    await dokterAPI.update(dokterId, form)
    successMessage.value = 'Profil dokter berhasil diperbarui'
    
    // Redirect after 1.5 seconds
    setTimeout(() => {
      router.push(`/admin/dokter/${dokterId}`)
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
  router.push('/admin/dokter')
}

onMounted(() => {
  loadDokter()
})
</script>
