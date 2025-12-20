<template>
  <!-- Profile Completion Modal - Block Access Until Complete -->
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
      <!-- Header -->
      <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white p-6">
        <h2 class="text-2xl font-bold flex items-center gap-3">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
          </svg>
          Lengkapi Profil Anda
        </h2>
        <p class="text-indigo-100 mt-2 text-sm">Data lengkap diperlukan untuk memberikan layanan terbaik</p>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-6">
        <!-- Progress Bar -->
        <div>
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-semibold text-gray-700">Progress Kelengkapan</span>
            <span class="text-sm font-bold text-indigo-600">{{ completion.percentage }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div
              class="bg-linear-to-r from-indigo-600 to-purple-600 h-3 rounded-full transition-all duration-300"
              :style="{ width: completion.percentage + '%' }"
            ></div>
          </div>
        </div>

        <!-- Missing Fields List -->
        <div v-if="completion.missing_fields.length > 0">
          <h3 class="text-sm font-bold text-gray-900 mb-3">Fields yang Perlu Dilengkapi ({{ completion.missing_fields.length }}):</h3>
          <div class="space-y-2">
            <div
              v-for="field in completion.missing_fields"
              :key="field"
              class="flex items-start gap-3 p-3 bg-red-50 border border-red-200 rounded-lg"
            >
              <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm text-gray-700 font-medium">{{ formatFieldName(field) }}</span>
            </div>
          </div>
        </div>

        <!-- Completed Fields -->
        <div v-if="completion.completed_fields.length > 0">
          <h3 class="text-sm font-bold text-gray-900 mb-3">Sudah Diisi ({{ completion.completed_fields.length }}):</h3>
          <div class="space-y-1">
            <div
              v-for="field in completion.completed_fields"
              :key="field"
              class="flex items-center gap-2 text-sm text-green-700"
            >
              <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
              {{ formatFieldName(field) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 p-6 bg-gray-50 flex gap-3">
        <button
          @click="handleEditProfile"
          class="flex-1 px-4 py-3 bg-linear-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:shadow-lg transition font-semibold flex items-center justify-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Edit Profil
        </button>
        <button
          @click="handleRefresh"
          class="px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold"
          title="Refresh untuk cek ulang"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>

      <!-- Info Box -->
      <div class="px-6 pb-6 pt-3">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-xs text-blue-700">
            <strong>💡 Tips:</strong> Lengkapi semua data profil untuk memberikan informasi medis yang akurat dan memastikan konsultasi berkualitas tinggi.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '@/api/auth'

const router = useRouter()
const isOpen = ref(false)
const loading = ref(false)
const completion = ref({
  percentage: 0,
  completed_fields: [],
  missing_fields: [],
  total_fields: 0
})

const show = () => {
  isOpen.value = true
  loadCompletion()
}

const hide = () => {
  isOpen.value = false
}

const loadCompletion = async () => {
  loading.value = true
  try {
    const response = await authApi.getProfileCompletion()
    completion.value = response.data.data
  } catch (error) {
    console.error('Error loading completion:', error)
  } finally {
    loading.value = false
  }
}

const handleEditProfile = () => {
  hide()
  // Route ke profile edit page sesuai role
  const role = localStorage.getItem('userRole') || 'pasien'
  if (role === 'dokter') {
    router.push('/dokter/profile')
  } else if (role === 'admin') {
    router.push('/admin/profile')
  } else {
    router.push('/profile')
  }
}

const handleRefresh = async () => {
  await loadCompletion()
}

const formatFieldName = (field) => {
  const fieldMap = {
    'name': 'Nama Lengkap',
    'email': 'Email',
    'email_verified_at': 'Email Terverifikasi',
    'nik': 'NIK',
    'date_of_birth': 'Tanggal Lahir',
    'gender': 'Jenis Kelamin',
    'phone_number': 'Nomor Telepon',
    'address': 'Alamat',
    'blood_type': 'Golongan Darah',
    'emergency_contact': 'Kontak Darurat',
    'specialization': 'Spesialisasi',
    'license_number': 'Nomor Lisensi',
    'place_of_birth': 'Tempat Lahir',
    'birthplace_city': 'Kota Lahir',
    'profile_photo': 'Foto Profil',
    'is_verified': 'Verifikasi Dokter'
  }
  return fieldMap[field] || field
}

defineExpose({
  show,
  hide,
  loadCompletion
})
</script>
