<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal -->
      <div class="flex min-h-screen items-center justify-center p-4">
        <div
          class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto"
          @click.stop
        >
          <!-- Header -->
          <div class="sticky top-0 px-6 py-4 flex items-center justify-between" style="background: linear-gradient(to right, rgb(220, 38, 38), rgb(234, 88, 12));">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <AlertCircle class="w-6 h-6" />
              Detail Kasus Darurat
            </h2>
            <button
              @click="$emit('close')"
              class="text-white hover:bg-white hover:bg-opacity-20 p-1 rounded transition"
            >
              <X class="w-6 h-6" />
            </button>
          </div>

          <!-- Content -->
          <div class="p-6 space-y-6">
            <!-- Status and Level -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm text-gray-600">Tingkat Darurat</label>
                <p :class="getLevelColor(emergency.level)" class="text-lg font-bold mt-1">
                  {{ getLevelLabel(emergency.level) }}
                </p>
              </div>
              <div>
                <label class="text-sm text-gray-600">Status</label>
                <p class="text-lg font-bold mt-1">{{ getStatusLabel(emergency.status) }}</p>
              </div>
            </div>

            <!-- Reason -->
            <div>
              <label class="text-sm text-gray-600 font-semibold">Gejala/Keluhan</label>
              <p class="text-gray-800 mt-2 whitespace-pre-wrap">{{ emergency.reason }}</p>
            </div>

            <!-- Notes -->
            <div v-if="emergency.notes">
              <label class="text-sm text-gray-600 font-semibold">Catatan</label>
              <p class="text-gray-800 mt-2 whitespace-pre-wrap">{{ emergency.notes }}</p>
            </div>

            <!-- Hospital Info -->
            <div v-if="emergency.hospital_name" class="bg-blue-50 p-4 rounded-lg">
              <label class="text-sm text-blue-900 font-semibold">Dirujuk ke Rumah Sakit</label>
              <p class="text-blue-900 mt-2 font-semibold">{{ emergency.hospital_name }}</p>
              <p v-if="emergency.hospital_address" class="text-blue-800 text-sm mt-1">
                {{ emergency.hospital_address }}
              </p>
            </div>

            <!-- Ambulance Info -->
            <div v-if="emergency.ambulance_called_at" class="bg-orange-50 p-4 rounded-lg">
              <label class="text-sm text-orange-900 font-semibold">🚑 Ambulans Dipanggil</label>
              <p class="text-orange-900 mt-2">{{ formatDate(emergency.ambulance_called_at) }}</p>
              <p v-if="emergency.ambulance_eta" class="text-orange-800 text-sm mt-1">
                ETA: {{ emergency.ambulance_eta }}
              </p>
            </div>

            <!-- Timestamps -->
            <div class="border-t pt-4 grid grid-cols-2 gap-4 text-sm">
              <div>
                <label class="text-gray-600">Dilaporkan</label>
                <p class="text-gray-800 font-semibold mt-1">{{ formatDate(emergency.created_at) }}</p>
              </div>
              <div v-if="emergency.escalated_at">
                <label class="text-gray-600">Tereskalasi</label>
                <p class="text-gray-800 font-semibold mt-1">{{ formatDate(emergency.escalated_at) }}</p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t pt-6 space-y-3">
              <!-- Escalate to Hospital -->
              <button
                v-if="emergency.status === 'open' || emergency.status === 'escalated'"
                @click="showEscalateForm = !showEscalateForm"
                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
              >
                <Building2 class="w-5 h-5" />
                Eskalasi ke Rumah Sakit
              </button>

              <!-- Call Ambulance -->
              <button
                v-if="emergency.status !== 'resolved' && !emergency.ambulance_called_at"
                @click="handleCallAmbulance"
                :disabled="isLoading"
                class="w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 disabled:bg-gray-400 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
              >
                <Truck class="w-5 h-5" />
                <span v-if="isLoading">Memanggil...</span>
                <span v-else>Panggil Ambulans</span>
              </button>

              <!-- Generate Referral Letter -->
              <button
                v-if="emergency.status === 'escalated'"
                @click="handleGenerateReferralLetter"
                :disabled="isLoading"
                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
              >
                <FileText class="w-5 h-5" />
                <span v-if="isLoading">Membuat...</span>
                <span v-else>Buat Surat Rujukan</span>
              </button>

              <!-- View Referral Letter -->
              <button
                v-if="emergency.referral_letter"
                @click="showReferralLetter = true"
                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
              >
                <Download class="w-5 h-5" />
                Lihat Surat Rujukan
              </button>

              <!-- View Audit Log -->
              <button
                @click="showAuditLog = !showAuditLog"
                class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
              >
                <LogSquare class="w-5 h-5" />
                Lihat Catatan Audit
              </button>

              <!-- Resolve -->
              <button
                v-if="emergency.status !== 'resolved'"
                @click="handleResolve"
                :disabled="isLoading"
                class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 text-white rounded-lg font-semibold transition"
              >
                <span v-if="isLoading">Menyelesaikan...</span>
                <span v-else>Tandai Selesai</span>
              </button>
            </div>
          </div>

          <!-- Escalate Form Section -->
          <div v-if="showEscalateForm" class="border-t p-6 bg-gray-50">
            <h3 class="font-bold text-gray-800 mb-4">Eskalasi ke Rumah Sakit</h3>
            <form @submit.prevent="handleEscalate" class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Rumah Sakit *</label>
                <input
                  v-model="escalateForm.hospital_name"
                  type="text"
                  required
                  placeholder="e.g., RSUP Fatmawati"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat *</label>
                <input
                  v-model="escalateForm.hospital_address"
                  type="text"
                  required
                  placeholder="Alamat lengkap rumah sakit"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                />
              </div>
              <div class="flex gap-2 pt-2">
                <button
                  type="submit"
                  :disabled="isLoading"
                  class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white rounded-lg font-semibold"
                >
                  <span v-if="isLoading">Mengeskalasi...</span>
                  <span v-else>Eskalasi</span>
                </button>
                <button
                  type="button"
                  @click="showEscalateForm = false"
                  class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-semibold"
                >
                  Batal
                </button>
              </div>
            </form>
          </div>

          <!-- Audit Log Section -->
          <div v-if="showAuditLog" class="border-t p-6 bg-gray-50">
            <h3 class="font-bold text-gray-800 mb-4">Catatan Audit</h3>
            <div v-if="auditLogs.length === 0" class="text-center py-6 text-gray-600">
              Belum ada catatan
            </div>
            <div v-else class="space-y-3 max-h-64 overflow-y-auto">
              <div
                v-for="log in auditLogs"
                :key="log.id"
                class="bg-white p-3 rounded border border-gray-200 text-sm"
              >
                <p class="font-semibold text-gray-800">{{ log.action }}</p>
                <p class="text-gray-600 mt-1">{{ log.details }}</p>
                <p class="text-gray-500 text-xs mt-2">{{ formatDate(log.timestamp) }}</p>
              </div>
            </div>
          </div>

          <!-- Referral Letter Modal -->
          <ReferralLetterView
            v-if="showReferralLetter && emergency.referral_letter"
            :emergency="emergency"
            @close="showReferralLetter = false"
          />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  X,
  AlertCircle,
  Building2,
  Truck,
  FileText,
  Download,
  LogSquare,
} from 'lucide-vue-next'
import ReferralLetterView from './ReferralLetterView.vue'
import { authApi } from '@/api'

