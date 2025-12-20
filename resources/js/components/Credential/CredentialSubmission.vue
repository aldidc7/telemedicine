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
  background-color: rgb(249, 250, 251);
  padding: 3rem 1rem;
}

.container {
  max-width: 56rem;
  margin-left: auto;
  margin-right: auto;
}

.header {
  margin-bottom: 2rem;
  text-align: center;
}

.title {
  font-size: 1.875rem;
  font-weight: 700;
  color: rgb(17, 24, 39);
  margin-bottom: 0.5rem;
}

.subtitle {
  color: rgb(75, 85, 99);
}

.status-alert {
  margin-bottom: 2rem;
  padding: 1.5rem;
  border-radius: 0.5rem;
  border-left: 4px solid;
  display: flex;
  gap: 1rem;
}

.status-alert.status-pending {
  background-color: rgb(254, 243, 224);
  border-left-color: rgb(202, 138, 4);
}

.status-alert.status-verified {
  background-color: rgb(240, 253, 244);
  border-left-color: rgb(34, 197, 94);
}

.status-alert.status-rejected {
  background-color: rgb(254, 242, 242);
  border-left-color: rgb(239, 68, 68);
}

.status-alert.status-unverified {
  background-color: rgb(243, 244, 246);
  border-left-color: rgb(107, 114, 128);
}

.status-icon {
  font-size: 1.5rem;
}

.status-title {
  font-weight: 600;
  color: rgb(17, 24, 39);
}

.status-message {
  font-size: 0.875rem;
  color: rgb(55, 65, 81);
  margin-top: 0.25rem;
}

.credentials-form {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 2rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 0;
  color: rgb(107, 114, 128);
}

.credentials-list {
  margin-bottom: 2rem;
}

.credential-card {
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.5rem;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.credential-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.credential-header h4 {
  font-weight: 600;
  font-size: 1.125rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-block;
}

.badge.status-verified {
  background-color: rgb(220, 252, 231);
  color: rgb(22, 163, 74);
}

.badge.status-pending {
  background-color: rgb(254, 252, 232);
  color: rgb(161, 98, 7);
}

.badge.status-rejected {
  background-color: rgb(254, 226, 226);
  color: rgb(220, 38, 38);
}

.badge.status-under_review {
  background-color: rgb(219, 234, 254);
  color: rgb(30, 58, 138);
}

.badge.status-draft {
  background-color: rgb(243, 244, 246);
  color: rgb(55, 65, 81);
}

.credential-details {
  font-size: 0.875rem;
  color: rgb(55, 65, 81);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.add-credential {
  border-top: 1px solid rgb(229, 231, 235);
  padding-top: 2rem;
}

.add-credential h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgb(55, 65, 81);
  margin-bottom: 0.5rem;
}

.form-control {
  width: 100%;
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 1px rgb(59, 130, 246);
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.file-info {
  font-size: 0.875rem;
  color: rgb(34, 197, 94);
  margin-top: 0.5rem;
}

.form-actions {
  margin-top: 1.5rem;
}

.btn-primary {
  padding: 0.5rem 1.5rem;
  background-color: rgb(37, 99, 235);
  color: white;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
  border: none;
  font-weight: 600;
}

.btn-primary:hover {
  background-color: rgb(29, 78, 216);
}

.btn-primary:disabled {
  background-color: rgb(209, 213, 219);
  cursor: not-allowed;
}

.submit-section {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 2rem;
}

.alert-info {
  background-color: rgb(239, 246, 255);
  border: 1px solid rgb(191, 219, 254);
  color: rgb(30, 58, 138);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.btn-submit {
  width: 100%;
  padding: 0.75rem 1.5rem;
  background-color: rgb(22, 163, 74);
  color: white;
  font-weight: 600;
  border-radius: 0.5rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
}

.btn-submit:hover {
  background-color: rgb(16, 185, 129);
}

.btn-submit:disabled {
  background-color: rgb(209, 213, 219);
  cursor: not-allowed;
}

.instructions {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}

.instructions h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.instructions ul {
  list-style-type: disc;
  list-style-position: inside;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: rgb(55, 65, 81);
}
</style>
