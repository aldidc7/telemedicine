// 📁 resources/js/api/rating.js
import axios from 'axios'

const API_BASE_URL = '/api/v1'
const token = localStorage.getItem('token')

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})

// Update token on auth changes
export const updateAuthToken = (newToken) => {
  if (newToken) {
    api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  } else {
    delete api.defaults.headers.common['Authorization']
  }
}

export const ratingAPI = {
  // Get ratings for a specific dokter
  getDokterRatings: async (dokter_id) => {
    try {
      const response = await api.get(`/ratings/dokter/${dokter_id}`)
      return response.data
    } catch (error) {
      console.error('Error fetching dokter ratings:', error)
      throw error
    }
  },

  // Get rating for a specific konsultasi
  getKonsultasiRating: async (konsultasi_id) => {
    try {
      const response = await api.get(`/ratings/konsultasi/${konsultasi_id}`)
      return response.data
    } catch (error) {
      console.error('Error fetching konsultasi rating:', error)
      throw error
    }
  },

  // Create a new rating
  create: async (ratingData) => {
    try {
      const response = await api.post('/ratings', ratingData)
      return response.data
    } catch (error) {
      console.error('Error creating rating:', error)
      throw error
    }
  },

  // Update a rating
  update: async (rating_id, ratingData) => {
    try {
      const response = await api.put(`/ratings/${rating_id}`, ratingData)
      return response.data
    } catch (error) {
      console.error('Error updating rating:', error)
      throw error
    }
  },

  // Delete a rating
  delete: async (rating_id) => {
    try {
      const response = await api.delete(`/ratings/${rating_id}`)
      return response.data
    } catch (error) {
      console.error('Error deleting rating:', error)
      throw error
    }
  }
}

export default api
