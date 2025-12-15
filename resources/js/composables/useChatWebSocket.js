/**
 * ===============================================
 * WebSocket Chat Client (Realtime)
 * ===============================================
 * 
 * Menghubungkan ke Laravel Broadcasting untuk
 * real-time message delivery menggunakan WebSocket
 * 
 * Penggunaan:
 * import { useChatWebSocket } from '@/composables/useChatWebSocket'
 * const chat = useChatWebSocket(konsultasiId)
 * chat.connect()
 * chat.onMessageReceived((msg) => messages.value.push(msg))
 */

import { ref, computed } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Konfigurasi Pusher
window.Pusher = Pusher
window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  encrypted: true,
})

export function useChatWebSocket(konsultasiId) {
  const isConnected = ref(false)
  const connectionError = ref(null)
  const messageHandlers = []
  const readHandlers = []
  let channel = null

  /**
   * Koneksikan ke private channel
   */
  const connect = () => {
    try {
      channel = window.Echo.private(`chat.${konsultasiId}`)

      channel
        .listen('.pesan-chat-sent', (data) => {
          isConnected.value = true
          messageHandlers.forEach(handler => handler(data))
        })
        .listen('.pesan-chat-dibaca', (data) => {
          readHandlers.forEach(handler => handler(data))
        })

      console.log(`✅ WebSocket connected to chat.${konsultasiId}`)
    } catch (error) {
      connectionError.value = error.message
      console.error('WebSocket connection error:', error)
    }
  }

  /**
   * Putus koneksi
   */
  const disconnect = () => {
    if (channel) {
      window.Echo.leaveChannel(`chat.${konsultasiId}`)
      isConnected.value = false
    }
  }

  /**
   * Listen to new messages
   */
  const onMessageReceived = (callback) => {
    messageHandlers.push(callback)
  }

  /**
   * Listen to read receipts
   */
  const onMessageRead = (callback) => {
    readHandlers.push(callback)
  }

  /**
   * Check connection status
   */
  const getConnectionStatus = () => ({
    isConnected: isConnected.value,
    error: connectionError.value,
  })

  return {
    connect,
    disconnect,
    onMessageReceived,
    onMessageRead,
    getConnectionStatus,
  }
}

/**
 * Enhanced Chat Composable dengan retry logic
 */
export function useChatWebSocketWithRetry(konsultasiId, maxRetries = 3) {
  const { 
    connect: baseConnect, 
    disconnect, 
    onMessageReceived, 
    onMessageRead, 
    getConnectionStatus 
  } = useChatWebSocket(konsultasiId)

  const retryCount = ref(0)
  const isRetrying = ref(false)

  const connect = async () => {
    try {
      baseConnect()
      retryCount.value = 0
    } catch (error) {
      if (retryCount.value < maxRetries) {
        isRetrying.value = true
        retryCount.value++
        
        // Exponential backoff: 1s, 2s, 4s
        const delay = Math.pow(2, retryCount.value - 1) * 1000
        
        await new Promise(resolve => setTimeout(resolve, delay))
        return connect()
      } else {
        throw new Error(`Failed to connect after ${maxRetries} retries`)
      }
    }
  }

  return {
    connect,
    disconnect,
    onMessageReceived,
    onMessageRead,
    getConnectionStatus,
    isRetrying,
    retryCount,
  }
}
