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
  @apply bg-gray-50 py-8 px-4;
}

.container {
  @apply max-w-6xl mx-auto;
}

.filter-bar {
  @apply flex gap-4 mb-6;
}

.search-input {
  @apply flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.status-filter {
  @apply px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.verifications-grid {
  @apply grid gap-6 md:grid-cols-2 lg:grid-cols-3;
}

.verification-card {
  @apply bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition;
}

.card-header {
  @apply flex justify-between items-start mb-4;
}

.doctor-name {
  @apply font-semibold text-lg;
}

.status-badge {
  @apply px-3 py-1 rounded-full text-sm font-semibold;
}

.status-badge.status-pending {
  @apply bg-yellow-100 text-yellow-800;
}

.status-badge.status-verified {
  @apply bg-green-100 text-green-800;
}

.status-badge.status-rejected {
  @apply bg-red-100 text-red-800;
}

.card-body {
  @apply text-gray-600 text-sm space-y-2;
}

.card-footer {
  @apply mt-4 pt-4 border-t border-gray-200;
}

.btn-view {
  @apply text-blue-600 hover:text-blue-700 font-semibold;
}

.empty-state {
  @apply bg-white rounded-lg shadow-md p-12 text-center text-gray-500;
}

.pagination {
  @apply flex justify-center items-center gap-4 mt-8;
}

.btn-page {
  @apply px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed;
}

.modal {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50;
}

.modal-content {
  @apply bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto;
}

.modal-lg {
  @apply max-w-4xl;
}

.modal-header {
  @apply flex justify-between items-center p-6 border-b border-gray-200;
}

.close-btn {
  @apply text-2xl text-gray-500 hover:text-gray-700 font-bold;
}

.modal-body {
  @apply p-6;
}

.doctor-info {
  @apply mb-6 p-4 bg-gray-50 rounded-lg;
}

.info-item {
  @apply flex justify-between py-2;
}

.label {
  @apply font-semibold text-gray-700;
}

.credentials-section {
  @apply mb-6;
}

.section-title {
  @apply text-lg font-semibold mb-4;
}

.credentials-list {
  @apply space-y-4;
}

.credential-item {
  @apply border border-gray-300 rounded-lg p-4 flex justify-between items-start;
}

.credential-info {
  @apply flex-1;
}

.credential-info h5 {
  @apply font-semibold text-lg;
}

.credential-info p {
  @apply text-sm text-gray-600 mt-1;
}

.credential-actions {
  @apply ml-4 flex gap-2;
}

.btn-download {
  @apply px-3 py-2 bg-gray-500 text-white rounded text-sm hover:bg-gray-600;
}

.btn-approve {
  @apply px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700;
}

.btn-reject {
  @apply px-3 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700;
}

.sub-modal {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-60;
}

.sub-modal-content {
  @apply bg-white rounded-lg p-6 max-w-md w-full;
}

.reject-textarea {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 mt-4 mb-4;
  min-height: 100px;
}

.modal-actions {
  @apply flex gap-3;
}

.modal-footer {
  @apply flex justify-between items-center p-6 border-t border-gray-200;
}

.btn-cancel {
  @apply px-4 py-2 border border-gray-300 rounded hover:bg-gray-100;
}

.btn-success {
  @apply px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700;
}

.btn-danger {
  @apply px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700;
}

.status-pending {
  @apply text-yellow-600;
}

.status-verified {
  @apply text-green-600;
}

.status-rejected {
  @apply text-red-600;
}
</style>