const props = defineProps({
  emergency: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['close', 'escalate', 'call-ambulance', 'add-contact'])

const showEscalateForm = ref(false)
const showAuditLog = ref(false)
const showReferralLetter = ref(false)
const isLoading = ref(false)
const auditLogs = ref([])
const escalateForm = ref({
  hospital_name: '',
  hospital_address: '',
})

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

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const handleCallAmbulance = async () => {
  isLoading.value = true
  try {
    await authApi.post(`/api/v1/emergencies/${props.emergency.id}/call-ambulance`)
    emit('call-ambulance')
    alert('Ambulans telah dipanggil')
  } catch (error) {
    alert('Gagal memanggil ambulans. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const handleEscalate = async () => {
  if (!escalateForm.value.hospital_name || !escalateForm.value.hospital_address) {
    alert('Mohon isi semua data rumah sakit')
    return
  }

  isLoading.value = true
  try {
    await authApi.post(
      `/api/v1/emergencies/${props.emergency.id}/escalate`,
      escalateForm.value
    )
    emit('escalate')
    showEscalateForm.value = false
    alert('Kasus berhasil tereskalasi')
  } catch (error) {
    alert('Gagal eskalasi. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const handleGenerateReferralLetter = async () => {
  isLoading.value = true
  try {
    await authApi.post(`/api/v1/emergencies/${props.emergency.id}/referral-letter`)
    alert('Surat rujukan berhasil dibuat')
    // Reload emergency data
    const response = await authApi.get(`/api/v1/emergencies/${props.emergency.id}`)
    Object.assign(props.emergency, response.data.data)
  } catch (error) {
    alert('Gagal membuat surat rujukan. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const handleResolve = async () => {
  if (!confirm('Apakah Anda yakin kasus ini sudah selesai ditangani?')) {
    return
  }

  isLoading.value = true
  try {
    await authApi.put(`/api/v1/emergencies/${props.emergency.id}/resolve`)
    alert('Kasus berhasil ditandai selesai')
    emit('close')
  } catch (error) {
    alert('Gagal menandai kasus selesai. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const loadAuditLog = async () => {
  try {
    const response = await authApi.get(`/api/v1/emergencies/${props.emergency.id}/log`)
    auditLogs.value = response.data.data
  } catch (error) {
    console.error('Failed to load audit log:', error)
  }
}

onMounted(() => {
  loadAuditLog()
})
</script>
