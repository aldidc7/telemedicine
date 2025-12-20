<template>
  <div class="availability-manager">
    <!-- Header -->
    <div class="header">
      <h2>📅 Jadwal Ketersediaan</h2>
      <p>Atur jam kerja Anda dan slot konsultasi</p>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid" v-if="statistics">
      <div class="stat-card">
        <div class="stat-value">{{ statistics.total_days }}</div>
        <div class="stat-label">Hari Aktif</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ statistics.total_hours_per_week }}h</div>
        <div class="stat-label">Jam/Minggu</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ statistics.active_count }}</div>
        <div class="stat-label">Schedule Aktif</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ statistics.avg_slot_duration }}min</div>
        <div class="stat-label">Durasi Slot</div>
      </div>
    </div>

    <!-- Current Schedule List -->
    <div class="schedule-list" v-if="availabilities.length > 0">
      <h3>Jadwal Saat Ini</h3>
      
      <div class="schedule-item" v-for="schedule in availabilities" :key="schedule.id">
        <div class="schedule-header">
          <div class="day-info">
            <span class="day-name">{{ schedule.day_name }}</span>
            <span class="time-range">{{ schedule.start_time }} - {{ schedule.end_time }}</span>
          </div>
          <div class="schedule-actions">
            <button 
              @click="toggleStatus(schedule)" 
              :class="['status-btn', schedule.is_active ? 'active' : 'inactive']"
            >
              {{ schedule.is_active ? '✓ Aktif' : '✗ Nonaktif' }}
            </button>
            <button @click="editSchedule(schedule)" class="edit-btn">
              ✎ Edit
            </button>
            <button @click="deleteSchedule(schedule)" class="delete-btn">
              🗑 Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <div class="form-section">
      <h3>{{ editingId ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}</h3>
      
      <form @submit.prevent="saveSchedule" class="schedule-form">
        <!-- Day of Week -->
        <div class="form-group">
          <label>Hari</label>
          <select v-model="form.day_of_week" required>
            <option value="">Pilih hari</option>
            <option value="1">Senin</option>
            <option value="2">Selasa</option>
            <option value="3">Rabu</option>
            <option value="4">Kamis</option>
            <option value="5">Jumat</option>
            <option value="6">Sabtu</option>
            <option value="0">Minggu</option>
          </select>
        </div>

        <!-- Start Time -->
        <div class="form-group">
          <label>Jam Mulai</label>
          <input 
            v-model="form.start_time" 
            type="time" 
            required
          />
        </div>

        <!-- End Time -->
        <div class="form-group">
          <label>Jam Selesai</label>
          <input 
            v-model="form.end_time" 
            type="time" 
            required
          />
        </div>

        <!-- Slot Duration -->
        <div class="form-group">
          <label>Durasi Slot (menit)</label>
          <select v-model.number="form.slot_duration_minutes">
            <option value="15">15 menit</option>
            <option value="30">30 menit</option>
            <option value="45">45 menit</option>
            <option value="60">60 menit</option>
          </select>
        </div>

        <!-- Max Appointments -->
        <div class="form-group">
          <label>Max Pasien per Slot</label>
          <input 
            v-model.number="form.max_appointments_per_day" 
            type="number" 
            min="1" 
            max="100"
          />
        </div>

        <!-- Buttons -->
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'Menyimpan...' : editingId ? 'Update' : 'Tambah' }}
          </button>
          <button 
            v-if="editingId" 
            type="button" 
            @click="cancelEdit" 
            class="btn-secondary"
          >
            Batal
          </button>
        </div>
      </form>

      <!-- Error Message -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>

    <!-- Quick Template -->
    <div class="template-section">
      <h3>Template Cepat</h3>
      <div class="template-buttons">
        <button @click="applyFullDayTemplate" class="template-btn">
          📋 Full Day (09:00-17:00)
        </button>
        <button @click="applyMorningTemplate" class="template-btn">
          🌅 Pagi (09:00-13:00)
        </button>
        <button @click="applyAfternoonTemplate" class="template-btn">
          🌆 Sore (14:00-18:00)
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Schedule {
  id: number
  day_of_week: number
  day_name: string
  start_time: string
  end_time: string
  slot_duration_minutes: number
  max_appointments_per_day: number
  is_active: boolean
}

interface Statistics {
  total_days: number
  total_hours_per_week: number
  avg_slot_duration: number
  active_count: number
}

const availabilities = ref<Schedule[]>([])
const statistics = ref<Statistics | null>(null)
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const editingId = ref<number | null>(null)

const form = ref({
  day_of_week: '',
  start_time: '09:00',
  end_time: '17:00',
  slot_duration_minutes: 30,
  max_appointments_per_day: 20,
})

