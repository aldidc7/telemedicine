<template>
  <div class="appointment-booking">
    <div class="booking-container">
      <h2 class="text-2xl font-bold mb-6">Jadwalkan Konsultasi</h2>

      <!-- Step Indicator -->
      <div class="steps mb-8">
        <div :class="['step', { active: step === 1, completed: step > 1 }]">
          <div class="step-number">1</div>
          <span>Pilih Dokter</span>
        </div>
        <div :class="['step', { active: step === 2, completed: step > 2 }]">
          <div class="step-number">2</div>
          <span>Pilih Tanggal & Waktu</span>
        </div>
        <div :class="['step', { active: step === 3 }]">
          <div class="step-number">3</div>
          <span>Konfirmasi</span>
        </div>
      </div>

      <!-- Step 1: Doctor Selection -->
      <div v-if="step === 1" class="step-content">
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Cari Dokter atau Spesialisasi
          </label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nama dokter, spesialisasi, atau keahlian..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Doctor List -->
        <div v-if="doctors.length > 0" class="doctor-list">
          <div
            v-for="doctor in doctors"
            :key="doctor.id"
            :class="['doctor-card', { selected: selectedDoctor?.id === doctor.id }]"
            @click="selectDoctor(doctor)"
          >
            <div class="doctor-header">
              <div class="doctor-info">
                <h4 class="font-semibold text-lg">{{ doctor.nama }}</h4>
                <p class="text-sm text-gray-600">{{ doctor.spesialisasi }}</p>
              </div>
              <div class="doctor-rating">
                <span class="rating-star">★ {{ doctor.rating || 4.5 }}</span>
              </div>
            </div>
            <p class="text-sm text-gray-700 mt-2">{{ doctor.bio }}</p>
            <div class="doctor-meta mt-3">
              <span class="badge">Pengalaman: {{ doctor.experience_years }} tahun</span>
              <span class="badge">{{ doctor.consultations_count }} konsultasi</span>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <p class="text-gray-500">Tidak ada dokter yang ditemukan</p>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            :disabled="!selectedDoctor"
            @click="goToStep(2)"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            Lanjut
          </button>
        </div>
      </div>

      <!-- Step 2: Date & Time Selection -->
      <div v-if="step === 2" class="step-content">
        <div class="grid md:grid-cols-2 gap-8">
          <!-- Calendar -->
          <div>
            <h3 class="font-semibold mb-4">Pilih Tanggal</h3>
            <div class="calendar">
              <div class="calendar-header">
                <button @click="previousMonth" class="px-2">←</button>
                <span>{{ currentMonth }}</span>
                <button @click="nextMonth" class="px-2">→</button>
              </div>
              <div class="calendar-grid">
                <div v-for="day in 7" :key="`day-${day}`" class="weekday">
                  {{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][day] }}
                </div>
                <div
                  v-for="date in calendarDates"
                  :key="`date-${date.getTime()}`"
                  :class="['calendar-date', {
                    other: date.getMonth() !== currentDate.getMonth(),
                    selected: selectedDate && selectedDate.toDateString() === date.toDateString(),
                    disabled: date < new Date()
                  }]"
                  @click="selectDate(date)"
                >
                  {{ date.getDate() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Time Slots -->
          <div>
            <h3 class="font-semibold mb-4">Pilih Waktu</h3>
            <div v-if="selectedDate" class="time-slots">
              <p class="text-sm text-gray-600 mb-4">{{ formatDate(selectedDate) }}</p>
              <div v-if="loadingSlots" class="text-center py-8">
                <p class="text-gray-500">Memuat jadwal...</p>
              </div>
              <div v-else-if="availableSlots.length > 0" class="slots-grid">
                <button
                  v-for="slot in availableSlots"
                  :key="slot.start_time"
                  :class="['time-slot', { selected: selectedSlot === slot.datetime }]"
                  @click="selectedSlot = slot.datetime"
                >
                  {{ slot.start_time }} - {{ slot.end_time }}
                </button>
              </div>
              <div v-else class="text-center py-8">
                <p class="text-gray-500">Tidak ada jadwal tersedia</p>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">Pilih tanggal terlebih dahulu</p>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-between">
          <button
            @click="goToStep(1)"
            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
          >
            Kembali
          </button>
          <button
            :disabled="!selectedSlot"
            @click="goToStep(3)"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400"
          >
            Lanjut
          </button>
        </div>
      </div>

      <!-- Step 3: Confirmation -->
      <div v-if="step === 3" class="step-content">
        <div class="confirmation-card">
          <h3 class="font-semibold text-lg mb-6">Konfirmasi Appointment</h3>
          
          <div class="detail-row">
            <span class="label">Dokter:</span>
            <span class="value">{{ selectedDoctor?.nama }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Spesialisasi:</span>
            <span class="value">{{ selectedDoctor?.spesialisasi }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Tanggal:</span>
            <span class="value">{{ formatDate(selectedDate) }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Waktu:</span>
            <span class="value">{{ getSlotTime() }}</span>
          </div>

          <!-- Reason for Consultation -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Alasan Konsultasi
            </label>
            <select
              v-model="consultationType"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="general">Konsultasi Umum</option>
              <option value="follow_up">Follow-up</option>
              <option value="prescription">Resep Obat</option>
            </select>
          </div>

          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Deskripsi Keluhan (Opsional)
            </label>
            <textarea
              v-model="consultationNotes"
              placeholder="Jelaskan keluhan atau pertanyaan Anda..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="4"
            />
          </div>

          <!-- Terms -->
          <div class="mt-6">
            <label class="flex items-start">
              <input
                v-model="agreedToTerms"
                type="checkbox"
                class="mt-1 mr-3"
              />
              <span class="text-sm text-gray-700">
                Saya setuju dengan syarat dan ketentuan konsultasi
              </span>
            </label>
          </div>

          <!-- Button Actions -->
          <div class="mt-8 flex justify-between">
            <button
              @click="goToStep(2)"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Kembali
            </button>
            <button
              :disabled="!agreedToTerms || bookingLoading"
              @click="bookAppointment"
              class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400"
            >
              <span v-if="!bookingLoading">Konfirmasi Appointment</span>
              <span v-else>Sedang memproses...</span>
            </button>
          </div>
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
const step = ref(1)
const searchQuery = ref('')
const doctors = ref([])
const selectedDoctor = ref(null)
const selectedDate = ref(null)
const selectedSlot = ref(null)
const availableSlots = ref([])
const consultationType = ref('general')
const consultationNotes = ref('')
const agreedToTerms = ref(false)
const loadingSlots = ref(false)
const bookingLoading = ref(false)
const currentDate = ref(new Date())

// Computed
const currentMonth = computed(() => {
  const months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December']
  return `${months[currentDate.value.getMonth()]} ${currentDate.value.getFullYear()}`
})

const calendarDates = computed(() => {
  const dates = []
  const firstDay = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1)
  const lastDay = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0)
  
  // Previous month dates
  const prevDate = new Date(firstDay)
  prevDate.setDate(prevDate.getDate() - firstDay.getDay())
  
  // Generate calendar grid
  let d = new Date(prevDate)
  while (d <= lastDay || d.getDay() !== 0) {
    dates.push(new Date(d))
    d.setDate(d.getDate() + 1)
  }
  
  return dates
})

// Methods
const loadDoctors = async () => {
  try {
    const response = await appointmentService.getDoctors()
    doctors.value = response.data
  } catch (error) {
    console.error('Error loading doctors:', error)
  }
}

const selectDoctor = (doctor) => {
  selectedDoctor.value = doctor
}

const goToStep = (newStep) => {
  step.value = newStep
}

const selectDate = async (date) => {
  if (date < new Date()) return
  
  selectedDate.value = date
  selectedSlot.value = null
  
  // Load available slots
  loadingSlots.value = true
  try {
    const endDate = new Date(date)
    endDate.setDate(endDate.getDate() + 1)
    
    const response = await appointmentService.getAvailableSlots(
      selectedDoctor.value.id,
      {
        start_date: formatDateForApi(date),
        end_date: formatDateForApi(endDate),
      }
    )
    
    availableSlots.value = response.data.slots.filter(slot => {
      return slot.date === formatDateForApi(date)
    })
  } catch (error) {
    console.error('Error loading slots:', error)
  } finally {
    loadingSlots.value = false
  }
}

const previousMonth = () => {
  currentDate.value.setMonth(currentDate.value.getMonth() - 1)
  currentDate.value = new Date(currentDate.value)
}

const nextMonth = () => {
  currentDate.value.setMonth(currentDate.value.getMonth() + 1)
  currentDate.value = new Date(currentDate.value)
}

const formatDate = (date) => {
  if (!date) return ''
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  return date.toLocaleDateString('id-ID', options)
}

const formatDateForApi = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const getSlotTime = () => {
  if (!selectedSlot.value) return ''
  const slot = availableSlots.value.find(s => s.datetime === selectedSlot.value)
  return slot ? `${slot.start_time} - ${slot.end_time}` : ''
}

const bookAppointment = async () => {
  bookingLoading.value = true
  try {
    const response = await appointmentService.bookAppointment({
      doctor_id: selectedDoctor.value.id,
      scheduled_at: selectedSlot.value,
      type: 'text_consultation',
      reason: consultationNotes.value,
      consultation_type: consultationType.value,
    })
    
    // Success
    router.push({
      name: 'AppointmentSuccess',
      params: { appointmentId: response.data.id }
    })
  } catch (error) {
    console.error('Error booking appointment:', error)
  } finally {
    bookingLoading.value = false
  }
}

onMounted(() => {
  loadDoctors()
})
</script>

<style scoped>
.appointment-booking {
  @apply bg-gray-50 py-8 px-4;
}

.booking-container {
  @apply max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8;
}

.steps {
  @apply flex justify-between mb-8;
}

.step {
  @apply flex flex-col items-center text-gray-500;
}

.step.active {
  @apply text-blue-600;
}

.step.completed {
  @apply text-green-600;
}

.step-number {
  @apply w-10 h-10 rounded-full border-2 border-current flex items-center justify-center mb-2 font-semibold;
}

.doctor-card {
  @apply border border-gray-300 rounded-lg p-4 mb-4 cursor-pointer hover:border-blue-500 hover:shadow-md transition;
}

.doctor-card.selected {
  @apply border-blue-600 bg-blue-50;
}

.doctor-header {
  @apply flex justify-between items-start;
}

.doctor-info h4 {
  @apply font-semibold text-lg;
}

.rating-star {
  @apply text-yellow-500 font-semibold;
}

.badge {
  @apply inline-block px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full mr-2;
}

.calendar {
  @apply border border-gray-300 rounded-lg overflow-hidden;
}

.calendar-header {
  @apply flex justify-between items-center p-4 bg-gray-100;
}

.calendar-grid {
  @apply grid grid-cols-7 gap-0 p-4;
}

.weekday {
  @apply text-center font-semibold text-gray-600 py-2;
}

.calendar-date {
  @apply text-center py-2 cursor-pointer hover:bg-blue-100;
}

.calendar-date.other {
  @apply text-gray-400;
}

.calendar-date.selected {
  @apply bg-blue-600 text-white font-semibold;
}

.calendar-date.disabled {
  @apply text-gray-300 cursor-not-allowed hover:bg-transparent;
}

.time-slots {
  @apply border border-gray-300 rounded-lg p-4;
}

.slots-grid {
  @apply grid grid-cols-2 gap-3;
}

.time-slot {
  @apply border border-gray-300 rounded-lg py-2 px-3 text-center hover:border-blue-500 hover:bg-blue-50 transition;
}

.time-slot.selected {
  @apply bg-blue-600 text-white border-blue-600;
}

.confirmation-card {
  @apply border border-gray-300 rounded-lg p-6;
}

.detail-row {
  @apply flex justify-between py-3 border-b border-gray-200;
}

.detail-row .label {
  @apply font-semibold text-gray-700;
}

.detail-row .value {
  @apply text-gray-900;
}
</style>
