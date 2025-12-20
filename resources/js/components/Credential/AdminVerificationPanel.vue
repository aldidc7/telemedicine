<template>
  <div class="credential-verification-admin">
    <div class="container">
      <h2 class="text-2xl font-bold mb-6">Verifikasi Kredensial Dokter</h2>

      <!-- Filter & Search -->
      <div class="filter-bar mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama dokter..."
          class="search-input"
        />
        <select v-model="statusFilter" class="status-filter">
          <option value="">Semua Status</option>
          <option value="pending">Menunggu</option>
          <option value="verified">Terverifikasi</option>
          <option value="rejected">Ditolak</option>
        </select>
      </div>

      <!-- Verifications List -->
      <div v-if="verifications.length > 0" class="verifications-grid">
        <div
          v-for="verification in filteredVerifications"
          :key="verification.id"
          class="verification-card"
          @click="openVerification(verification)"
        >
          <div class="card-header">
            <h4 class="doctor-name">{{ verification.doctor_name }}</h4>
            <span :class="['status-badge', `status-${verification.status}`]">
              {{ getStatusLabel(verification.status) }}
            </span>
          </div>
          <div class="card-body">
            <p class="text-sm text-gray-600">
              Credentials: {{ verification.credential_count }}
            </p>
            <p class="text-sm text-gray-600">
              Submitted: {{ formatDate(verification.submitted_at) }}
            </p>
          </div>
          <div class="card-footer">
            <button class="btn-view">Lihat Detail →</button>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <p>Tidak ada verifikasi yang perlu diproses</p>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button
          :disabled="currentPage === 1"
          @click="currentPage--"
          class="btn-page"
        >
          Sebelumnya
        </button>
        <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
        <button
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          class="btn-page"
        >
          Selanjutnya
        </button>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedVerification && showDetail" class="modal">
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <h3>Verifikasi Dokter: {{ selectedVerification.doctor.name }}</h3>
          <button @click="closeDetail" class="close-btn">×</button>
        </div>

        <div class="modal-body">
          <!-- Doctor Info -->
          <div class="doctor-info">
            <div class="info-item">
              <span class="label">Email:</span>
              <span>{{ selectedVerification.doctor.email }}</span>
            </div>
            <div class="info-item">
              <span class="label">Phone:</span>
              <span>{{ selectedVerification.doctor.phone }}</span>
            </div>
          </div>

          <!-- Credentials List -->
          <div class="credentials-section">
            <h4 class="section-title">Dokumen Kredensial</h4>
            <div class="credentials-list">
              <div
                v-for="credential in selectedVerification.credentials"
                :key="credential.id"
                class="credential-item"
              >
                <div class="credential-info">
                  <h5>{{ credential.type }}</h5>
                  <p><strong>Nomor:</strong> {{ credential.number }}</p>
                  <p><strong>Expired:</strong> {{ credential.expiry_date }}</p>
                  <p><strong>Status:</strong> <span :class="`status-${credential.status}`">{{ credential.status }}</span></p>
                  <p v-if="credential.notes"><strong>Catatan:</strong> {{ credential.notes }}</p>
                </div>
                <div class="credential-actions">
                  <button
                    v-if="credential.document_url"
                    @click="downloadDocument(credential.document_url)"
                    class="btn-download"
                  >
                    Download
                  </button>
                  <button
                    v-if="credential.status === 'pending'"
                    @click="approveCredential(credential.id)"
                    class="btn-approve"
                  >
                    Verifikasi
                  </button>
                  <button
                    v-if="credential.status === 'pending'"
                    @click="openRejectModal(credential.id)"
                    class="btn-reject"
                  >
                    Tolak
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Reject Modal -->
          <div v-if="showRejectModal" class="sub-modal">
            <div class="sub-modal-content">
              <h4>Tolak Dokumen</h4>
              <textarea
                v-model="rejectReason"
                placeholder="Alasan penolakan..."
                class="reject-textarea"
              />
              <div class="modal-actions">
                <button @click="showRejectModal = false" class="btn-cancel">Batal</button>
                <button @click="submitReject" class="btn-danger">Tolak</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            @click="closeDetail"
            class="btn-cancel"
          >
            Tutup
          </button>
          <button
            v-if="canApproveAll"
            @click="approveAll"
            class="btn-success"
          >
            Setujui Semua
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import credentialService from '@/services/credentialService'

