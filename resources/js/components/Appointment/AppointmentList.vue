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
  background-color: rgb(249, 250, 251);
  padding: 2rem 1rem;
}

.container {
  max-width: 72rem;
  margin: 0 auto;
}

.filter-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgb(229, 231, 235);
}

.tab {
  padding: 0.75rem 1rem;
  color: rgb(75, 85, 99);
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
}

.tab:hover {
  color: rgb(31, 41, 55);
}

.tab.active {
  color: rgb(37, 99, 235);
  border-bottom-color: rgb(37, 99, 235);
  font-weight: 600;
}

.count {
  margin-left: 0.5rem;
  padding: 0.25rem 0.5rem;
  background-color: rgb(229, 231, 235);
  color: rgb(55, 65, 81);
  border-radius: 9999px;
  font-size: 0.875rem;
}

.appointment-grid {
  display: grid;
  gap: 1.5rem;
}

.appointment-card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: box-shadow 0.2s;
}

.appointment-card:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1rem;
  border-bottom: 1px solid rgb(229, 231, 235);
}

.doctor-info h4 {
  font-weight: 600;
  font-size: 1.125rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-pending {
  background-color: rgb(254, 243, 199);
  color: rgb(113, 63, 18);
}

.status-confirmed {
  background-color: rgb(219, 234, 254);
  color: rgb(30, 58, 138);
}

.status-completed {
  background-color: rgb(220, 252, 231);
  color: rgb(5, 46, 22);
}

.status-cancelled {
  background-color: rgb(254, 226, 226);
  color: rgb(127, 29, 29);
}

.card-body {
  padding: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.detail-item .label {
  color: rgb(75, 85, 99);
}

.detail-item .value {
  color: rgb(17, 24, 39);
  font-weight: 500;
}

.card-footer {
  padding: 1rem;
  background-color: rgb(249, 250, 251);
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-primary {
  padding: 0.5rem 1rem;
  background-color: rgb(37, 99, 235);
  color: white;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: rgb(29, 78, 216);
}

.btn-secondary {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(37, 99, 235);
  color: rgb(37, 99, 235);
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: rgb(239, 246, 255);
}

.btn-danger {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(220, 38, 38);
  color: rgb(220, 38, 38);
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-danger:hover {
  background-color: rgb(254, 242, 242);
}

.btn-outline {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-outline:hover {
  background-color: rgb(243, 244, 246);
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
  padding: 2rem;
  max-width: 28rem;
  width: 100%;
}

.star-rating {
  display: flex;
  gap: 0.5rem;
}

.empty-state {
  background-color: white;
  border-radius: 0.5rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}
</style>
