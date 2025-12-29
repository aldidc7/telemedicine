/**
 * ================================================
 * CHAT MESSAGE STORE - dengan Offline Handling
 * ================================================
 * 
 * Fitur:
 * - Tracking message status (pending, sent, failed)
 * - Local storage persistence untuk failed messages
 * - Retry logic dengan exponential backoff
 * - Network availability detection
 * - Message queue untuk offline scenarios
 * 
 * Penggunaan:
 * import { useChatMessageStore } from '@/stores/chatMessageStore'
 * const chatStore = useChatMessageStore()
 * 
 * // Send message dengan error handling
 * await chatStore.sendMessage(consultationId, messageText, file)
 * 
 * // Retry failed message
 * await chatStore.retryMessage(messageId)
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { pesanAPI } from '@/api/pesan'

// ================================================
// Types
// ================================================

export const MESSAGE_STATUS = {
  PENDING: 'pending',      // Menunggu dikirim
  SENT: 'sent',           // Berhasil dikirim ke server
  FAILED: 'failed',       // Gagal dikirim
  DELIVERED: 'delivered', // Diterima server
  READ: 'read',           // Dibaca
}

export const RETRY_CONFIG = {
  MAX_RETRIES: 3,
  BASE_DELAY: 1000,      // 1 second
  MAX_DELAY: 30000,      // 30 seconds
  BACKOFF_MULTIPLIER: 2,
}

// ================================================
// Type Comments (JSDoc for IDE support)
// ================================================

/**
 * @typedef {Object} ChatMessage
 * @property {string} [id] - Local ID jika belum ter-sync dengan server
 * @property {number} [serverId] - ID dari server setelah ter-sync
 * @property {number} consultationId
 * @property {string} message
 * @property {File | null} [file]
 * @property {string} status
 * @property {string} [error]
 * @property {number} retryCount
 * @property {string} createdAt
 * @property {string} [failedAt]
 * @property {string} [lastRetryAt]
 */

/**
 * @typedef {Object} MessageQueue
 * @property {number} consultationId
 * @property {ChatMessage} message
 * @property {number} timestamp
 */

// ================================================
// Store Implementation
// ================================================

