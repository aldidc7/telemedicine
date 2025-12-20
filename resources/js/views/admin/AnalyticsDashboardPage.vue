<template>
  <div class="analytics-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <h1>Analytics Dashboard</h1>
      <div class="header-actions">
        <select v-model="selectedPeriod" @change="fetchAnalytics" class="period-selector">
          <option value="7days">Last 7 Days</option>
          <option value="30days">Last 30 Days</option>
          <option value="3months">Last 3 Months</option>
          <option value="12months">Last 12 Months</option>
        </select>
        <button @click="refreshData" class="btn-refresh">Refresh</button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading analytics data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-message">
      <p>{{ error }}</p>
      <button @click="fetchAnalytics" class="btn-retry">Retry</button>
    </div>

    <!-- Analytics Content -->
    <div v-else class="analytics-content">
      <!-- Doctor Dashboard -->
      <template v-if="userRole === 'dokter'">
        <div class="doctor-dashboard">
          <!-- KPI Cards -->
          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="kpi-label">Total Consultations</div>
              <div class="kpi-value">{{ analytics.total_consultations }}</div>
              <div class="kpi-change" :class="{ positive: monthGrowth >= 0 }">
                {{ monthGrowth >= 0 ? '+' : '' }}{{ monthGrowth }}% vs last month
              </div>
            </div>

            <div class="kpi-card">
              <div class="kpi-label">Completed</div>
              <div class="kpi-value">{{ analytics.completed_consultations }}</div>
              <div class="kpi-percentage">
                {{ calculatePercentage(analytics.completed_consultations, analytics.total_consultations) }}% of total
              </div>
            </div>

            <div class="kpi-card">
              <div class="kpi-label">Average Rating</div>
              <div class="kpi-value">{{ analytics.average_rating.toFixed(2) }} ⭐</div>
              <div class="kpi-subtext">From {{ analytics.total_ratings }} ratings</div>
            </div>

            <div class="kpi-card">
              <div class="kpi-label">Patient Satisfaction</div>
              <div class="kpi-value">{{ calculateSatisfactionPercentage() }}%</div>
              <div class="kpi-subtext">Very Satisfied + Satisfied</div>
            </div>
          </div>

          <!-- Charts -->
          <div class="charts-grid">
            <!-- Consultations Trend -->
            <div class="chart-container">
              <h3>Consultations Trend</h3>
              <canvas id="consultationsChart"></canvas>
            </div>

            <!-- Rating Distribution -->
            <div class="chart-container">
              <h3>Rating Distribution</h3>
              <canvas id="ratingDistributionChart"></canvas>
            </div>

            <!-- Consultation Types -->
            <div class="chart-container">
              <h3>Consultation Types</h3>
              <div class="consultation-types-list">
                <div v-for="type in analytics.consultation_types" :key="type.type" class="type-item">
                  <span class="type-label">{{ formatConsultationType(type.type) }}</span>
                  <span class="type-count">{{ type.count }}</span>
                </div>
              </div>
            </div>

            <!-- Patient Satisfaction -->
            <div class="chart-container">
              <h3>Patient Satisfaction</h3>
              <div class="satisfaction-bars">
                <div class="satisfaction-item">
                  <div class="satisfaction-label">Very Satisfied</div>
                  <div class="satisfaction-bar" style="width: 50px; background: #10b981;"></div>
                  <span>{{ analytics.patient_satisfaction.very_satisfied }}</span>
                </div>
                <div class="satisfaction-item">
                  <div class="satisfaction-label">Satisfied</div>
                  <div class="satisfaction-bar" style="width: 50px; background: #3b82f6;"></div>
                  <span>{{ analytics.patient_satisfaction.satisfied }}</span>
                </div>
                <div class="satisfaction-item">
                  <div class="satisfaction-label">Neutral</div>
                  <div class="satisfaction-bar" style="width: 50px; background: #f59e0b;"></div>
                  <span>{{ analytics.patient_satisfaction.neutral }}</span>
                </div>
                <div class="satisfaction-item">
                  <div class="satisfaction-label">Unsatisfied</div>
                  <div class="satisfaction-bar" style="width: 50px; background: #ef4444;"></div>
                  <span>{{ analytics.patient_satisfaction.unsatisfied }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Metrics -->
          <div class="metrics-table">
            <h3>Additional Metrics</h3>
            <table>
              <tbody>
                <tr>
                  <td>Average Consultation Duration</td>
                  <td>{{ analytics.avg_consultation_duration.toFixed(0) }} minutes</td>
                </tr>
                <tr>
                  <td>Pending Consultations</td>
                  <td>{{ analytics.pending_consultations }}</td>
                </tr>
                <tr>
                  <td>This Month Consultations</td>
                  <td>{{ analytics.this_month_consultations }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Admin Dashboard -->
      <template v-else-if="userRole === 'admin'">
        <div class="admin-dashboard">
          <!-- System-wide KPIs -->
          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="kpi-label">Total Users</div>
              <div class="kpi-value">{{ analytics.total_users }}</div>
              <div class="kpi-breakdown">
                {{ analytics.total_patients }} patients | {{ analytics.total_doctors }} doctors
              </div>
            </div>

            <div class="kpi-card">
              <div class="kpi-label">Verified Doctors</div>
              <div class="kpi-value">{{ analytics.verified_doctors }}</div>
              <div class="kpi-percentage">
                {{ calculatePercentage(analytics.verified_doctors, analytics.total_doctors) }}% of all doctors
              </div>
            </div>

            <div class="kpi-card">
              <div class="kpi-label">Total Consultations</div>
              <div class="kpi-value">{{ analytics.total_consultations }}</div>
              <div class="kpi-percentage">
                {{ calculatePercentage(analytics.completed_consultations, analytics.total_consultations) }}% completed
              </div>
            </div>

            <div class="kpi-card">
              <div class="kpi-label">Platform Rating</div>
              <div class="kpi-value">{{ analytics.avg_consultation_rating.toFixed(2) }} ⭐</div>
              <div class="kpi-subtext">Average across all consultations</div>
            </div>
          </div>

          <!-- Growth Charts -->
          <div class="charts-grid">
            <!-- Platform Growth -->
            <div class="chart-container">
              <h3>User Growth</h3>
              <canvas id="userGrowthChart"></canvas>
            </div>

            <!-- Consultation Growth -->
            <div class="chart-container">
              <h3>Consultation Growth</h3>
              <canvas id="consultationGrowthChart"></canvas>
            </div>

            <!-- Status Breakdown -->
            <div class="chart-container">
              <h3>Consultation Status</h3>
              <canvas id="statusChart"></canvas>
            </div>

            <!-- Specialization Distribution -->
            <div class="chart-container">
              <h3>Doctor Specializations</h3>
              <div class="specialization-list">
                <div v-for="spec in analytics.specialization_distribution" :key="spec.specialization" class="spec-item">
                  <span class="spec-label">{{ spec.specialization }}</span>
                  <span class="spec-count">{{ spec.count }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Doctors -->
          <div class="top-doctors-section">
            <h3>Top Doctors</h3>
            <table class="doctors-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Specialization</th>
                  <th>Rating</th>
                  <th>Consultations</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(doctor, idx) in analytics.top_doctors" :key="doctor.id">
                  <td>{{ idx + 1 }}</td>
                  <td>{{ doctor.nama }}</td>
                  <td>{{ doctor.spesialisasi }}</td>
                  <td>{{ doctor.avg_rating }} ⭐</td>
                  <td>{{ doctor.consultation_count }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios'
import Chart from 'chart.js/auto'

const authStore = useAuthStore()
const userRole = computed(() => authStore.user?.role)

const analytics = ref({})
const loading = ref(false)
const error = ref(null)
const selectedPeriod = ref('30days')

let consultationsChart = null
let ratingDistributionChart = null
let userGrowthChart = null
let consultationGrowthChart = null
let statusChart = null

const monthGrowth = computed(() => {
  const thisMonth = analytics.value.this_month_consultations || 0
  const lastMonth = analytics.value.last_month_consultations || 0
  if (lastMonth === 0) return 0
  return Math.round(((thisMonth - lastMonth) / lastMonth) * 100)
})

const fetchAnalytics = async () => {
  loading.value = true
  error.value = null

  try {
    const endpoint = userRole.value === 'dokter'
      ? '/api/v1/analytics/doctor-dashboard'
      : '/api/v1/analytics/admin-dashboard'

    const response = await axios.get(endpoint, {
      params: { period: selectedPeriod.value }
    })

    analytics.value = response.data.data
    initializeCharts()
  } catch (err) {
    error.value = err.response?.data?.error?.message || 'Failed to load analytics'
    console.error('Analytics error:', err)
  } finally {
    loading.value = false
  }
}

const initializeCharts = () => {
  if (userRole.value === 'dokter') {
    initializeDoctorCharts()
  } else if (userRole.value === 'admin') {
    initializeAdminCharts()
  }
}

const initializeDoctorCharts = () => {
  // Consultations Trend
  const ctx1 = document.getElementById('consultationsChart')
  if (ctx1) {
    if (consultationsChart) consultationsChart.destroy()
    consultationsChart = new Chart(ctx1, {
      type: 'line',
      data: {
        labels: analytics.value.consultation_by_month?.map(d => d.month) || [],
        datasets: [{
          label: 'Consultations',
          data: analytics.value.consultation_by_month?.map(d => d.count) || [],
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: true } }
      }
    })
  }

  // Rating Distribution
  const ctx2 = document.getElementById('ratingDistributionChart')
  if (ctx2) {
    if (ratingDistributionChart) ratingDistributionChart.destroy()
    const ratings = analytics.value.rating_distribution || {}
    ratingDistributionChart = new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: Object.keys(ratings).map(r => `${r} Star`),
        datasets: [{
          label: 'Count',
          data: Object.values(ratings),
          backgroundColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: false } }
      }
    })
  }
}

const initializeAdminCharts = () => {
  // User Growth
  const ctx1 = document.getElementById('userGrowthChart')
  if (ctx1) {
    if (userGrowthChart) userGrowthChart.destroy()
    userGrowthChart = new Chart(ctx1, {
      type: 'line',
      data: {
        labels: analytics.value.users_by_month?.map(d => d.month) || [],
        datasets: [{
          label: 'New Users',
          data: analytics.value.users_by_month?.map(d => d.count) || [],
          borderColor: '#8b5cf6',
          backgroundColor: 'rgba(139, 92, 246, 0.1)',
          fill: true,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: true } }
      }
    })
  }

  // Consultation Growth
  const ctx2 = document.getElementById('consultationGrowthChart')
  if (ctx2) {
    if (consultationGrowthChart) consultationGrowthChart.destroy()
    consultationGrowthChart = new Chart(ctx2, {
      type: 'line',
      data: {
        labels: analytics.value.consultations_by_month?.map(d => d.month) || [],
        datasets: [{
          label: 'Consultations',
          data: analytics.value.consultations_by_month?.map(d => d.count) || [],
          borderColor: '#06b6d4',
          backgroundColor: 'rgba(6, 182, 212, 0.1)',
          fill: true,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: true } }
      }
    })
  }

  // Status Chart
  const ctx3 = document.getElementById('statusChart')
  if (ctx3) {
    if (statusChart) statusChart.destroy()
    const statuses = analytics.value.consultation_status_breakdown || []
    statusChart = new Chart(ctx3, {
      type: 'doughnut',
      data: {
        labels: statuses.map(s => s.status),
        datasets: [{
          data: statuses.map(s => s.count),
          backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: true, position: 'bottom' } }
      }
    })
  }
}

const calculatePercentage = (value, total) => {
  if (total === 0) return 0
  return Math.round((value / total) * 100)
}

const calculateSatisfactionPercentage = () => {
  const sat = analytics.value.patient_satisfaction || {}
  const satisfied = (sat.very_satisfied || 0) + (sat.satisfied || 0)
  const total = Object.values(sat).reduce((a, b) => a + b, 0)
  return total === 0 ? 0 : Math.round((satisfied / total) * 100)
}

const formatConsultationType = (type) => {
  const types = { text: 'Text', audio: 'Audio', video: 'Video' }
  return types[type] || type
}

const refreshData = () => {
  fetchAnalytics()
}

onMounted(() => {
  fetchAnalytics()
})
</script>

<style scoped>
.analytics-dashboard {
  padding: 2rem;
  background: #f9fafb;
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  font-size: 2rem;
  font-weight: bold;
  color: #111827;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.period-selector {
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background: white;
  font-size: 0.875rem;
}

.btn-refresh {
  padding: 0.5rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-weight: 500;
}

.btn-refresh:hover {
  background: #2563eb;
}

.loading-container {
  text-align: center;
  padding: 3rem;
}

.spinner {
  width: 3rem;
  height: 3rem;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  padding: 2rem;
  background: #fee2e2;
  color: #991b1b;
  border-radius: 0.375rem;
  margin-bottom: 2rem;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.kpi-label {
  font-size: 0.875rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.kpi-value {
  font-size: 2.25rem;
  font-weight: bold;
  color: #111827;
  margin-bottom: 0.5rem;
}

.kpi-change,
.kpi-percentage,
.kpi-subtext,
.kpi-breakdown {
  font-size: 0.875rem;
  color: #6b7280;
}

.kpi-change.positive {
  color: #10b981;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-container {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-container h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.125rem;
  color: #111827;
}

.consultation-types-list,
.specialization-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.type-item,
.spec-item {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.type-label,
.spec-label {
  color: #374151;
  font-weight: 500;
}

.type-count,
.spec-count {
  color: #3b82f6;
  font-weight: bold;
}

.satisfaction-bars {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.satisfaction-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.satisfaction-label {
  width: 120px;
  font-size: 0.875rem;
  color: #374151;
}

.satisfaction-bar {
  height: 24px;
  border-radius: 0.25rem;
}

.metrics-table,
.top-doctors-section {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.metrics-table h3,
.top-doctors-section h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.125rem;
  color: #111827;
}

table {
  width: 100%;
  border-collapse: collapse;
}

table tbody tr {
  border-bottom: 1px solid #f3f4f6;
}

table tbody tr:last-child {
  border-bottom: none;
}

table td {
  padding: 1rem;
  color: #374151;
}

table thead {
  background: #f9fafb;
}

table thead th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }

  table {
    font-size: 0.875rem;
  }

  table td,
  table th {
    padding: 0.5rem;
  }
}
</style>
