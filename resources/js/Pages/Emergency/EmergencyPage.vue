<template>
  <div class="min-h-screen p-4 md:p-8" style="background: linear-gradient(to bottom right, rgb(254, 242, 242), rgb(255, 247, 237));">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 flex items-center gap-3">
          <AlertCircle class="w-8 h-8 text-red-600" />
          Sistem Penanganan Darurat
        </h1>
        <p class="text-gray-600 mt-2">Lapor dan kelola kasus darurat medis dengan prioritas tinggi</p>
      </div>

      <!-- Active Emergency Alert -->
      <div v-if="activeEmergency" class="mb-8 p-6 bg-red-100 border-l-4 border-red-600 rounded-lg">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-bold text-red-800">⚠️ Ada Kasus Darurat Aktif</h3>
            <p class="text-red-700 mt-2">Level: <span class="font-bold">{{ activeEmergency.level.toUpperCase() }}</span></p>
            <p class="text-red-700">Status: <span class="font-bold">{{ getStatusLabel(activeEmergency.status) }}</span></p>
            <p class="text-red-700 mt-2">{{ activeEmergency.reason }}</p>
          </div>
          <button
            @click="goToActiveEmergency"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition"
          >
            Lihat Detail
          </button>
        </div>
      </div>

      <!-- Create Emergency Form -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Lapor Kasus Darurat</h2>

        <form @submit.prevent="submitEmergency" class="space-y-6">
          <!-- Consultation Selection -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Konsultasi Terkait *
            </label>
            <select
              v-model="form.consultation_id"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            >
              <option value="">-- Pilih Konsultasi --</option>
              <option v-for="consultation in consultations" :key="consultation.id" :value="consultation.id">
                Konsultasi dengan {{ consultation.doctor_name }} - {{ formatDate(consultation.created_at) }}
              </option>
            </select>
            <span v-if="errors.consultation_id" class="text-red-600 text-sm mt-1">{{ errors.consultation_id[0] }}</span>
          </div>

          <!-- Emergency Level -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">
              Tingkat Darurat *
            </label>
            <div class="space-y-3">
              <label v-for="level in ['critical', 'severe', 'moderate']" :key="level" class="flex items-center">
                <input
                  type="radio"
                  v-model="form.level"
                  :value="level"
                  required
                  class="w-4 h-4 text-red-600"
                />
                <span class="ml-3 font-semibold" :class="getLevelColor(level)">
                  {{ getLevelLabel(level) }}
                </span>
              </label>
            </div>
            <div class="mt-3 p-3 bg-blue-50 rounded text-sm text-blue-800">
              <strong>Panduan:</strong>
              <ul class="list-disc ml-5 mt-2">
                <li><strong>CRITICAL:</strong> Kehidupan dalam bahaya, perlu ambulans & rumah sakit segera</li>
                <li><strong>SEVERE:</strong> Kondisi serius, perlu eskalasi rumah sakit</li>
                <li><strong>MODERATE:</strong> Kondisi stabil tapi memerlukan tindakan segera</li>
              </ul>
            </div>
          </div>

          <!-- Emergency Reason -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Gejala/Keluhan Darurat *
            </label>
            <textarea
              v-model="form.reason"
              required
              rows="4"
              placeholder="Jelaskan gejala dan kondisi pasien secara detail..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            ></textarea>
            <span v-if="errors.reason" class="text-red-600 text-sm mt-1">{{ errors.reason[0] }}</span>
          </div>

          <!-- Additional Notes -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Catatan Tambahan
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Informasi tambahan yang relevan..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold rounded-lg transition flex items-center justify-center gap-2"
          >
            <AlertTriangle v-if="!isSubmitting" class="w-5 h-5" />
            <span v-if="isSubmitting">Melaporkan...</span>
            <span v-else>Lapor Kasus Darurat</span>
          </button>
        </form>
      </div>

      <!-- Emergency History -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Kasus Darurat</h2>

        <div v-if="emergencies.length === 0" class="text-center py-12">
          <CheckCircle2 class="w-12 h-12 text-green-600 mx-auto mb-4" />
          <p class="text-gray-600 text-lg">Belum ada kasus darurat yang terlaporkan</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="emergency in emergencies"
            :key="emergency.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer"
            @click="viewEmergency(emergency)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <span :class="getStatusIcon(emergency.status)" class="text-xl">{{ getStatusIcon(emergency.status) }}</span>
                  <h3 class="font-bold text-gray-800">{{ emergency.reason.substring(0, 50) }}...</h3>
                </div>
                <p class="text-gray-600 text-sm mt-2">
                  Level: <span :class="getLevelColor(emergency.level)" class="font-semibold">{{ getLevelLabel(emergency.level) }}</span>
                </p>
                <p class="text-gray-600 text-sm">Status: <span class="font-semibold">{{ getStatusLabel(emergency.status) }}</span></p>
                <p class="text-gray-500 text-xs mt-2">{{ formatDate(emergency.created_at) }}</p>
              </div>
              <ChevronRight class="w-5 h-5 text-gray-400" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Emergency Details Modal -->
    <EmergencyDetailsModal
      v-if="selectedEmergency"
      :emergency="selectedEmergency"
      @close="selectedEmergency = null"
      @escalate="handleEscalate"
      @call-ambulance="handleCallAmbulance"
      @add-contact="handleAddContact"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import {
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  ChevronRight,
} from 'lucide-vue-next'
import EmergencyDetailsModal from '@/Components/Emergency/EmergencyDetailsModal.vue'
import { authApi } from '@/api'

