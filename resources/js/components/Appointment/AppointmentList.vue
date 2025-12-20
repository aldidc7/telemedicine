<template>
  <div class="appointment-list">
    <div class="container">
      <h2 class="text-2xl font-bold mb-6">Appointment Saya</h2>

      <!-- Filter Tabs -->
      <div class="filter-tabs mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          :class="['tab', { active: activeTab === tab.value }]"
          @click="activeTab = tab.value"
        >
          {{ tab.label }}
          <span class="count">{{ getCount(tab.value) }}</span>
        </button>
      </div>

      <!-- Appointment List -->
      <div v-if="filteredAppointments.length > 0" class="appointment-grid">
        <div
          v-for="appointment in filteredAppointments"
          :key="appointment.id"
          class="appointment-card"
        >
          <div class="card-header">
            <div class="doctor-info">
              <h4 class="font-semibold">{{ appointment.doctor.nama }}</h4>
              <p class="text-sm text-gray-600">{{ appointment.doctor.spesialisasi }}</p>
            </div>
            <span :class="['status-badge', `status-${appointment.status}`]">
              {{ getStatusLabel(appointment.status) }}
            </span>
          </div>

          <div class="card-body">
            <div class="detail-item">
              <span class="label">📅 Tanggal & Waktu:</span>
              <span class="value">{{ formatDateTime(appointment.scheduled_at) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">⏱️ Durasi:</span>
              <span class="value">{{ appointment.duration_minutes }} menit</span>
            </div>
            <div v-if="appointment.reason" class="detail-item">
              <span class="label">📝 Alasan:</span>
              <span class="value">{{ appointment.reason }}</span>
            </div>
            <div class="detail-item">
              <span class="label">💰 Harga:</span>
              <span class="value font-semibold">{{ formatCurrency(appointment.price) }}</span>
            </div>
          </div>

          <div class="card-footer">
            <button
              v-if="appointment.status === 'pending'"
              @click="confirmAppointment(appointment.id)"
              class="btn-primary"
            >
              Konfirmasi
            </button>
            <button
              v-if="isUpcoming(appointment.scheduled_at)"
              @click="openRescheduleModal(appointment)"
              class="btn-secondary"
            >
              Reschedule
            </button>
            <button
              v-if="canCancel(appointment.status, appointment.scheduled_at)"
              @click="cancelAppointment(appointment.id)"
              class="btn-danger"
            >
              Batalkan
            </button>
            <button
              v-if="appointment.status === 'completed' && !appointment.rating"
              @click="openRatingModal(appointment)"
              class="btn-secondary"
            >
              Beri Rating
            </button>
            <button
              @click="viewAppointmentDetails(appointment.id)"
              class="btn-outline"
            >
              Detail
            </button>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <p class="text-gray-500 text-center py-12">
          Tidak ada appointment {{ activeTab }}
        </p>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination mt-8">
        <button
          :disabled="currentPage === 1"
          @click="currentPage--"
          class="px-4 py-2 border rounded hover:bg-gray-100 disabled:opacity-50"
        >
          Sebelumnya
        </button>
        <span class="px-4 py-2">Halaman {{ currentPage }} dari {{ totalPages }}</span>
        <button
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          class="px-4 py-2 border rounded hover:bg-gray-100 disabled:opacity-50"
        >
          Selanjutnya
        </button>
      </div>
    </div>

    <!-- Reschedule Modal -->
    <div v-if="showRescheduleModal" class="modal">
      <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Jadwalkan Ulang Appointment</h3>
        <p class="text-gray-600 mb-4">{{ selectedAppointment?.doctor.nama }}</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Tanggal & Waktu Baru</label>
          <input
            v-model="newScheduleDate"
            type="datetime-local"
            class="w-full px-4 py-2 border rounded-lg"
          />
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="showRescheduleModal = false"
            class="px-4 py-2 border rounded hover:bg-gray-100"
          >
            Batal
          </button>
          <button
            @click="rescheduleAppointment"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Reschedule
          </button>
        </div>
      </div>
    </div>

    <!-- Rating Modal -->
    <div v-if="showRatingModal" class="modal">
      <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Beri Rating untuk {{ selectedAppointment?.doctor.nama }}</h3>
        
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Rating</label>
          <div class="star-rating">
            <button
              v-for="i in 5"
              :key="i"
              @click="rating = i"
              class="text-3xl"
            >
              {{ i <= rating ? '⭐' : '☆' }}
            </button>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Komentar</label>
          <textarea
            v-model="ratingComment"
            placeholder="Bagikan pengalaman Anda..."
            class="w-full px-4 py-2 border rounded-lg"
            rows="4"
          />
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="showRatingModal = false"
            class="px-4 py-2 border rounded hover:bg-gray-100"
          >
            Batal
          </button>
          <button
            @click="submitRating"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
          >
            Kirim Rating
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import appointmentService from '@/services/appointmentService'

const router = useRouter()

// State
const appointments = ref([])
const activeTab = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)
const pageSize = 10

const tabs = [
  { label: 'Semua', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Confirmed', value: 'confirmed' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]

// Modal states
const showRescheduleModal = ref(false)
const showRatingModal = ref(false)
const selectedAppointment = ref(null)
const newScheduleDate = ref('')
const rating = ref(0)
const ratingComment = ref('')

// Computed
const filteredAppointments = computed(() => {
  let filtered = appointments.value

  if (activeTab.value !== 'all') {
    filtered = filtered.filter(a => a.status === activeTab.value)
  }

  return filtered
})

// Methods
const loadAppointments = async () => {
  try {
    const response = await appointmentService.getAppointments({
      page: currentPage.value,
      per_page: pageSize,
    })

    appointments.value = response.data.data || response.data
    totalPages.value = response.data.last_page || 1
  } catch (error) {
    console.error('Error loading appointments:', error)
  }
}

const getCount = (status) => {
  if (status === 'all') {
    return appointments.value.length
  }
  return appointments.value.filter(a => a.status === status).length
}

const formatDateTime = (dateTime) => {
  return new Date(dateTime).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(value)
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Menunggu Konfirmasi',
    confirmed: 'Dikonfirmasi',
    in_progress: 'Sedang Berlangsung',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    no_show: 'Tidak Hadir',
  }
  return labels[status] || status
}

const isUpcoming = (dateTime) => {
  return new Date(dateTime) > new Date()
}

const canCancel = (status, dateTime) => {
  return ['pending', 'confirmed'].includes(status) && isUpcoming(dateTime)
}

const confirmAppointment = async (appointmentId) => {
  if (confirm('Konfirmasi appointment ini?')) {
    try {
      await appointmentService.confirmAppointment(appointmentId)
      loadAppointments()
    } catch (error) {
      console.error('Error confirming appointment:', error)
    }
  }
}

const cancelAppointment = async (appointmentId) => {
  if (confirm('Batalkan appointment ini?')) {
    try {
      await appointmentService.cancelAppointment(appointmentId)
      loadAppointments()
    } catch (error) {
      console.error('Error cancelling appointment:', error)
    }
  }
}

const openRescheduleModal = (appointment) => {
  selectedAppointment.value = appointment
  showRescheduleModal.value = true
}

const rescheduleAppointment = async () => {
  if (!newScheduleDate.value) return

  try {
    await appointmentService.rescheduleAppointment(selectedAppointment.value.id, {
      scheduled_at: newScheduleDate.value,
    })

    showRescheduleModal.value = false
    loadAppointments()
  } catch (error) {
    console.error('Error rescheduling appointment:', error)
  }
}

const openRatingModal = (appointment) => {
  selectedAppointment.value = appointment
  showRatingModal.value = true
  rating.value = 0
  ratingComment.value = ''
}

const submitRating = async () => {
  try {
    await appointmentService.rateAppointment(selectedAppointment.value.id, {
      rating: rating.value,
      comment: ratingComment.value,
    })

    showRatingModal.value = false
    loadAppointments()
  } catch (error) {
    console.error('Error submitting rating:', error)
  }
}

const viewAppointmentDetails = (appointmentId) => {
  router.push(`/appointments/${appointmentId}`)
}

onMounted(() => {
  loadAppointments()
})
</script>

<style scoped>
.appointment-list {
  @apply bg-gray-50 py-8 px-4;
}

.container {
  @apply max-w-6xl mx-auto;
}

.filter-tabs {
  @apply flex gap-4 border-b border-gray-200;
}

.tab {
  @apply px-4 py-3 text-gray-600 border-b-2 border-transparent hover:text-gray-900 transition;
}

.tab.active {
  @apply text-blue-600 border-blue-600 font-semibold;
}

.count {
  @apply ml-2 px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-sm;
}

.appointment-grid {
  @apply grid gap-6;
}

.appointment-card {
  @apply bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition;
}

.card-header {
  @apply flex justify-between items-start p-4 border-b border-gray-200;
}

.doctor-info h4 {
  @apply font-semibold text-lg;
}

.status-badge {
  @apply px-3 py-1 rounded-full text-sm font-semibold;
}

.status-pending {
  @apply bg-yellow-100 text-yellow-800;
}

.status-confirmed {
  @apply bg-blue-100 text-blue-800;
}

.status-completed {
  @apply bg-green-100 text-green-800;
}

.status-cancelled {
  @apply bg-red-100 text-red-800;
}

.card-body {
  @apply p-4;
}

.detail-item {
  @apply flex justify-between py-2;
}

.detail-item .label {
  @apply text-gray-600;
}

.detail-item .value {
  @apply text-gray-900 font-medium;
}

.card-footer {
  @apply p-4 bg-gray-50 flex gap-2 flex-wrap;
}

.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700;
}

.btn-secondary {
  @apply px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50;
}

.btn-danger {
  @apply px-4 py-2 border border-red-600 text-red-600 rounded hover:bg-red-50;
}

.btn-outline {
  @apply px-4 py-2 border border-gray-300 rounded hover:bg-gray-100;
}

.modal {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50;
}

.modal-content {
  @apply bg-white rounded-lg p-8 max-w-md w-full;
}

.star-rating {
  @apply flex gap-2;
}

.empty-state {
  @apply bg-white rounded-lg;
}

.pagination {
  @apply flex justify-center items-center gap-4;
}
</style>
