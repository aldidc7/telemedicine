import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import ErrorHandler from '@/utils/ErrorHandler'
import RequestLogger from '@/utils/RequestLogger'

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'
  // Don't set default Content-Type - let axios auto-detect based on data
})

// Request interceptor
client.interceptors.request.use(
  config => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    
    // IMPORTANT: For FormData, axios automatically handles multipart/form-data
    // For regular JSON, set Content-Type to application/json
    if (config.data instanceof FormData) {
      // Don't set Content-Type for FormData - axios will set it with boundary
      // Explicitly delete if it was set, just to be safe
      delete config.headers['Content-Type']
    } else if (config.data && typeof config.data === 'object' && !Array.isArray(config.data)) {
      // Set JSON content type for regular objects/JSON data
      config.headers['Content-Type'] = 'application/json'
    }
    
    // Add request timestamp untuk tracking
    config.metadata = { startTime: performance.now() }
    
    return config
  },
  error => Promise.reject(error)
)

// Response interceptor
client.interceptors.response.use(
  response => {
    // Response logging sudah ditangani oleh RequestLogger
    return response
  },
  error => {
    // Handle authentication errors
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
      return Promise.reject(error)
    }

    // Log error - skip Pusher/broadcasting errors in development
    const isWebSocketError = error.config?.url?.includes('pusher') || 
                           error.config?.url?.includes('broadcasting')
    
    if (!isWebSocketError || import.meta.env.PROD) {
      const duration = error.config?.metadata 
        ? performance.now() - error.config.metadata.startTime 
        : 0
      
      if (import.meta.env.DEV && !isWebSocketError) {
        console.error(`[ERROR] ${error.config?.method?.toUpperCase()} ${error.config?.url}`, {
          status: error.response?.status,
          message: error.message,
          duration: `${duration.toFixed(2)}ms`,
        })
      }
    }

    // Attach error handler to error object
    error.userMessage = ErrorHandler.getUserMessage(error)
    
    return Promise.reject(error)
  }
)

// Setup request logger dengan client instance yang sudah initialized
RequestLogger.setup(client)

export default client