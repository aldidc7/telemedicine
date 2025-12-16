import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import ErrorHandler from '@/utils/ErrorHandler'
import RequestLogger from '@/utils/RequestLogger'

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request interceptor
client.interceptors.request.use(
  config => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    
    // IMPORTANT: For FormData (file uploads), let axios set Content-Type automatically
    // with proper multipart/form-data and boundary
    if (config.data instanceof FormData) {
      // Delete Content-Type so axios can set it with correct boundary
      delete config.headers['Content-Type']
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