<template>
  <div class="credential-submission">
    <div class="container">
      <div class="header">
        <h2 class="title">Verifikasi Kredensial Dokter</h2>
        <p class="subtitle">Lengkapi data kredensial untuk verifikasi KKI</p>
      </div>

      <!-- Status Alert -->
      <div v-if="verificationStatus" :class="['status-alert', `status-${verificationStatus.status}`]">
        <span class="status-icon">
          {{ getStatusIcon(verificationStatus.status) }}
        </span>
        <div>
          <p class="status-title">{{ getStatusTitle(verificationStatus.status) }}</p>
          <p class="status-message">{{ getStatusMessage(verificationStatus.status) }}</p>
        </div>
      </div>

      <!-- Credentials Form -->
      <div class="credentials-form">
        <div v-if="credentials.length === 0" class="empty-state">
          <p>Belum ada credentials yang disubmit</p>
        </div>

        <div v-else class="credentials-list">
          <div
            v-for="(cred, index) in credentials"
            :key="index"
            class="credential-card"
          >
            <div class="credential-header">
              <h4>{{ cred.typeLabel }}</h4>
              <span :class="['badge', `status-${cred.status}`]">
                {{ cred.statusLabel }}
              </span>
            </div>
            <div class="credential-details">
              <p><strong>Nomor:</strong> {{ cred.number }}</p>
              <p><strong>Issued By:</strong> {{ cred.issuedBy || '-' }}</p>
              <p><strong>Tanggal Terbit:</strong> {{ cred.issuedDate }}</p>
              <p><strong>Tanggal Expired:</strong> {{ cred.expiryDate }}</p>
              <p v-if="cred.rejectionReason">
                <strong class="text-red-600">Alasan Penolakan:</strong> {{ cred.rejectionReason }}
              </p>
            </div>
          </div>
        </div>

        <!-- Add New Credential -->
        <div v-if="verificationStatus && verificationStatus.status !== 'verified'" class="add-credential">
          <h3>Tambah Kredensial Baru</h3>

          <div class="form-group">
            <label>Jenis Kredensial *</label>
            <select
              v-model="newCredential.type"
              class="form-control"
            >
              <option value="">Pilih jenis kredensial</option>
              <option value="kki">KKI (Kompetensi Klinisi Indonesia)</option>
              <option value="sip">SIP (Surat Ijin Praktik)</option>
              <option value="aipki">AIPKI (Indonesian Medical Doctor License)</option>
              <option value="spesialis">Spesialis</option>
              <option value="subspesialis">Sub-spesialis</option>
            </select>
          </div>

          <div class="form-group">
            <label>Nomor Kredensial *</label>
            <input
              v-model="newCredential.number"
              type="text"
              placeholder="Nomor kredensial"
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label>Issued By</label>
            <input
              v-model="newCredential.issuedBy"
              type="text"
              placeholder="Lembaga yang mengeluarkan"
              class="form-control"
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Tanggal Terbit *</label>
              <input
                v-model="newCredential.issuedDate"
                type="date"
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label>Tanggal Expired *</label>
              <input
                v-model="newCredential.expiryDate"
                type="date"
                class="form-control"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Dokumen (PDF/JPG/PNG, Max 5MB)</label>
            <input
              @change="onDocumentSelected"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              class="form-control"
            />
            <p v-if="newCredential.document" class="file-info">
              ✓ {{ newCredential.document.name }}
            </p>
          </div>

          <div class="form-actions">
            <button
              @click="addCredential"
              :disabled="!isNewCredentialValid || submitting"
              class="btn-primary"
            >
              {{ submitting ? 'Sedang diproses...' : 'Tambah Kredensial' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div v-if="pendingCredentials.length > 0" class="submit-section">
        <div class="alert-info">
          <p>Anda memiliki {{ pendingCredentials.length }} kredensial yang belum diverifikasi</p>
        </div>
        <button
          @click="submitForVerification"
          :disabled="submitting"
          class="btn-submit"
        >
          {{ submitting ? 'Sedang mengirim...' : 'Kirim untuk Verifikasi' }}
        </button>
      </div>

      <!-- Instructions -->
      <div class="instructions">
        <h3>Panduan Pengisian</h3>
        <ul>
          <li>KKI (Kompetensi Klinisi Indonesia) - Wajib</li>
          <li>SIP (Surat Ijin Praktik) - Wajib</li>
          <li>Spesialis/Sub-spesialis - Optional (sesuai keahlian)</li>
          <li>Upload dokumen asli/scan untuk setiap kredensial</li>
          <li>Proses verifikasi memakan waktu 3-5 hari kerja</li>
          <li>Anda akan menerima notifikasi setelah verifikasi selesai</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import credentialService from '@/services/credentialService'

// State
const verificationStatus = ref(null)
const credentials = ref([])
const newCredential = ref({
  type: '',
  number: '',
  issuedBy: '',
  issuedDate: '',
  expiryDate: '',
  document: null,
})
const submitting = ref(false)
const credentialsToAdd = ref([])

// Computed
const isNewCredentialValid = computed(() => {
  return newCredential.value.type &&
    newCredential.value.number &&
    newCredential.value.issuedDate &&
    newCredential.value.expiryDate
})

const pendingCredentials = computed(() => {
  return credentials.value.filter(c => c.status === 'pending')
})

// Methods
const loadVerificationStatus = async () => {
  try {
    const response = await credentialService.getCredentialsStatus()
    verificationStatus.value = response.data
    
    credentials.value = response.data.credentials.map(c => ({
      ...c,
      typeLabel: getCredentialTypeLabel(c.type),
      statusLabel: getStatusLabel(c.status),
    }))
  } catch (error) {
    console.error('Error loading status:', error)
  }
}

const onDocumentSelected = (event) => {
  newCredential.value.document = event.target.files[0]
}

const addCredential = () => {
  credentialsToAdd.value.push({
    type: newCredential.value.type,
    number: newCredential.value.number,
    issuedBy: newCredential.value.issuedBy,
    issuedDate: newCredential.value.issuedDate,
    expiryDate: newCredential.value.expiryDate,
    document: newCredential.value.document,
  })

  // Reset form
  newCredential.value = {
    type: '',
    number: '',
    issuedBy: '',
    issuedDate: '',
    expiryDate: '',
    document: null,
  }

  // Update preview
  if (credentialsToAdd.value.length > 0) {
    credentials.value = [
      ...credentials.value,
      ...credentialsToAdd.value.map(c => ({
        type: c.type,
        typeLabel: getCredentialTypeLabel(c.type),
        number: c.number,
        issuedBy: c.issuedBy,
        issuedDate: c.issuedDate,
        expiryDate: c.expiryDate,
        status: 'draft',
        statusLabel: 'Draft',
      }))
    ]
  }
}

const submitForVerification = async () => {
  if (credentialsToAdd.value.length === 0) return

  submitting.value = true
  try {
    const formData = new FormData()
    
    credentialsToAdd.value.forEach((cred, index) => {
      formData.append(`credentials[${index}][type]`, cred.type)
      formData.append(`credentials[${index}][number]`, cred.number)
      formData.append(`credentials[${index}][issued_date]`, cred.issuedDate)
      formData.append(`credentials[${index}][expiry_date]`, cred.expiryDate)
      if (cred.issuedBy) {
        formData.append(`credentials[${index}][issued_by]`, cred.issuedBy)
      }
      if (cred.document) {
        formData.append(`credentials[${index}][document]`, cred.document)
      }
    })

    await credentialService.submitCredentials(formData)
    credentialsToAdd.value = []
    await loadVerificationStatus()
  } catch (error) {
    console.error('Error submitting credentials:', error)
  } finally {
    submitting.value = false
  }
}

const getCredentialTypeLabel = (type) => {
  const labels = {
    kki: 'KKI (Kompetensi Klinisi Indonesia)',
    sip: 'SIP (Surat Ijin Praktik)',
    aipki: 'AIPKI',
    spesialis: 'Spesialis',
    subspesialis: 'Sub-spesialis',
  }
  return labels[type] || type
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Menunggu Verifikasi',
    under_review: 'Sedang Diverifikasi',
    verified: 'Terverifikasi',
    rejected: 'Ditolak',
    expired: 'Expired',
  }
  return labels[status] || status
}

const getStatusIcon = (status) => {
  const icons = {
    unverified: '⚠️',
    pending: '⏳',
    verified: '✅',
    rejected: '❌',
  }
  return icons[status] || '❓'
}

const getStatusTitle = (status) => {
  const titles = {
    unverified: 'Belum Diverifikasi',
    pending: 'Menunggu Verifikasi',
    verified: 'Sudah Terverifikasi',
    rejected: 'Verifikasi Ditolak',
  }
  return titles[status] || status
}

const getStatusMessage = (status) => {
  const messages = {
    unverified: 'Silakan submit kredensial Anda untuk verifikasi',
    pending: 'Kredensial Anda sedang diverifikasi. Proses memakan waktu 3-5 hari kerja',
    verified: 'Anda sudah terverifikasi dan dapat mulai konsultasi',
    rejected: 'Ada dokumen yang ditolak. Silakan periksa dan submit ulang',
  }
  return messages[status] || ''
}

onMounted(() => {
  loadVerificationStatus()
})
</script>

<style scoped>
.credential-submission {
  @apply bg-gray-50 py-12 px-4;
}

.container {
  @apply max-w-4xl mx-auto;
}

.header {
  @apply mb-8 text-center;
}

.title {
  @apply text-3xl font-bold text-gray-900 mb-2;
}

.subtitle {
  @apply text-gray-600;
}

.status-alert {
  @apply mb-8 p-6 rounded-lg border-l-4 flex gap-4;
}

.status-alert.status-pending {
  @apply bg-yellow-50 border-yellow-400;
}

.status-alert.status-verified {
  @apply bg-green-50 border-green-400;
}

.status-alert.status-rejected {
  @apply bg-red-50 border-red-400;
}

.status-alert.status-unverified {
  @apply bg-gray-100 border-gray-400;
}

.status-icon {
  @apply text-2xl;
}

.status-title {
  @apply font-semibold text-gray-900;
}

.status-message {
  @apply text-sm text-gray-700 mt-1;
}

.credentials-form {
  @apply bg-white rounded-lg shadow-md p-8 mb-8;
}

.empty-state {
  @apply text-center py-12 text-gray-500;
}

.credentials-list {
  @apply mb-8;
}

.credential-card {
  @apply border border-gray-300 rounded-lg p-6 mb-4;
}

.credential-header {
  @apply flex justify-between items-start mb-4;
}

.credential-header h4 {
  @apply font-semibold text-lg;
}

.badge {
  @apply px-3 py-1 rounded-full text-sm font-semibold;
}

.badge.status-verified {
  @apply bg-green-100 text-green-800;
}

.badge.status-pending {
  @apply bg-yellow-100 text-yellow-800;
}

.badge.status-rejected {
  @apply bg-red-100 text-red-800;
}

.badge.status-under_review {
  @apply bg-blue-100 text-blue-800;
}

.badge.status-draft {
  @apply bg-gray-100 text-gray-800;
}

.credential-details {
  @apply text-sm text-gray-700 space-y-2;
}

.add-credential {
  @apply border-t border-gray-200 pt-8;
}

.add-credential h3 {
  @apply text-xl font-semibold mb-6;
}

.form-group {
  @apply mb-4;
}

.form-group label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}

.form-control {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.form-row {
  @apply grid grid-cols-2 gap-4;
}

.file-info {
  @apply text-sm text-green-600 mt-2;
}

.form-actions {
  @apply mt-6;
}

.btn-primary {
  @apply px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed;
}

.submit-section {
  @apply bg-white rounded-lg shadow-md p-8 mb-8;
}

.alert-info {
  @apply bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 mb-6;
}

.btn-submit {
  @apply w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:bg-gray-400;
}

.instructions {
  @apply bg-white rounded-lg shadow-md p-8;
}

.instructions h3 {
  @apply text-xl font-semibold mb-4;
}

.instructions ul {
  @apply list-disc list-inside space-y-2 text-gray-700;
}
</style>
