import axios from 'axios'

const API_BASE = '/api/v1'

const appointmentService = {
  /**
   * Get all doctors
   */
  getDoctors(params = {}) {
    return axios.get(`${API_BASE}/dokter`, { params })
  },

  /**
   * Get available slots for a doctor
   */
  getAvailableSlots(doctorId, params) {
    return axios.get(`${API_BASE}/doctors/${doctorId}/available-slots`, {
      params,
    })
  },

  /**
   * Get doctor's availability schedule
   */
  getDoctorAvailability(doctorId) {
    return axios.get(`${API_BASE}/doctors/${doctorId}/availability`)
  },

  /**
   * Book an appointment
   */
  bookAppointment(data) {
    return axios.post(`${API_BASE}/appointments`, data)
  },

  /**
   * Get user's appointments
   */
  getAppointments(params = {}) {
    return axios.get(`${API_BASE}/appointments`, { params })
  },

  /**
   * Get appointment details
   */
  getAppointment(appointmentId) {
    return axios.get(`${API_BASE}/appointments/${appointmentId}`)
  },

  /**
   * Confirm appointment (doctor only)
   */
  confirmAppointment(appointmentId, data = {}) {
    return axios.post(`${API_BASE}/appointments/${appointmentId}/confirm`, data)
  },

  /**
   * Cancel appointment
   */
  cancelAppointment(appointmentId, reason = '') {
    return axios.post(`${API_BASE}/appointments/${appointmentId}/cancel`, {
      reason,
    })
  },

  /**
   * Reschedule appointment
   */
  rescheduleAppointment(appointmentId, data) {
    return axios.post(`${API_BASE}/appointments/${appointmentId}/reschedule`, data)
  },

  /**
   * Rate appointment
   */
  rateAppointment(appointmentId, data) {
    return axios.post(`${API_BASE}/appointments/${appointmentId}/rate`, data)
  },

  /**
   * Get appointment statistics
   */
  getAppointmentStats() {
    return axios.get(`${API_BASE}/appointments/stats`)
  },

  /**
   * Get today's appointments
   */
  getTodayAppointments() {
    return axios.get(`${API_BASE}/appointments/today`)
  },

  /**
   * Set doctor availability (doctor only)
   */
  setAvailability(data) {
    return axios.post(`${API_BASE}/doctors/availability`, data)
  },

  /**
   * Bulk set doctor availability (doctor only)
   */
  bulkSetAvailability(schedule) {
    return axios.post(`${API_BASE}/doctors/availability/bulk`, {
      schedule,
    })
  },

  /**
   * Get doctor's availability list (doctor only)
   */
  getMyAvailability() {
    return axios.get(`${API_BASE}/doctors/availability/list`)
  },

  /**
   * Update availability (doctor only)
   */
  updateAvailability(availabilityId, data) {
    return axios.patch(`${API_BASE}/doctors/availability/${availabilityId}`, data)
  },

  /**
   * Delete availability (doctor only)
   */
  deleteAvailability(availabilityId) {
    return axios.delete(`${API_BASE}/doctors/availability/${availabilityId}`)
  },
}

export default appointmentService