// State
const verifications = ref([])
const selectedVerification = ref(null)
const showDetail = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const pageSize = 20

const showRejectModal = ref(false)
const selectedCredentialId = ref(null)
const rejectReason = ref('')

// Computed
const filteredVerifications = computed(() => {
  return verifications.value.filter(v => {
    const matchesSearch = v.doctor_name
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase())
    const matchesStatus = !statusFilter.value ||
      v.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

const canApproveAll = computed(() => {
  if (!selectedVerification.value) return false
  const pending = selectedVerification.value.credentials.filter(
    c => c.status === 'pending'
  )
  return pending.length === 0 && selectedVerification.value.status !== 'verified'
})

// Methods
const loadVerifications = async () => {
  try {
    const response = await credentialService.getPendingVerifications({
      page: currentPage.value,
      per_page: pageSize,
    })

    verifications.value = response.data.data
    totalPages.value = response.data.pagination.last_page
  } catch (error) {
    console.error('Error loading verifications:', error)
  }
}

const openVerification = async (verification) => {
  try {
    const response = await credentialService.getVerificationDetail(verification.id)
    selectedVerification.value = response.data
    showDetail.value = true
  } catch (error) {
    console.error('Error loading verification detail:', error)
  }
}

const closeDetail = () => {
  showDetail.value = false
  selectedVerification.value = null
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Menunggu',
    verified: 'Terverifikasi',
    rejected: 'Ditolak',
  }
  return labels[status] || status
}

const formatDate = (dateTime) => {
  return new Date(dateTime).toLocaleDateString('id-ID')
}

const downloadDocument = (url) => {
  window.open(url, '_blank')
}

const approveCredential = async (credentialId) => {
  if (!selectedVerification.value) return

  try {
    await credentialService.verifyCredentials(selectedVerification.value.id, {
      credential_ids: [credentialId],
    })

    // Reload detail
    await openVerification(selectedVerification.value)
    await loadVerifications()
  } catch (error) {
    console.error('Error approving credential:', error)
  }
}

const openRejectModal = (credentialId) => {
  selectedCredentialId.value = credentialId
  showRejectModal.value = true
  rejectReason.value = ''
}

const submitReject = async () => {
  if (!selectedVerification.value || !rejectReason.value) return

  try {
    await credentialService.rejectCredential(selectedVerification.value.id, {
      credential_id: selectedCredentialId.value,
      reason: rejectReason.value,
    })

    showRejectModal.value = false
    await openVerification(selectedVerification.value)
    await loadVerifications()
  } catch (error) {
    console.error('Error rejecting credential:', error)
  }
}

const approveAll = async () => {
  if (!selectedVerification.value) return

  try {
    await credentialService.approveVerification(
      selectedVerification.value.id,
      { notes: 'All credentials verified' }
    )

    closeDetail()
    await loadVerifications()
  } catch (error) {
    console.error('Error approving verification:', error)
  }
}

onMounted(() => {
  loadVerifications()
})
</script>

<style scoped>
.credential-verification-admin {
  background-color: rgb(249, 250, 251);
  padding: 2rem 1rem;
}

.container {
  max-width: 72rem;
  margin-left: auto;
  margin-right: auto;
}

.filter-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.search-input {
  flex: 1;
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 1px rgb(59, 130, 246);
}

.status-filter {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s;
}

.status-filter:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 1px rgb(59, 130, 246);
}

.verifications-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: 1fr;
}

