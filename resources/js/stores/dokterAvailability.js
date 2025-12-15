import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { dokterAPI } from '@/api/dokter'
import { useAuthStore } from './auth'

export const useDokterAvailability = defineStore('dokterAvailability', () => {
  const isAvailable = ref(false)
  const isLoading = ref(false)
  const error = ref(null)

  const authStore = useAuthStore()

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

  // Fetch current availability status
  const fetchAvailability = async () => {
    if (!authStore.isDokter) return
    
    isLoading.value = true
    try {
      const dokterId = authStore.user?.dokter?.id
      if (!dokterId) {
        console.warn('Dokter ID not found for fetching availability')
        return
      }

      // Create timeout promise
      const timeoutPromise = new Promise((resolve) => {
        setTimeout(() => {
          console.warn('Fetch availability timeout')
          resolve(null)
        }, 2000) // 2 second timeout
      })

      const response = await Promise.race([
        dokterAPI.getDetail(dokterId),
        timeoutPromise
      ])

      if (response?.data?.data) {
        isAvailable.value = response.data.data.tersedia || response.data.data.is_available || false
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
    updateAvailability,
    fetchAvailability,
    toggleAvailability
  }
})
