/**
 * ============================================
 * LOADING & ERROR STATE COMPOSABLE
 * ============================================
 * 
 * Reusable loading dan error state management
 */

import { ref, computed } from 'vue'

export function useLoadingState() {
  const isLoading = ref(false)
  const error = ref(null)
  const success = ref(false)

  const setLoading = (value) => {
    isLoading.value = value
  }

  const setError = (errorMsg) => {
    error.value = errorMsg
    success.value = false
  }

  const setSuccess = (successMsg) => {
    success.value = successMsg
    error.value = null
  }

  const clearState = () => {
    isLoading.value = false
    error.value = null
    success.value = false
  }

  const hasError = computed(() => error.value !== null)
  const hasSuccess = computed(() => success.value !== false)

  return {
    isLoading,
    error,
    success,
    setLoading,
    setError,
    setSuccess,
    clearState,
    hasError,
    hasSuccess,
  }
}

/**
 * Async operation wrapper dengan loading & error handling
 */
export function useAsyncOperation(operation) {
  const { isLoading, error, success, setLoading, setError, setSuccess, clearState } = useLoadingState()

  const execute = async (...args) => {
    clearState()
    setLoading(true)
    try {
      const result = await operation(...args)
      setSuccess(result?.message || 'Operation successful')
      return result
    } catch (err) {
      // Safely extract error message from various error sources
      let message = 'An error occurred'
      
      if (err?.response?.data?.pesan) {
        message = err.response.data.pesan
      } else if (err?.response?.data?.message) {
        message = err.response.data.message
      } else if (err?.message) {
        message = err.message
      }
      
      setError(message)
      throw err
    } finally {
      setLoading(false)
    }
  }

  return {
    isLoading,
    error,
    success,
    execute,
    clearState,
  }
}
