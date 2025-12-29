<template>
  <div class="consultation-chat" v-if="consultation">
    <!-- Header -->
    <div class="chat-header">
      <h3>Chat Konsultasi</h3>
      <span v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }}</span>
    </div>

    <!-- Offline Warning -->
    <div v-if="showOfflineWarning" class="offline-warning">
      <span class="offline-warning-icon">🔴</span>
      <span>Anda sedang offline. Pesan akan dikirim saat koneksi kembali.</span>
      <span v-if="networkStatus" class="network-status-badge" :class="networkStatus">
        {{ networkStatus === 'online' ? '🟢 Online' : '🔴 Offline' }}
      </span>
    </div>

    <!-- Failed Messages Banner -->
    <div v-if="hasFailedMessages" class="failed-messages-banner">
      <span class="failed-messages-info">
        ⚠️ {{ failedMessageCount }} pesan gagal dikirim
      </span>
      <button 
        @click="retryAllFailedMessages"
        :disabled="isRetrying"
        class="retry-all-button"
      >
        {{ isRetrying ? '⏳ Mengirim ulang...' : '🔄 Kirim Semua' }}
      </button>
    </div>

    <!-- Messages Area -->
    <div class="messages-container" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <p>Belum ada pesan. Mulai chat dengan dokter!</p>
      </div>

      <div 
        v-for="message in messages" 
        :key="message.id || message.serverId"
        :class="[
          'message', 
          message.sender_id === currentUserId || message.status === 'pending' ? 'sent' : 'received',
          { 'message-failed': message.status === 'failed' },
          { 'message-pending': message.status === 'pending' }
        ]"
      >
        <div class="message-content">
          <div class="message-header">
            <span class="sender-name">{{ message.sender_name || 'Anda' }}</span>
            <span class="timestamp">{{ formatTime(message.created_at) }}</span>
            
            <!-- Message Status Icons -->
            <span v-if="message.status === 'pending'" class="message-status-icon pending" title="Menunggu dikirim">
              ⏳
            </span>
            <span v-else-if="message.status === 'sent'" class="message-status-icon sent" title="Terkirim">
              ✓
            </span>
            <span v-else-if="message.status === 'delivered'" class="message-status-icon delivered" title="Diterima">
              ✓✓
            </span>
            <span v-else-if="message.status === 'read'" class="message-status-icon read" title="Dibaca">
              ✓✓
            </span>
            <span v-else-if="message.status === 'failed'" class="message-status-icon failed" title="Gagal dikirim">
              ✗
            </span>
          </div>
          
          <div class="message-text">{{ message.message }}</div>
          
          <!-- File attachment preview -->
          <div v-if="message.file_url" class="message-file">
            <img v-if="message.file_type === 'image'" :src="message.file_url" :alt="message.message" />
            <a v-else :href="message.file_url" target="_blank" class="file-link">
              📎 Download {{ message.file_type }}
            </a>
          </div>
          
          <!-- Error Message dan Retry Button -->
          <div v-if="message.status === 'failed'" class="message-error">
            <div class="error-message">
              <span class="error-icon">⚠️</span>
              <div class="error-details">
                <p class="error-text">{{ message.error || 'Gagal mengirim pesan' }}</p>
                <p class="retry-hint">Percobaan {{ message.retryCount }}/{{ MAX_RETRIES }}</p>
              </div>
            </div>
            <button 
              @click="handleRetryMessage(message.id)"
              :disabled="isRetrying"
              class="retry-button"
            >
              {{ isRetrying ? '⏳ Mengirim ulang...' : '🔄 Coba Lagi' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading indicator -->
      <div v-if="loading" class="loading-indicator">
        <span></span><span></span><span></span>
      </div>
    </div>

    <!-- Input Area -->
    <div class="chat-input-area">
      <div class="input-controls">
        <textarea
          v-model="messageInput"
          @keydown.enter.ctrl="sendMessage"
          @keydown.enter.exact.prevent="messageInput += '\n'"
          placeholder="Ketik pesan... (Ctrl+Enter untuk kirim)"
          class="message-input"
          rows="3"
        ></textarea>
        
        <label class="file-upload-label">
          📎
          <input 
            type="file" 
            @change="handleFileSelect" 
            accept="image/*,.pdf,.doc,.docx"
            class="file-input"
          />
        </label>
      </div>

      <button 
        @click="sendMessage" 
        :disabled="!messageInput.trim() || sending"
        class="send-button"
      >
        {{ sending ? 'Mengirim...' : 'Kirim' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watchEffect } from 'vue'
import { useChatMessageStore, MESSAGE_STATUS } from '@/stores/chatMessageStore'

interface Message {
  id?: string           // Local ID untuk pending messages
  serverId?: number     // ID dari server
  consultation_id?: number
  sender_id?: number
  sender_name?: string
  sender_avatar?: string
  message: string
  file_url?: string | null
  file_type?: string | null
  created_at: string
  is_read?: boolean
  status?: string       // pending, sent, failed, delivered, read
  error?: string        // Error message jika gagal
  retryCount?: number   // Jumlah percobaan retry
  lastRetryAt?: string  // Waktu retry terakhir
}

const props = defineProps({
  consultationId: {
    type: Number,
    required: true,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['message-sent'])

// Store
const chatStore = useChatMessageStore()

// State
const messages = ref<Message[]>([])
const messageInput = ref('')
const selectedFile = ref<File | null>(null)
const loading = ref(false)
const sending = ref(false)
const isRetrying = ref(false)
const unreadCount = ref(0)
const messagesContainer = ref<HTMLElement | null>(null)
const consultation = ref(null)
const networkStatus = ref(navigator.onLine)
const showOfflineWarning = ref(false)

// Constants
const MAX_RETRIES = chatStore.RETRY_CONFIG.MAX_RETRIES

// Computed
const apiUrl = computed(() => `/api/v1/consultations/${props.consultationId}`)
const hasFailedMessages = computed(() => 
  messages.value.some(m => m.status === MESSAGE_STATUS.FAILED)
)
const failedMessageCount = computed(() =>
  messages.value.filter(m => m.status === MESSAGE_STATUS.FAILED).length
)

// Methods
const fetchMessages = async () => {
  loading.value = true
  try {
    // Load dari store jika sudah ada
    const storeMessages = chatStore.getConsultationMessages(props.consultationId)
    if (storeMessages.length > 0) {
      messages.value = storeMessages
    } else {
      // Load dari API
      const response = await fetch(`${apiUrl.value}/messages`)
      const data = await response.json()
      
      if (data.success) {
        const apiMessages = data.data.messages.map((m: any) => ({
          ...m,
          status: MESSAGE_STATUS.SENT,
          retryCount: 0,
        }))
        
        messages.value = apiMessages
        // Store di Pinia
        apiMessages.forEach((msg: Message) => {
          const existing = storeMessages.find(m => m.serverId === msg.serverId || m.id === msg.id)
          if (!existing) {
            chatStore.messages.push(msg)
          }
        })
        
        unreadCount.value = data.data.unread_count
        
        await nextTick()
        scrollToBottom()
      }
    }
  } catch (error) {
    console.error('Error fetching messages:', error)
    showOfflineWarning.value = !networkStatus.value
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!messageInput.value.trim() && !selectedFile.value) return

  sending.value = true
  
  try {
    // Use store untuk send dengan automatic retry handling
    const message = await chatStore.sendMessage(
      props.consultationId,
      messageInput.value,
      selectedFile.value || undefined
    )
    
    // Add ke UI messages
    const uiMessage: Message = {
      id: message.id,
      serverId: message.serverId,
      consultation_id: props.consultationId,
      sender_id: props.currentUserId,
      sender_name: 'Anda',
      message: message.message,
      created_at: message.createdAt,
      status: message.status,
      error: message.error,
      retryCount: message.retryCount,
    }
    
    // Jika message belum ada di UI, tambahkan
    if (!messages.value.find(m => m.id === message.id)) {
      messages.value.push(uiMessage)
    }
    
    messageInput.value = ''
    selectedFile.value = null
    emit('message-sent', uiMessage)
    
    await nextTick()
    scrollToBottom()
    
    // Show offline warning jika online status tidak pasti
    if (!networkStatus.value) {
      showOfflineWarning.value = true
      setTimeout(() => { showOfflineWarning.value = false }, 5000)
    }
  } catch (error) {
    console.error('Error sending message:', error)
    // Error handling done by store - message marked as failed
    
    // Make sure failed message is shown
    const failedMsg = chatStore.failedMessages.find(
      m => m.consultationId === props.consultationId
    )
    if (failedMsg && !messages.value.find(m => m.id === failedMsg.id)) {
      messages.value.push({
        id: failedMsg.id,
        consultation_id: props.consultationId,
        sender_id: props.currentUserId,
        sender_name: 'Anda',
        message: failedMsg.message,
        created_at: failedMsg.createdAt,
        status: failedMsg.status,
        error: failedMsg.error,
        retryCount: failedMsg.retryCount,
      })
    }
  } finally {
    sending.value = false
  }
}

/**
 * Retry failed message
 */
const handleRetryMessage = async (messageId: string) => {
  if (isRetrying.value) return

  isRetrying.value = true
  
  try {
    const message = await chatStore.retryMessage(messageId)
    
    // Update UI message
    const uiMsg = messages.value.find(m => m.id === messageId)
    if (uiMsg) {
      uiMsg.status = message.status
      uiMsg.serverId = message.serverId
      uiMsg.error = message.error
      uiMsg.retryCount = message.retryCount
    }
    
    // Emit untuk notify parent
    emit('message-sent', message)
    
    // Scroll to bottom if message was sent
    if (message.status === MESSAGE_STATUS.SENT) {
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Retry failed:', error)
    
    // Update UI dengan error status
    const msg = messages.value.find(m => m.id === messageId)
    if (msg) {
      msg.status = MESSAGE_STATUS.FAILED
      msg.error = 'Gagal mengirim ulang. Silakan coba lagi nanti.'
    }
  } finally {
    isRetrying.value = false
  }
}

/**
 * Retry all failed messages
 */
const retryAllFailedMessages = async () => {
  isRetrying.value = true
  
  try {
    const successCount = await chatStore.retryAllMessages()
    
    // Refresh messages display
    const storeMessages = chatStore.getConsultationMessages(props.consultationId)
    messages.value = storeMessages.length > 0 ? storeMessages : messages.value
    
    if (successCount > 0) {
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Batch retry failed:', error)
  } finally {
    isRetrying.value = false
  }
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Handle network status changes
 */
const handleNetworkOnline = () => {
  networkStatus.value = true
  console.log('✅ Online - Attempting to send failed messages')
  
  // Auto-retry failed messages
  if (hasFailedMessages.value) {
    setTimeout(() => {
      retryAllFailedMessages()
    }, 1000)
  }
}

const handleNetworkOffline = () => {
  networkStatus.value = false
  showOfflineWarning.value = true
  console.log('❌ Offline - Messages will be retried when online')
}

// Lifecycle
let pollInterval: number
let cleanupNetworkListeners: (() => void) | null = null

onMounted(() => {
  // Initialize store network listeners
  cleanupNetworkListeners = chatStore.setupNetworkListeners()
  
  // Load messages
  fetchMessages()
  
  // Setup polling untuk new messages
  pollInterval = window.setInterval(() => {
    fetchMessages()
  }, 3000)
  
  // Setup network status listeners
  window.addEventListener('online', handleNetworkOnline)
  window.addEventListener('offline', handleNetworkOffline)
  
  // Sync messages dari store
  watchEffect(() => {
    const storeMessages = chatStore.getConsultationMessages(props.consultationId)
    if (storeMessages.length > messages.value.length) {
      messages.value = [
        ...messages.value,
        ...storeMessages.filter(
          sm => !messages.value.find(m => m.id === sm.id || m.serverId === sm.serverId)
        ),
      ]
    }
  })
})

onUnmounted(() => {
  // Cleanup
  clearInterval(pollInterval)
  
  if (cleanupNetworkListeners) {
    cleanupNetworkListeners()
  }
  
  window.removeEventListener('online', handleNetworkOnline)
  window.removeEventListener('offline', handleNetworkOffline)
})
</script>

<style scoped lang="css">
.consultation-chat {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chat-header {
  padding: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
}

.unread-badge {
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #999;
  font-style: italic;
}

.message {
  display: flex;
  margin-bottom: 8px;
}

.message.sent {
  justify-content: flex-end;
}

.message.received {
  justify-content: flex-start;
}

.message-content {
  max-width: 70%;
  padding: 10px 14px;
  border-radius: 12px;
  word-wrap: break-word;
}

.message.sent .message-content {
  background: #667eea;
  color: white;
}

.message.received .message-content {
  background: #f0f0f0;
  color: #333;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  margin-bottom: 4px;
  opacity: 0.8;
  gap: 8px;
}

.sender-name {
  font-weight: 600;
  margin-right: 8px;
}

.timestamp {
  font-size: 11px;
}

.message-text {
  line-height: 1.4;
  white-space: pre-wrap;
}

.message-file {
  margin-top: 8px;
  border-top: 1px solid currentColor;
  padding-top: 8px;
  opacity: 0.9;
}

.message-file img {
  max-width: 100%;
  border-radius: 6px;
  margin-top: 4px;
}

.file-link {
  color: inherit;
  text-decoration: underline;
  display: block;
  margin-top: 4px;
}

.loading-indicator {
  display: flex;
  justify-content: center;
  gap: 4px;
  padding: 8px;
}

.loading-indicator span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #667eea;
  animation: bounce 1.4s infinite;
}

.loading-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.loading-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes bounce {
  0%, 80%, 100% {
    opacity: 0.3;
    transform: translateY(0);
  }
  40% {
    opacity: 1;
    transform: translateY(-8px);
  }
}

.chat-input-area {
  padding: 12px;
  border-top: 1px solid #eee;
  display: flex;
  gap: 8px;
}

.input-controls {
  flex: 1;
  display: flex;
  gap: 8px;
}

.message-input {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
  font-size: 14px;
  resize: none;
  max-height: 100px;
}

.message-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.file-upload-label {
  width: 40px;
  height: 40px;
  padding: 10px;
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: background 0.2s;
}

.file-upload-label:hover {
  background: #efefef;
}

.file-input {
  display: none;
}

.send-button {
  padding: 10px 20px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.send-button:hover:not(:disabled) {
  background: #5568d3;
}

.send-button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* ========================================
   MESSAGE STATUS INDICATORS
   ======================================== */

.message-status-icon {
  display: inline-block;
  margin-left: 4px;
  font-size: 12px;
  opacity: 0.7;
}

.message-status-icon.pending {
  animation: pulse 1.5s ease-in-out infinite;
}

.message-status-icon.sent {
  color: #667eea;
}

.message-status-icon.delivered {
  color: #0fb881;
}

.message-status-icon.read {
  color: #0fb881;
  opacity: 1;
}

.message-status-icon.failed {
  color: #ff4757;
  animation: shake 0.3s ease-in-out;
}

/* ========================================
   FAILED MESSAGE STYLING
   ======================================== */

.message.message-failed {
  opacity: 0.85;
}

.message.message-failed .message-content {
  border: 2px solid #ff4757;
  padding: 10px 12px;
}

.message.message-pending {
  opacity: 0.7;
}

.message-error {
  margin-top: 8px;
  padding: 8px;
  background: rgba(255, 71, 87, 0.1);
  border-left: 3px solid #ff4757;
  border-radius: 4px;
  font-size: 12px;
}

.error-message {
  display: flex;
  gap: 6px;
  margin-bottom: 8px;
  color: #c92a2a;
}

.error-icon {
  flex-shrink: 0;
  font-size: 14px;
}

.error-details {
  flex: 1;
}

.error-text {
  margin: 0;
  font-weight: 500;
  line-height: 1.3;
}

.retry-hint {
  margin: 4px 0 0 0;
  font-size: 11px;
  opacity: 0.7;
}

.retry-button {
  width: 100%;
  padding: 6px 10px;
  background: #ff4757;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
}

.retry-button:hover:not(:disabled) {
  background: #ff3838;
  transform: scale(0.98);
}

.retry-button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.retry-button:active:not(:disabled) {
  transform: scale(0.95);
}

/* ========================================
   OFFLINE WARNING
   ======================================== */

.offline-warning {
  padding: 12px;
  background: #fff3cd;
  border-bottom: 2px solid #ffc107;
  color: #856404;
  font-size: 13px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  animation: slideDown 0.3s ease-out;
}

.offline-warning-icon {
  font-size: 16px;
}

.network-status-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 12px;
  margin-left: auto;
}

.network-status-badge.online {
  background: #d4edda;
  color: #155724;
}

.network-status-badge.offline {
  background: #f8d7da;
  color: #721c24;
}

.failed-messages-banner {
  padding: 10px 12px;
  background: #f8d7da;
  border-bottom: 2px solid #f5c6cb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  gap: 8px;
}

.failed-messages-info {
  color: #721c24;
  font-weight: 500;
}

.retry-all-button {
  padding: 4px 10px;
  background: #ff4757;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s;
}

.retry-all-button:hover:not(:disabled) {
  background: #ff3838;
}

.retry-all-button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* ========================================
   ANIMATIONS
   ======================================== */

@keyframes pulse {
  0%, 100% {
    opacity: 0.5;
  }
  50% {
    opacity: 1;
  }
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-2px);
  }
  75% {
    transform: translateX(2px);
  }
}

@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
