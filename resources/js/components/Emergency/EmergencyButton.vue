<template>
  <button
    v-if="showButton"
    @click="isOpen = true"
    :class="[
      'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition duration-200',
      isActivated
        ? 'bg-red-600 hover:bg-red-700 text-white shadow-lg animate-pulse'
        : 'bg-red-100 hover:bg-red-200 text-red-700 border-2 border-red-600'
    ]"
  >
    <AlertCircle class="w-5 h-5" />
    <span>Lapor Darurat</span>
  </button>

  <!-- Quick Emergency Modal -->
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div
        class="fixed inset-0 bg-black bg-opacity-50"
        @click="closeModal"
      ></div>

      <div class="flex min-h-screen items-center justify-center p-4">
        <div
          class="relative bg-white rounded-lg shadow-2xl max-w-2xl w-full"
          @click.stop
        >
          <!-- Header -->
          <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <AlertTriangle class="w-6 h-6" />
              Lapor Kasus Darurat Medis
            </h2>
            <button
              @click="closeModal"
              class="text-white hover:bg-white hover:bg-opacity-20 p-1 rounded transition"
            >
              <X class="w-6 h-6" />
            </button>
          </div>

          <!-- Content -->
          <div class="p-6">
            <!-- Warning -->
            <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6">
              <p class="text-red-800 font-semibold">⚠️ Peringatan Penting</p>
              <p class="text-red-700 text-sm mt-2">
                Fitur ini hanya untuk kasus gawat darurat medis yang memerlukan penanganan segera. Penggunaan yang tidak sesuai dapat menghambat respons penanganan darurat nyata.
              </p>
            </div>

            <!-- Quick Form -->
            <form @submit.prevent="submitEmergency" class="space-y-4">
              <!-- Emergency Level -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                  Tingkat Kegawatan *
                </label>
                <div class="space-y-2">
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      v-model="emergencyForm.level"
                      value="critical"
                      class="w-4 h-4 text-red-600"
                    />
                    <span class="ml-3 font-semibold text-red-700">
                      🔴 KRITIS - Kehidupan dalam bahaya
                    </span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      v-model="emergencyForm.level"
                      value="severe"
                      class="w-4 h-4 text-orange-600"
                    />
                    <span class="ml-3 font-semibold text-orange-600">
                      🟠 SERIUS - Perlu penanganan segera
                    </span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      v-model="emergencyForm.level"
                      value="moderate"
                      class="w-4 h-4 text-yellow-600"
                    />
                    <span class="ml-3 font-semibold text-yellow-600">
                      🟡 SEDANG - Stabil tapi urgent
                    </span>
                  </label>
                </div>
              </div>

              <!-- Symptoms -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Gejala/Keluhan Pasien *
                </label>
                <textarea
                  v-model="emergencyForm.reason"
                  required
                  rows="4"
                  placeholder="Jelaskan secara detail gejala yang dialami pasien, misalnya: nyeri dada, sesak napas, dll"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                ></textarea>
              </div>

              <!-- Additional Info -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Informasi Tambahan
                </label>
                <textarea
                  v-model="emergencyForm.notes"
                  rows="2"
                  placeholder="Riwayat medis, alergi, atau informasi penting lainnya"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                ></textarea>
              </div>

              <!-- Confirmation Checkbox -->
              <label class="flex items-start cursor-pointer">
                <input
                  v-model="isConfirmed"
                  type="checkbox"
                  class="w-4 h-4 mt-1 text-red-600 rounded"
                />
                <span class="ml-3 text-sm text-gray-700">
                  Saya mengonfirmasi bahwa laporan ini adalah untuk kasus darurat medis yang serius dan memerlukan respons cepat.
                </span>
              </label>

              <!-- Action Buttons -->
              <div class="flex gap-3 pt-4 border-t">
                <button
                  type="submit"
                  :disabled="!isConfirmed || isSubmitting"
                  class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold rounded-lg transition flex items-center justify-center gap-2"
                >
                  <AlertTriangle v-if="!isSubmitting" class="w-5 h-5" />
                  <span v-if="isSubmitting">Melaporkan...</span>
                  <span v-else>Lapor Darurat Sekarang</span>
                </button>
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-lg transition"
                >
                  Batal
                </button>
              </div>
            </form>

            <!-- Contact Support -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <p class="text-blue-900 font-semibold text-sm">📞 Jika ini adalah keadaan darurat nyata:</p>
              <p class="text-blue-800 text-sm mt-2">Hubungi nomor ambulans lokal atau pergi langsung ke rumah sakit terdekat. Jangan hanya mengandalkan pelaporan digital untuk keadaan darurat nyata.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { AlertCircle, AlertTriangle, X } from 'lucide-vue-next'
import { authApi } from '@/api'

const props = defineProps({
  consultationId: {
    type: [String, Number],
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['emergency-created'])

const isOpen = ref(false)
const isSubmitting = ref(false)
const isConfirmed = ref(false)
const isActivated = ref(false)

const emergencyForm = ref({
  consultation_id: props.consultationId,
  level: 'severe',
  reason: '',
  notes: '',
})

const showButton = computed(() => !props.disabled && props.consultationId)

const closeModal = () => {
  if (!isSubmitting.value) {
    isOpen.value = false
    isConfirmed.value = false
  }
}

const submitEmergency = async () => {
  if (!isConfirmed.value || !emergencyForm.value.reason.trim()) {
    alert('Mohon lengkapi semua informasi dan konfirmasi')
    return
  }

  isSubmitting.value = true
  try {
    const response = await authApi.post('/api/v1/emergencies', emergencyForm.value)
    
    isActivated.value = true
    closeModal()
    
    // Reset form
    emergencyForm.value = {
      consultation_id: props.consultationId,
      level: 'severe',
      reason: '',
      notes: '',
    }

    emit('emergency-created', response.data.data)
    
    alert('✅ Kasus darurat berhasil dilaporkan!\n\nTim medis dan ambulans akan segera ditugaskan. Tetap tenang dan ikuti instruksi dari tenaga medis.')
  } catch (error) {
    alert('❌ Gagal melaporkan kasus darurat. Silakan coba lagi.')
    console.error('Emergency creation error:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