// Methods
const fetchSchedules = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/v1/doctors/availability/list', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })

    const data = await response.json()
    if (data.schedule) {
      availabilities.value = data.schedule
    }
  } catch (err) {
    error.value = 'Gagal load jadwal'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const saveSchedule = async () => {
  error.value = ''
  saving.value = true

  try {
    const endpoint = editingId.value
      ? `/api/v1/doctors/availability/${editingId.value}`
      : '/api/v1/doctors/availability'

    const method = editingId.value ? 'PATCH' : 'POST'

    const response = await fetch(endpoint, {
      method,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(form.value),
    })

    const data = await response.json()

    if (response.ok) {
      form.value = {
        day_of_week: '',
        start_time: '09:00',
        end_time: '17:00',
        slot_duration_minutes: 30,
        max_appointments_per_day: 20,
      }
      editingId.value = null
      await fetchSchedules()
    } else {
      error.value = data.error || 'Gagal menyimpan jadwal'
    }
  } catch (err) {
    error.value = 'Error menyimpan jadwal'
    console.error(err)
  } finally {
    saving.value = false
  }
}

const editSchedule = (schedule: Schedule) => {
  editingId.value = schedule.id
  form.value = {
    day_of_week: String(schedule.day_of_week),
    start_time: schedule.start_time,
    end_time: schedule.end_time,
    slot_duration_minutes: schedule.slot_duration_minutes,
    max_appointments_per_day: schedule.max_appointments_per_day,
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const cancelEdit = () => {
  editingId.value = null
  form.value = {
    day_of_week: '',
    start_time: '09:00',
    end_time: '17:00',
    slot_duration_minutes: 30,
    max_appointments_per_day: 20,
  }
}

const deleteSchedule = async (schedule: Schedule) => {
  if (!confirm(`Hapus jadwal ${schedule.day_name}?`)) return

  try {
    const response = await fetch(`/api/v1/doctors/availability/${schedule.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })

    if (response.ok) {
      await fetchSchedules()
    } else {
      error.value = 'Gagal menghapus jadwal'
    }
  } catch (err) {
    error.value = 'Error menghapus jadwal'
  }
}

const toggleStatus = async (schedule: Schedule) => {
  try {
    const response = await fetch(`/api/v1/doctors/availability/${schedule.id}`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        is_active: !schedule.is_active,
      }),
    })

    if (response.ok) {
      await fetchSchedules()
    }
  } catch (err) {
    error.value = 'Error mengubah status'
  }
}

const applyFullDayTemplate = () => {
  form.value.start_time = '09:00'
  form.value.end_time = '17:00'
}

const applyMorningTemplate = () => {
  form.value.start_time = '09:00'
  form.value.end_time = '13:00'
}

const applyAfternoonTemplate = () => {
  form.value.start_time = '14:00'
  form.value.end_time = '18:00'
}

// Lifecycle
onMounted(() => {
  fetchSchedules()
})
</script>

<style scoped lang="css">
.availability-manager {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  margin-bottom: 30px;
}

.header h2 {
  margin: 0;
  font-size: 28px;
  color: #333;
}

.header p {
  margin: 8px 0 0;
  color: #666;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.stat-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 13px;
  opacity: 0.9;
}

/* Schedule List */
.schedule-list {
  margin-bottom: 40px;
}

.schedule-list h3 {
  font-size: 18px;
  color: #333;
  margin-bottom: 15px;
}

.schedule-item {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
}

.schedule-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.day-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.day-name {
  font-weight: 600;
  font-size: 16px;
  color: #333;
  min-width: 100px;
}

.time-range {
  color: #666;
  font-size: 14px;
}

.schedule-actions {
  display: flex;
  gap: 8px;
}

.status-btn {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.status-btn.active {
  background: #4caf50;
  color: white;
}

.status-btn.inactive {
  background: #f44336;
  color: white;
}

.edit-btn,
.delete-btn {
  padding: 8px 12px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
}

.edit-btn:hover {
  background: #f5f5f5;
}

.delete-btn:hover {
  border-color: #f44336;
  color: #f44336;
}

/* Form Section */
.form-section {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
}

.form-section h3 {
  font-size: 18px;
  color: #333;
  margin-top: 0;
  margin-bottom: 20px;
}

.schedule-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 6px;
  color: #333;
}

.form-group input,
.form-group select {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  grid-column: 1 / -1;
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5568d3;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  background: white;
  border: 1px solid #ddd;
  color: #333;
}

.btn-secondary:hover {
  background: #f5f5f5;
}

.error-message {
  margin-top: 15px;
  padding: 12px;
  background: #ffebee;
  color: #c62828;
  border-radius: 4px;
  font-size: 14px;
}

/* Template Section */
.template-section {
  background: #f9f9f9;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
}

.template-section h3 {
  font-size: 16px;
  color: #333;
  margin-top: 0;
  margin-bottom: 15px;
}

.template-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.template-btn {
  padding: 10px 16px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.template-btn:hover {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

@media (max-width: 768px) {
  .schedule-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .schedule-actions {
    width: 100%;
    flex-wrap: wrap;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
