import axios from 'axios'

const API_BASE = '/api/v1'

export default {
  /**
   * Get notifications
   */
  async getNotifications(page = 1, perPage = 20, type = null) {
    const params = { page, per_page: perPage }
    if (type) params.type = type

    const response = await axios.get(`${API_BASE}/notifications`, { params })
    return response.data.data
  },

  /**
   * Get unread notifications
   */
  async getUnreadNotifications(limit = 10) {
    const response = await axios.get(`${API_BASE}/notifications/unread`, {
      params: { limit }
    })
    return response.data.data
  },

  /**
   * Get unread count
   */
  async getUnreadCount() {
    const response = await axios.get(`${API_BASE}/notifications/unread-count`)
    return response.data.data
  },

  /**
   * Mark notification as read
   */
  async markAsRead(id) {
    const response = await axios.put(`${API_BASE}/notifications/${id}/read`)
    return response.data.data
  },

  /**
   * Mark all notifications as read
   */
  async markAllAsRead() {
    const response = await axios.put(`${API_BASE}/notifications/mark-all-read`)
    return response.data.data
  },

  /**
   * Delete notification
   */
  async deleteNotification(id) {
    const response = await axios.delete(`${API_BASE}/notifications/${id}`)
    return response.data
  },

  /**
   * Clear all notifications
   */
  async clearAll() {
    const response = await axios.delete(`${API_BASE}/notifications/clear-all`)
    return response.data
  },

  /**
   * Get notification statistics
   */
  async getStats() {
    const response = await axios.get(`${API_BASE}/notifications/stats`)
    return response.data.data
  },
}
