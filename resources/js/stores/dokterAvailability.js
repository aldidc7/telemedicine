import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { dokterAPI } from '@/api/dokter'
import { useAuthStore } from './auth'

export const useDokterAvailability = defineStore('dokterAvailability', () => {
  const isAvailable = ref(false)
  const isLoading = ref(false)
  const error = ref(null)

  const authStore = useAuthStore()

  // Initialize availability from auth store
  const initializeFromAuthStore = () => {
    const dokterData = authStore.user?.dokter
    if (dokterData) {
      isAvailable.value = dokterData.is_available || dokterData.tersedia || false
      console.log('Initialized availability from authStore:', isAvailable.value)
      return true
    }
    return false
  }

  // Update availability status
  const updateAvailability = async (newStatus) => {
    if (!authStore.isDokter) return
    
    isLoading.value = true
    error.value = null
    
    try {
      const dokterId = authStore.user?.dokter?.id
      if (!dokterId) {
        throw new Error('Dokter ID not found')
      }

      // Call API to update status
      await dokterAPI.updateKetersediaan(dokterId, {
        is_available: newStatus,
        tersedia: newStatus
      })

      // Update local state
      isAvailable.value = newStatus
      console.log('Availability status updated:', newStatus ? 'Online' : 'Offline')
    } catch (err) {
      console.error('Error updating availability:', err)
      error.value = err.message || 'Gagal mengubah status ketersediaan'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Fetch current availability status - try from authStore first, then API
  const fetchAvailability = async () => {
    if (!authStore.isDokter) return
    
    // Try to get from authStore first (faster, no API call)
    if (initializeFromAuthStore()) {
      return
    }
    
    isLoading.value = true
    try {
      const dokterId = authStore.user?.dokter?.id
      if (!dokterId) {
        console.warn('Dokter ID not found for fetching availability')
        return
      }

      // Create timeout promise with longer timeout (5 seconds instead of 2)
      const timeoutPromise = new Promise((resolve) => {
        setTimeout(() => {
          console.warn('Fetch availability timeout after 5 seconds')
          resolve(null)
        }, 5000) // 5 second timeout - more reasonable
      })

      const response = await Promise.race([
        dokterAPI.getDetail(dokterId),
        timeoutPromise
      ])

      if (response?.data?.data) {
        isAvailable.value = response.data.data.tersedia || response.data.data.is_available || false
        console.log('Fetched availability from API:', isAvailable.value)
      }
    } catch (err) {
      console.error('Error fetching availability:', err)
      // Don't throw, just silently fail and keep current state
    } finally {
      isLoading.value = false
    }
  }

  // Toggle availability (helper method)
  const toggleAvailability = async () => {
    try {
      await updateAvailability(!isAvailable.value)
    } catch (err) {
      // Error already logged in updateAvailability
      throw err
    }
  }

  return {
    isAvailable,
    isLoading,
    error,
    initializeFromAuthStore,
    updateAvailability,
    fetchAvailability,
    toggleAvailability
  }
})