const form = ref({
  consultation_id: '',
  level: 'severe',
  reason: '',
  notes: '',
})

const errors = ref({})
const isSubmitting = ref(false)
const consultations = ref([])
const emergencies = ref([])
const activeEmergency = computed(() =>
  emergencies.value.find((e) => ['open', 'escalated'].includes(e.status))
)
const selectedEmergency = ref(null)

const getLevelLabel = (level) => {
  const labels = {
    critical: '⚠️ KRITIS',
    severe: '🔴 SERIUS',
    moderate: '🟡 SEDANG',
  }
  return labels[level] || level
}

const getLevelColor = (level) => {
  const colors = {
    critical: 'text-red-700',
    severe: 'text-red-600',
    moderate: 'text-orange-600',
  }
  return colors[level] || ''
}

const getStatusLabel = (status) => {
  const labels = {
    open: 'Terbuka',
    escalated: 'Tereskalasi',
    resolved: 'Selesai',
    referred: 'Dirujuk',
  }
  return labels[status] || status
}

const getStatusIcon = (status) => {
  const icons = {
    open: '📋',
    escalated: '🚑',
    resolved: '✅',
    referred: '🏥',
  }
  return icons[status] || '❓'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const loadConsultations = async () => {
  try {
    const response = await authApi.get('/api/v1/konsultasi')
    consultations.value = response.data.data
  } catch (error) {
    console.error('Failed to load consultations:', error)
  }
}

const loadEmergencies = async () => {
  try {
    const response = await authApi.get('/api/v1/emergencies')
    emergencies.value = response.data.data
  } catch (error) {
    console.error('Failed to load emergencies:', error)
  }
}

const submitEmergency = async () => {
  isSubmitting.value = true
  errors.value = {}

  try {
    const response = await authApi.post('/api/v1/emergencies', form.value)
    
    // Reset form
    form.value = {
      consultation_id: '',
      level: 'severe',
      reason: '',
      notes: '',
    }

    // Reload emergencies
    await loadEmergencies()

    // Show success notification
    alert('Kasus darurat berhasil dilaporkan. Tim medis akan segera ditugaskan.')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Gagal melaporkan kasus darurat. Silakan coba lagi.')
    }
  } finally {
    isSubmitting.value = false
  }
}

const goToActiveEmergency = () => {
  if (activeEmergency.value) {
    selectedEmergency.value = activeEmergency.value
  }
}

const viewEmergency = (emergency) => {
  selectedEmergency.value = emergency
}

const handleEscalate = async (emergencyId, hospitalData) => {
  try {
    await authApi.post(`/api/v1/emergencies/${emergencyId}/escalate`, hospitalData)
    await loadEmergencies()
    alert('Kasus berhasil tereskalasi ke rumah sakit')
  } catch (error) {
    alert('Gagal eskalasi kasus. Silakan coba lagi.')
  }
}

const handleCallAmbulance = async (emergencyId) => {
  try {
    await authApi.post(`/api/v1/emergencies/${emergencyId}/call-ambulance`)
    await loadEmergencies()
    alert('Ambulans telah dipanggil. ETA akan diperbarui segera.')
  } catch (error) {
    alert('Gagal memanggil ambulans. Silakan coba lagi.')
  }
}

const handleAddContact = async (emergencyId, contactData) => {
  try {
    await authApi.post(`/api/v1/emergencies/${emergencyId}/contacts`, contactData)
    await loadEmergencies()
    alert('Kontak darurat berhasil ditambahkan')
  } catch (error) {
    alert('Gagal menambah kontak. Silakan coba lagi.')
  }
}

onMounted(() => {
  loadConsultations()
  loadEmergencies()
})
</script>
