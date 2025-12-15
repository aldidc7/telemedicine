<!-- 📁 resources/js/components/modals/BuatKonsultasiModal.vue -->
<template>
  <div class="fixed inset-0 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Gradient Header -->
      <div class="bg-linear-to-r from-indigo-600 to-purple-600 rounded-t-2xl p-8 relative overflow-hidden shrink-0">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white bg-opacity-10 rounded-full -mr-20 -mt-20"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <h2 class="text-3xl font-bold text-white mb-1">Buat Konsultasi Baru</h2>
            <p class="text-indigo-100">Konsultasi dengan dokter spesialis terpercaya</p>
          </div>
          <button
            @click="$emit('close')"
            class="w-10 h-10 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center text-white text-2xl transition"
          >
            ×
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="p-8 space-y-6 flex-1 overflow-y-auto">
        <!-- Error Alert -->
        <div v-if="error" class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <p class="text-red-700 font-medium">{{ error }}</p>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccess" class="p-4 bg-green-50 border-l-4 border-green-500 rounded-lg flex items-start gap-3 animate-pulse">
          <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <p class="text-green-700 font-medium">Konsultasi berhasil dibuat! Halaman akan diperbarui dalam beberapa saat...</p>
        </div>

        <!-- Form Grid -->
        <div class="grid grid-cols-1 gap-6">
          <!-- Dokter Selection -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <label class="flex text-sm font-bold text-gray-800 items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Pilih Dokter
              </label>
              <div v-if="form.dokter_id" class="flex items-center gap-1 text-xs font-semibold text-green-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Terpilih
              </div>
            </div>
            
            <!-- Dokter Selection as Cards -->
            <div class="grid grid-cols-1 gap-3">
              <button
                v-for="dokter in dokterList"
                :key="dokter.id"
                @click="selectDokter(dokter)"
                :class="[
                  'p-4 rounded-xl border-2 transition-all text-left',
                  form.dokter_id === dokter.id.toString()
                    ? 'border-indigo-600 bg-indigo-50 shadow-md'
                    : 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50'
                ]"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="font-bold text-gray-900 text-lg">Dr. {{ dokter.user?.name || dokter.name }}</p>
                    <p class="text-sm text-indigo-600 font-semibold">{{ dokter.specialization }}</p>
                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                      <span v-if="dokter.is_available" class="bg-green-100 text-green-800 px-2 py-1 rounded">Tersedia</span>
                      <span v-else class="bg-red-100 text-red-800 px-2 py-1 rounded">Tidak Tersedia</span>
                      <span v-if="dokter.avg_rating" class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        {{ dokter.avg_rating?.toFixed(1) }} ({{ dokter.rating_count }})
                      </span>
                    </div>
                  </div>
                  <div class="ml-4 shrink-0">
                    <div v-if="form.dokter_id === dokter.id.toString()" class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                </div>
              </button>
            </div>

            <!-- Loading Spinner for Doctor List -->
            <div v-if="loadingDokter" class="text-center py-4">
              <svg class="w-6 h-6 animate-spin text-indigo-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
            </div>
          </div>

          <!-- Dokter Info Cards (when selected) -->
          <div v-if="selectedDokter" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Spesialisasi Card -->
            <div class="bg-linear-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
              <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Spesialisasi</p>
              <p class="text-lg font-bold text-blue-900">{{ selectedDokter.specialization }}</p>
            </div>

            <!-- Status Card -->
            <div :class="[
              'rounded-xl p-4 border',
              selectedDokter.is_available
                ? 'bg-linear-to-br from-green-50 to-green-100 border-green-200'
                : 'bg-linear-to-br from-red-50 to-red-100 border-red-200'
            ]">
              <p class="text-xs font-semibold uppercase tracking-wide mb-1" :class="selectedDokter.is_available ? 'text-green-600' : 'text-red-600'">Status</p>
              <p class="text-lg font-bold" :class="selectedDokter.is_available ? 'text-green-900' : 'text-red-900'">
                {{ selectedDokter.is_available ? '✓ Tersedia' : '✗ Tidak Tersedia' }}
              </p>
            </div>

            <!-- Maks Konsultasi Card -->
            <div class="bg-linear-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
              <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-1">Maks Konsultasi</p>
              <p class="text-lg font-bold text-purple-900">{{ selectedDokter.max_concurrent_consultations }}</p>
            </div>
          </div>

          <!-- Jenis Keluhan -->
          <div v-if="form.dokter_id">
            <div class="flex items-center justify-between mb-3">
              <label class="flex text-sm font-bold text-gray-800 items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Jenis Keluhan
              </label>
              <div v-if="form.jenis_keluhan" class="flex items-center gap-1 text-xs font-semibold text-green-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Terpilih
              </div>
            </div>
            
            <!-- Keluhan Selection as Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <button
                v-for="keluhan in keluhanOptions"
                :key="keluhan"
                @click="form.jenis_keluhan = keluhan"
                :class="[
                  'p-3 rounded-lg border-2 font-semibold text-sm transition-all',
                  form.jenis_keluhan === keluhan
                    ? 'border-indigo-600 bg-indigo-100 text-indigo-900'
                    : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-indigo-300'
                ]"
              >
                {{ keluhan }}
              </button>
            </div>
          </div>

          <!-- Deskripsi Singkat -->
          <div v-if="form.jenis_keluhan">
            <div class="flex items-center justify-between mb-3">
              <label class="flex text-sm font-bold text-gray-800 items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                Deskripsi Keluhan
              </label>
              <span class="text-xs font-semibold" :class="form.deskripsi.length >= 500 ? 'text-red-600' : form.deskripsi.length >= 250 ? 'text-amber-600' : 'text-gray-500'">
                {{ form.deskripsi.length }}/500
              </span>
            </div>
            <textarea
              v-model="form.deskripsi"
              required
              rows="8"
              minlength="10"
              maxlength="500"
              placeholder="Jelaskan keluhan Anda secara detail agar dokter dapat memahami kondisi Anda dengan baik. Semakin detail, semakin baik..."
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition resize-none"
            />
            <p class="text-xs text-gray-600 mt-2">Minimal 10 karakter - Maksimal 500 karakter</p>

            <!-- Character Progress Bar -->
            <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
              <div
                :style="{ width: (form.deskripsi.length / 500) * 100 + '%' }"
                :class="[
                  'h-full transition-all duration-200',
                  form.deskripsi.length < 10 ? 'bg-red-500' :
                  form.deskripsi.length < 75 ? 'bg-amber-500' :
                  form.deskripsi.length < 250 ? 'bg-blue-500' : 'bg-green-500'
                ]"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 p-8 flex gap-3 bg-gray-50 rounded-b-2xl shrink-0">
        <!-- Validation Requirements -->
        <div v-if="form.dokter_id && form.jenis_keluhan && form.deskripsi.length < 10" class="flex-1 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 flex items-center gap-2">
          <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          Minimal 10 karakter untuk deskripsi ({{ form.deskripsi.length }}/10)
        </div>
        
        <button
          @click="$emit('close')"
          class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 font-bold transition active:scale-95"
        >
          Batal
        </button>
        <button
          @click="handleSubmit"
          :disabled="isLoading || !form.dokter_id || !form.jenis_keluhan || form.deskripsi.length < 10"
          :class="[
            'flex-1 px-4 py-3 rounded-xl font-bold transition flex items-center justify-center gap-2 active:scale-95',
            isLoading || !form.dokter_id || !form.jenis_keluhan || form.deskripsi.length < 10
              ? 'bg-gray-300 text-gray-600 cursor-not-allowed opacity-60'
              : 'bg-linear-to-r from-indigo-600 to-purple-600 text-white hover:shadow-lg'
          ]"
        >
          <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8H3m0 0h18" />
          </svg>
          <span v-if="!isLoading">Buat Konsultasi</span>
          <span v-else class="flex items-center gap-2">
            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            Memproses...
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { dokterAPI } from '@/api/dokter'
import { konsultasiAPI } from '@/api/konsultasi'