@media (min-width: 768px) {
  .verifications-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .verifications-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.verification-card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.verification-card:hover {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.doctor-name {
  font-weight: 600;
  font-size: 1.125rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-badge.status-pending {
  background-color: rgb(254, 252, 232);
  color: rgb(161, 98, 7);
}

.status-badge.status-verified {
  background-color: rgb(220, 252, 231);
  color: rgb(22, 163, 74);
}

.status-badge.status-rejected {
  background-color: rgb(254, 226, 226);
  color: rgb(220, 38, 38);
}

.card-body {
  color: rgb(75, 85, 99);
  font-size: 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.card-footer {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgb(229, 231, 235);
}

.btn-view {
  color: rgb(37, 99, 235);
  font-weight: 600;
  cursor: pointer;
}

.btn-view:hover {
  color: rgb(29, 78, 216);
}

.empty-state {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 3rem;
  text-align: center;
  color: rgb(107, 114, 128);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-page {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-page:hover {
  background-color: rgb(243, 244, 246);
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 50;
}

.modal-content {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
  max-width: 42rem;
  width: 100%;
  max-height: 100vh;
  overflow-y: auto;
}

.modal-lg {
  max-width: 56rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgb(229, 231, 235);
}

.close-btn {
  font-size: 1.5rem;
  color: rgb(107, 114, 128);
  font-weight: 700;
  cursor: pointer;
  border: none;
  background: none;
}

.close-btn:hover {
  color: rgb(55, 65, 81);
}

.modal-body {
  padding: 1.5rem;
}

.doctor-info {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: rgb(243, 244, 246);
  border-radius: 0.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.label {
  font-weight: 600;
  color: rgb(55, 65, 81);
}

.credentials-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.credentials-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.credential-item {
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.credential-info {
  flex: 1;
}

.credential-info h5 {
  font-weight: 600;
  font-size: 1.125rem;
}

.credential-info p {
  font-size: 0.875rem;
  color: rgb(75, 85, 99);
  margin-top: 0.25rem;
}

.credential-actions {
  margin-left: 1rem;
  display: flex;
  gap: 0.5rem;
}

.btn-download {
  padding: 0.75rem 0.75rem;
  background-color: rgb(107, 114, 128);
  color: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
}

.btn-download:hover {
  background-color: rgb(75, 85, 99);
}

.btn-approve {
  padding: 0.75rem 0.75rem;
  background-color: rgb(22, 163, 74);
  color: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
}

.btn-approve:hover {
  background-color: rgb(16, 185, 129);
}

.btn-reject {
  padding: 0.75rem 0.75rem;
  background-color: rgb(239, 68, 68);
  color: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
}

.btn-reject:hover {
  background-color: rgb(220, 38, 38);
}

.sub-modal {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 60;
}

.sub-modal-content {
  background-color: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  max-width: 28rem;
  width: 100%;
}

.reject-textarea {
  width: 100%;
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s;
  margin-top: 1rem;
  margin-bottom: 1rem;
  min-height: 100px;
  font-family: inherit;
}

.reject-textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1), 0 0 0 1px rgb(239, 68, 68);
}

.modal-actions {
  display: flex;
  gap: 0.75rem;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-top: 1px solid rgb(229, 231, 235);
}

.btn-cancel {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background-color 0.2s;
  background: none;
  font-weight: 600;
}

.btn-cancel:hover {
  background-color: rgb(243, 244, 246);
}

.btn-success {
  padding: 0.5rem 1rem;
  background-color: rgb(22, 163, 74);
  color: white;
  border-radius: 0.375rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
  font-weight: 600;
}

.btn-success:hover {
  background-color: rgb(16, 185, 129);
}

.btn-danger {
  padding: 0.5rem 1rem;
  background-color: rgb(239, 68, 68);
  color: white;
  border-radius: 0.375rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
  font-weight: 600;
}

.btn-danger:hover {
  background-color: rgb(220, 38, 38);
}

.status-pending {
  color: rgb(202, 138, 4);
}

.status-verified {
  color: rgb(34, 197, 94);
}

.status-rejected {
  color: rgb(239, 68, 68);
}
</style>
