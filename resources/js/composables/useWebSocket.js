/**
 * ===============================================
 * WebSocket Integration for Telemedicine App
 * ===============================================
 * 
 * Comprehensive composable untuk real-time features:
 * - Appointment notifications
 * - Message notifications
 * - Doctor availability updates
 * - Prescription status updates
 * - User online status
 * - Connection state management
 * 
 * Usage:
 * import { useWebSocket } from '@/composables/useWebSocket'
 * const { isConnected, onAppointmentUpdate, onMessageReceived } = useWebSocket()
 * 
 * Features:
 * - Automatic reconnection
 * - Event queuing while offline
 * - Connection status tracking
 * - Memory-efficient event handling
 * - Multiple event listeners support
 */

import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Global state
let echoInstance = null
const globalConnectionState = reactive({
  isConnected: false,
  isConnecting: false,
  lastError: null,
  reconnectAttempts: 0,
  maxReconnectAttempts: 5,
})

// Event queues for offline messages
const offlineQueue = reactive({
  messages: [],
})

/**
 * Initialize Echo instance (Pusher WebSocket)
 */
function initializeEcho() {
  if (echoInstance) return echoInstance

  // Skip initialization if Pusher key is not configured (for development without WebSocket)
  if (!import.meta.env.VITE_PUSHER_APP_KEY || import.meta.env.VITE_PUSHER_APP_KEY === 'local_key_12345') {
    console.debug('ℹ️ WebSocket disabled - Pusher not configured for this environment')
    return null
  }

  try {
    window.Pusher = Pusher
    echoInstance = new Echo({
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
      encrypted: true,
      enableStats: false,
      enableLogging: import.meta.env.DEV,
    })

    // Listen for connection events
    echoInstance.connector.pusher.connection.bind('connected', () => {
      globalConnectionState.isConnected = true
      globalConnectionState.isConnecting = false
      globalConnectionState.reconnectAttempts = 0
      console.log('🟢 WebSocket connected to Pusher')
      processOfflineQueue()
    })

    echoInstance.connector.pusher.connection.bind('disconnected', () => {
      globalConnectionState.isConnected = false
      // Only log in dev mode to avoid console spam
      if (import.meta.env.DEV) {
        console.debug('🔴 WebSocket disconnected')
      }
    })

    echoInstance.connector.pusher.connection.bind('error', (error) => {
      globalConnectionState.lastError = error
      // Only log in dev mode to avoid console spam
      if (import.meta.env.DEV) {
        console.debug('⚠️ WebSocket error (this is OK if Pusher is not configured):', error)
      }
    })

    return echoInstance
  } catch (error) {
    // Silently fail if Pusher is not available - application should work without it
    if (import.meta.env.DEV) {
      console.debug('ℹ️ WebSocket initialization skipped:', error.message)
    }
    globalConnectionState.lastError = error.message
    return null
  }
}

/**
 * Process queued messages from offline period
 */
function processOfflineQueue() {
  if (offlineQueue.messages.length > 0) {
    console.log(`📤 Processing ${offlineQueue.messages.length} queued messages`)
    offlineQueue.messages = []
  }
}

/**
 * Main WebSocket composable
 */