const props = defineProps({
  dokter: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const keluhanOptions = [
  'Umum',
  'Gigi',
  'Kulit',
  'Mata',
  'THT',
  'Paru-paru',
  'Jantung',
  'Orthopedi',
  'Lainnya'
]

const form = ref({
  dokter_id: '',
  jenis_keluhan: '',
  deskripsi: ''
})

const isLoading = ref(false)
const loadingDokter = ref(false)
const error = ref(null)
const showSuccess = ref(false)
const dokterList = ref([])
const selectedDokter = ref(null)

onMounted(async () => {
  await loadDokterList()
  // Jika dokter sudah dipilih dari parent, set otomatis
  if (props.dokter) {
    form.value.dokter_id = props.dokter.id.toString()
    selectedDokter.value = props.dokter
  }
})

const loadDokterList = async () => {
  loadingDokter.value = true
  try {
    const response = await dokterAPI.getList()
    dokterList.value = response.data.data || []
  } catch (err) {
    error.value = 'Gagal memuat data dokter'
    console.error(err)
  } finally {
    loadingDokter.value = false
  }
}

const selectDokter = (dokter) => {
  form.value.dokter_id = dokter.id.toString()
  selectedDokter.value = dokter
}

const handleSubmit = async () => {
  if (!form.value.dokter_id || !form.value.jenis_keluhan || !form.value.deskripsi) {
    error.value = 'Semua field harus diisi'
    return
  }

  if (form.value.deskripsi.length < 10) {
    error.value = 'Deskripsi minimal 10 karakter'
    return
  }

  isLoading.value = true
  error.value = null
  showSuccess.value = false

  try {
    await konsultasiAPI.create({
      dokter_id: parseInt(form.value.dokter_id),
      jenis_keluhan: form.value.jenis_keluhan,
      deskripsi: form.value.deskripsi
    })

    showSuccess.value = true
    
    // Auto close after 2 seconds
    setTimeout(() => {
      emit('success')
    }, 2000)
  } catch (err) {
    const errorMsg = err.response?.data?.pesan || err.message || 'Gagal membuat konsultasi'
    error.value = errorMsg
    showSuccess.value = false
    console.error('Konsultasi Error:', {
      message: errorMsg,
      response: err.response?.data,
      status: err.response?.status,
      fullError: err
    })
  } finally {
    isLoading.value = false
  }
}
</script>