export const useChatMessageStore = defineStore('chatMessage', () => {
  // State
  const messages = ref([])
  const failedMessages = ref([])
  const messageQueue = ref([])
  const isOnline = ref(navigator.onLine)
  const isSyncing = ref(false)
  const autoRetryEnabled = ref(true)
  
  // For tracking send operations
  const activeSendOperations = ref(new Map())

  // ================================================
  // Computed Properties
  // ================================================

  const failedMessageCount = computed(() => failedMessages.value.length)
  const pendingMessageCount = computed(() => 
    messages.value.filter(m => m.status === MESSAGE_STATUS.PENDING).length
  )
  const canAutoRetry = computed(() => isOnline.value && autoRetryEnabled.value)

  // ================================================
  // Helper Functions
  // ================================================

  /**
   * Generate unique local message ID
   */
  const generateLocalId = () => {
    return `local_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
  }

  /**
   * Calculate exponential backoff delay
   */
  const calculateBackoffDelay = (retryCount) => {
    const delay = RETRY_CONFIG.BASE_DELAY * Math.pow(
      RETRY_CONFIG.BACKOFF_MULTIPLIER,
      retryCount - 1
    )
    return Math.min(delay, RETRY_CONFIG.MAX_DELAY)
  }

  /**
   * Persist messages to localStorage
   */
  const persistMessages = () => {
    try {
      const failedToStore = failedMessages.value.map(msg => ({
        ...msg,
        file: null, // Don't persist File objects
      }))
      localStorage.setItem(
        'telemedicine_failed_messages',
        JSON.stringify(failedToStore)
      )
    } catch (error) {
      console.error('Failed to persist messages:', error)
    }
  }

  /**
   * Restore failed messages from localStorage
   */
  const restoreFailedMessages = () => {
    try {
      const stored = localStorage.getItem('telemedicine_failed_messages')
      if (stored) {
        const restored = JSON.parse(stored)
        failedMessages.value = restored
      }
    } catch (error) {
      console.error('Failed to restore messages:', error)
    }
  }

  /**
   * Construct error message dari API response
   */
  const extractErrorMessage = (error) => {
    if (error.response?.data?.pesan) {
      return error.response.data.pesan
    }
    if (error.message) {
      return error.message
    }
    if (!navigator.onLine) {
      return 'Tidak ada koneksi internet. Pesan akan dikirim ulang saat online.'
    }
    return 'Gagal mengirim pesan. Silakan coba lagi.'
  }

  /**
   * Emit event untuk UI update
   */
  const notifyMessageStatusChange = (message) => {
    // Trigger reactive updates
    messages.value = [...messages.value]
    
    if (message.status === MESSAGE_STATUS.FAILED) {
      if (!failedMessages.value.find(m => m.id === message.id)) {
        failedMessages.value.push(message)
      }
    } else {
      failedMessages.value = failedMessages.value.filter(m => m.id !== message.id)
    }
    
    persistMessages()
  }

  // ================================================
  // Core Message Operations
  // ================================================

  /**
   * Add message ke queue (optimistic update untuk UI)
   */
  const addMessageToUI = (message) => {
    messages.value.push(message)
    return message
  }

  /**
   * Send message dengan automatic retry dan offline handling
   */
  const sendMessage = async (
    consultationId,
    messageText,
    file
  ) => {
    // Validasi input
    if (!messageText?.trim() && !file) {
      throw new Error('Pesan atau file harus diisi')
    }

    // Create local message object
    const localMessage = {
      id: generateLocalId(),
      consultationId,
      message: messageText || '📎 File sent',
      file: file || undefined,
      status: MESSAGE_STATUS.PENDING,
      retryCount: 0,
      createdAt: new Date().toISOString(),
    }

    // Add to UI immediately (optimistic update)
    addMessageToUI(localMessage)

    // Check if operation already in progress (prevent duplicates)
    const operationKey = `${consultationId}_${localMessage.id}`
    if (activeSendOperations.value.has(operationKey)) {
      return activeSendOperations.value.get(operationKey)
    }

    // Create send operation
    const sendOperation = (async () => {
      try {
        // Try to send message
        const sentMessage = await _attemptSendMessage(localMessage)
        
        // Mark as sent successfully
        localMessage.status = MESSAGE_STATUS.SENT
        localMessage.serverId = sentMessage.serverId
        notifyMessageStatusChange(localMessage)
        
        return localMessage
      } catch (error) {
        console.error('Failed to send message:', error)
        
        // Check if we can retry
        if (localMessage.retryCount < RETRY_CONFIG.MAX_RETRIES) {
          localMessage.status = MESSAGE_STATUS.FAILED
          localMessage.error = extractErrorMessage(error)
          localMessage.failedAt = new Date().toISOString()
          notifyMessageStatusChange(localMessage)
          
          // Auto-retry if online
          if (canAutoRetry.value) {
            await autoRetryMessage(localMessage)
          }
        } else {
          // Max retries reached
          localMessage.status = MESSAGE_STATUS.FAILED
          localMessage.error = 'Gagal mengirim pesan setelah beberapa percobaan. Silakan coba lagi nanti.'
          notifyMessageStatusChange(localMessage)
        }
        
        throw error
      } finally {
        // Clean up operation tracking
        activeSendOperations.value.delete(operationKey)
      }
    })()

    // Track operation
    activeSendOperations.value.set(operationKey, sendOperation)
    
    return sendOperation
  }

  /**
   * Attempt to send message to server (internal)
   */
  const _attemptSendMessage = async (message) => {
    const formData = new FormData()
    formData.append('consultation_id', message.consultationId.toString())
    formData.append('message', message.message)
    
    if (message.file) {
      formData.append('file', message.file)
    }

    const response = await pesanAPI.create(formData)
    
    if (!response.data?.data?.id) {
      throw new Error('Invalid server response')
    }

    return {
      serverId: response.data.data.id,
      ...response.data.data,
    }
  }

  /**
   * Auto-retry message dengan exponential backoff
   */
  const autoRetryMessage = async (message) => {
    if (message.retryCount >= RETRY_CONFIG.MAX_RETRIES) {
      return
    }

    message.retryCount++
    const delay = calculateBackoffDelay(message.retryCount)
    
    console.log(
      `Retrying message (attempt ${message.retryCount}/${RETRY_CONFIG.MAX_RETRIES}) ` +
      `in ${delay}ms`
    )

    // Wait before retry
    await new Promise(resolve => setTimeout(resolve, delay))

    // Only retry if still online
    if (!isOnline.value) {
      console.log('Offline - postponing retry until online')
      return
    }

    try {
      const sentMessage = await _attemptSendMessage(message)
      
      message.status = MESSAGE_STATUS.SENT
      message.serverId = sentMessage.serverId
      message.error = undefined
      
      failedMessages.value = failedMessages.value.filter(m => m.id !== message.id)
      notifyMessageStatusChange(message)
      
      console.log('Message retry successful!')
    } catch (error) {
      console.error('Message retry failed:', error)
      
      message.status = MESSAGE_STATUS.FAILED
      message.error = extractErrorMessage(error)
      message.lastRetryAt = new Date().toISOString()
      notifyMessageStatusChange(message)
      
      // Schedule next retry
      if (message.retryCount < RETRY_CONFIG.MAX_RETRIES && canAutoRetry.value) {
        setTimeout(() => autoRetryMessage(message), 5000)
      }
    }
  }

  /**
   * Manual retry untuk failed message
   */
  const retryMessage = async (messageId) => {
    const message = failedMessages.value.find(m => m.id === messageId)
    
    if (!message) {
      throw new Error('Message not found')
    }

    if (message.retryCount >= RETRY_CONFIG.MAX_RETRIES) {
      throw new Error('Maximum retry attempts reached')
    }

    // Update status ke pending untuk retry
    message.status = MESSAGE_STATUS.PENDING
    message.error = undefined
    notifyMessageStatusChange(message)

    try {
      message.retryCount++
      const sentMessage = await _attemptSendMessage(message)
      
      message.status = MESSAGE_STATUS.SENT
      message.serverId = sentMessage.serverId
      failedMessages.value = failedMessages.value.filter(m => m.id !== messageId)
      notifyMessageStatusChange(message)
      
      return message
    } catch (error) {
      message.status = MESSAGE_STATUS.FAILED
      message.error = extractErrorMessage(error)
      notifyMessageStatusChange(message)
      
      throw error
    }
  }

  /**
   * Retry all failed messages
   */
  const retryAllMessages = async () => {
    if (failedMessages.value.length === 0) {
      return 0
    }

    isSyncing.value = true
    let successCount = 0

    try {
      for (const message of [...failedMessages.value]) {
        try {
          await retryMessage(message.id)
          successCount++
        } catch (error) {
          console.error(`Failed to retry message ${message.id}:`, error)
          // Continue with next message
        }
      }
    } finally {
      isSyncing.value = false
    }

    return successCount
  }

  /**
   * Load messages untuk konsultasi tertentu
   */
  const loadMessages = async (consultationId) => {
    try {
      const response = await pesanAPI.getList(consultationId)
      
      if (response.data?.data) {
        const serverMessages = response.data.data.map((msg) => ({
          serverId: msg.id,
          consultationId,
          message: msg.message,
          status: MESSAGE_STATUS.SENT,
          retryCount: 0,
          createdAt: msg.created_at,
        }))
        
        // Merge dengan existing messages (avoid duplicates)
        messages.value = [
          ...serverMessages.filter(
            sm => !messages.value.find(m => m.serverId === sm.serverId)
          ),
          ...messages.value.filter(m => !m.serverId),
        ]
      }
    } catch (error) {
      console.error('Failed to load messages:', error)
      throw error
    }
  }

  /**
   * Clear message untuk konsultasi tertentu
   */
  const clearMessages = (consultationId) => {
    if (consultationId) {
      messages.value = messages.value.filter(
        m => m.consultationId !== consultationId
      )
    } else {
      messages.value = []
    }
  }

  /**
   * Update message status dari server
   */
  const updateMessageStatus = (messageId, status) => {
    const message = messages.value.find(m => m.serverId === messageId)
    if (message) {
      message.status = status
      notifyMessageStatusChange(message)
    }
  }

  // ================================================
  // Network Status Handling
  // ================================================

  /**
   * Setup network status listeners
   */
  const setupNetworkListeners = () => {
    const handleOnline = () => {
      console.log('✅ Network connection restored')
      isOnline.value = true
      
      // Auto-retry failed messages when online
      if (failedMessages.value.length > 0 && autoRetryEnabled.value) {
        console.log(`Retrying ${failedMessages.value.length} failed messages`)
        retryAllMessages()
      }
    }

    const handleOffline = () => {
      console.log('❌ Network connection lost')
      isOnline.value = false
    }

    window.addEventListener('online', handleOnline)
    window.addEventListener('offline', handleOffline)

    // Cleanup function
    return () => {
      window.removeEventListener('online', handleOnline)
      window.removeEventListener('offline', handleOffline)
    }
  }

  // ================================================
  // Utility Functions
  // ================================================

  /**
   * Get messages untuk konsultasi tertentu
   */
  const getConsultationMessages = (consultationId) => {
    return messages.value.filter(m => m.consultationId === consultationId)
  }

  /**
   * Get failed messages untuk konsultasi tertentu
   */
  const getFailedMessages = (consultationId) => {
    return failedMessages.value.filter(m => m.consultationId === consultationId)
  }

  /**
   * Mark message as read
   */
  const markAsRead = async (messageId) => {
    try {
      await pesanAPI.markAsDibaca(messageId)
      updateMessageStatus(messageId, MESSAGE_STATUS.READ)
    } catch (error) {
      console.error('Failed to mark message as read:', error)
    }
  }

  /**
   * Toggle auto-retry
   */
  const setAutoRetryEnabled = (enabled) => {
    autoRetryEnabled.value = enabled
  }

  // ================================================
  // Export
  // ================================================

  return {
    // State
    messages,
    failedMessages,
    messageQueue,
    isOnline,
    isSyncing,
    autoRetryEnabled,

    // Computed
    failedMessageCount,
    pendingMessageCount,
    canAutoRetry,

    // Methods
    sendMessage,
    retryMessage,
    retryAllMessages,
    loadMessages,
    clearMessages,
    updateMessageStatus,
    setupNetworkListeners,
    getConsultationMessages,
    getFailedMessages,
    markAsRead,
    setAutoRetryEnabled,

    // Constants
    MESSAGE_STATUS,
    RETRY_CONFIG,
  }
})