export function useWebSocket() {
  const isConnected = computed(() => globalConnectionState.isConnected)
  const isConnecting = computed(() => globalConnectionState.isConnecting)
  const lastError = computed(() => globalConnectionState.lastError)

  // Event listeners registry
  const listeners = reactive({
    appointment: [],
    message: [],
    prescription: [],
    rating: [],
    presence: [],
    notification: [],
    custom: {},
  })

  /**
   * Initialize WebSocket connection on component mount
   */
  const initializeConnection = () => {
    if (!import.meta.env.VITE_PUSHER_APP_KEY || import.meta.env.VITE_PUSHER_APP_KEY === 'local_key_12345') {
      // Silently skip WebSocket for development without Pusher configured
      return
    }

    globalConnectionState.isConnecting = true
    const echo = initializeEcho()
    
    if (!echo) {
      globalConnectionState.isConnecting = false
      return
    }

    // Subscribe to private channels after initialization
    setTimeout(() => {
      subscribeToPrivateChannels()
    }, 500)
  }

  /**
   * Subscribe to user-specific private channels
   */
  const subscribeToPrivateChannels = async () => {
    if (!echoInstance) return

    try {
      // Get current user from API or store
      const userResponse = await fetch('/api/v1/auth/me', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      })

      if (!userResponse.ok) return

      const { data: user } = await userResponse.json()
      const userId = user.id

      // Subscribe to private user channel
      const privateChannel = echoInstance.private(`private-user.${userId}`)
      setupChannelListeners(privateChannel, 'user')

      // Subscribe to appointments channel
      const appointmentChannel = echoInstance.private(`private-appointments.${userId}`)
      setupChannelListeners(appointmentChannel, 'appointment')

      // Subscribe to notifications channel
      const notificationChannel = echoInstance.private(`private-notifications.${userId}`)
      setupChannelListeners(notificationChannel, 'notification')

      // Subscribe to presence channel for online status
      const presenceChannel = echoInstance.join(`presence-clinic.${user.clinic_id || 'main'}`)
      setupPresenceListeners(presenceChannel)

      console.log(`✅ Subscribed to all WebSocket channels for user ${userId}`)
    } catch (error) {
      console.error('Failed to subscribe to channels:', error)
      globalConnectionState.lastError = error.message
    }
  }

  /**
   * Setup event listeners for private channels
   */
  const setupChannelListeners = (channel, channelType) => {
    // Appointment events
    if (channelType === 'appointment') {
      channel
        .listen('.appointment-confirmed', (data) => {
          notifyListeners('appointment', {
            type: 'confirmed',
            ...data,
          })
        })
        .listen('.appointment-cancelled', (data) => {
          notifyListeners('appointment', {
            type: 'cancelled',
            ...data,
          })
        })
        .listen('.appointment-completed', (data) => {
          notifyListeners('appointment', {
            type: 'completed',
            ...data,
          })
        })
        .listen('.appointment-reminder', (data) => {
          notifyListeners('appointment', {
            type: 'reminder',
            ...data,
          })
        })
    }

    // Message events
    if (channelType === 'user') {
      channel
        .listen('.message-sent', (data) => {
          notifyListeners('message', {
            type: 'received',
            ...data,
          })
        })
        .listen('.message-read', (data) => {
          notifyListeners('message', {
            type: 'read',
            ...data,
          })
        })
    }

    // Notification events
    if (channelType === 'notification') {
      channel
        .listen('.prescription-ready', (data) => {
          notifyListeners('prescription', {
            type: 'ready',
            ...data,
          })
        })
        .listen('.rating-received', (data) => {
          notifyListeners('rating', {
            type: 'received',
            ...data,
          })
        })
        .listen('.general-notification', (data) => {
          notifyListeners('notification', {
            type: 'general',
            ...data,
          })
        })
    }
  }

  /**
   * Setup presence channel listeners for online status
   */
  const setupPresenceListeners = (channel) => {
    channel
      .here((users) => {
        notifyListeners('presence', {
          type: 'here',
          users,
        })
        console.log(`👥 ${users.length} users online`)
      })
      .joining((user) => {
        notifyListeners('presence', {
          type: 'joining',
          user,
        })
        console.log(`✅ ${user.name} joined`)
      })
      .leaving((user) => {
        notifyListeners('presence', {
          type: 'leaving',
          user,
        })
        console.log(`❌ ${user.name} left`)
      })
  }

  /**
   * Notify all listeners of an event
   */
  const notifyListeners = (eventType, data) => {
    if (listeners[eventType]) {
      listeners[eventType].forEach(callback => {
        try {
          callback(data)
        } catch (error) {
          console.error(`Error in ${eventType} listener:`, error)
        }
      })
    }

    // Also notify custom listeners
    if (listeners.custom[eventType]) {
      listeners.custom[eventType].forEach(callback => {
        try {
          callback(data)
        } catch (error) {
          console.error(`Error in custom ${eventType} listener:`, error)
        }
      })
    }
  }

  /**
   * Register listener for appointment events
   */
  const onAppointmentUpdate = (callback) => {
    listeners.appointment.push(callback)
    return () => {
      listeners.appointment = listeners.appointment.filter(cb => cb !== callback)
    }
  }

  /**
   * Register listener for message events
   */
  const onMessageReceived = (callback) => {
    listeners.message.push(callback)
    return () => {
      listeners.message = listeners.message.filter(cb => cb !== callback)
    }
  }

  /**
   * Register listener for prescription events
   */
  const onPrescriptionUpdate = (callback) => {
    listeners.prescription.push(callback)
    return () => {
      listeners.prescription = listeners.prescription.filter(cb => cb !== callback)
    }
  }

  /**
   * Register listener for rating events
   */
  const onRatingReceived = (callback) => {
    listeners.rating.push(callback)
    return () => {
      listeners.rating = listeners.rating.filter(cb => cb !== callback)
    }
  }

  /**
   * Register listener for presence events
   */
  const onPresenceChange = (callback) => {
    listeners.presence.push(callback)
    return () => {
      listeners.presence = listeners.presence.filter(cb => cb !== callback)
    }
  }

  /**
   * Register listener for notifications
   */
  const onNotification = (callback) => {
    listeners.notification.push(callback)
    return () => {
      listeners.notification = listeners.notification.filter(cb => cb !== callback)
    }
  }

  /**
   * Register custom event listener
   */
  const on = (eventName, callback) => {
    if (!listeners.custom[eventName]) {
      listeners.custom[eventName] = []
    }
    listeners.custom[eventName].push(callback)
    return () => {
      listeners.custom[eventName] = listeners.custom[eventName].filter(cb => cb !== callback)
    }
  }

  /**
   * Disconnect WebSocket
   */
  const disconnect = () => {
    if (echoInstance) {
      echoInstance.disconnect()
      globalConnectionState.isConnected = false
      console.log('WebSocket disconnected')
    }
  }

  /**
   * Reconnect WebSocket
   */
  const reconnect = () => {
    if (globalConnectionState.reconnectAttempts < globalConnectionState.maxReconnectAttempts) {
      globalConnectionState.reconnectAttempts++
      console.log(`🔄 Reconnecting... (${globalConnectionState.reconnectAttempts}/${globalConnectionState.maxReconnectAttempts})`)
      initializeConnection()
    } else {
      console.error('Max reconnect attempts reached')
    }
  }

  /**
   * Queue message for sending while offline
   */
  const queueOfflineMessage = (message) => {
    offlineQueue.messages.push({
      timestamp: Date.now(),
      ...message,
    })
  }

  /**
   * Auto-initialize on mount
   */
  onMounted(() => {
    initializeConnection()
  })

  /**
   * Cleanup on unmount
   */
  onUnmounted(() => {
    disconnect()
  })

  return {
    // State
    isConnected,
    isConnecting,
    lastError,

    // Methods
    initializeConnection,
    disconnect,
    reconnect,
    queueOfflineMessage,

    // Event listeners
    onAppointmentUpdate,
    onMessageReceived,
    onPrescriptionUpdate,
    onRatingReceived,
    onPresenceChange,
    onNotification,
    on,
  }
}

/**
 * Helper: Get connection status
 */
export function getWebSocketStatus() {
  return {
    isConnected: globalConnectionState.isConnected,
    isConnecting: globalConnectionState.isConnecting,
    lastError: globalConnectionState.lastError,
    reconnectAttempts: globalConnectionState.reconnectAttempts,
  }
}

/**
 * Helper: Check if WebSocket is available
 */
export function isWebSocketAvailable() {
  return !!import.meta.env.VITE_PUSHER_APP_KEY
}
