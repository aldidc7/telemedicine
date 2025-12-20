<template>
  <div class="consultation-chat" v-if="consultation">
    <!-- Header -->
    <div class="chat-header">
      <h3>Chat Konsultasi</h3>
      <span v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }}</span>
    </div>

    <!-- Messages Area -->
    <div class="messages-container" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <p>Belum ada pesan. Mulai chat dengan dokter!</p>
      </div>

      <div 
        v-for="message in messages" 
        :key="message.id"
        :class="['message', message.sender_id === currentUserId ? 'sent' : 'received']"
      >
        <div class="message-content">
          <div class="message-header">
            <span class="sender-name">{{ message.sender_name }}</span>
            <span class="timestamp">{{ formatTime(message.created_at) }}</span>
          </div>
          <div class="message-text">{{ message.message }}</div>
          
          <!-- File attachment preview -->
          <div v-if="message.file_url" class="message-file">
            <img v-if="message.file_type === 'image'" :src="message.file_url" :alt="message.message" />
            <a v-else :href="message.file_url" target="_blank" class="file-link">
              📎 Download {{ message.file_type }}
            </a>
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
import { ref, computed, onMounted, nextTick, watchEffect } from 'vue'

interface Message {
  id: number
  consultation_id: number
  sender_id: number
  sender_name: string
  sender_avatar: string
  message: string
  file_url: string | null
  file_type: string | null
  created_at: string
  is_read: boolean
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

// State
const messages = ref<Message[]>([])
const messageInput = ref('')
const selectedFile = ref<File | null>(null)
const loading = ref(false)
const sending = ref(false)
const unreadCount = ref(0)
const messagesContainer = ref<HTMLElement | null>(null)
const consultation = ref(null)

// Computed
const apiUrl = computed(() => `/api/v1/consultations/${props.consultationId}`)

// Methods
const fetchMessages = async () => {
  loading.value = true
  try {
    const response = await fetch(`${apiUrl.value}/messages`)
    const data = await response.json()
    
    if (data.success) {
      messages.value = data.data.messages
      unreadCount.value = data.data.unread_count
      
      // Scroll to bottom
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Error fetching messages:', error)
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!messageInput.value.trim()) return

  sending.value = true
  const formData = new FormData()
  formData.append('message', messageInput.value)
  
  if (selectedFile.value) {
    formData.append('file', selectedFile.value)
  }

  try {
    const response = await fetch(`${apiUrl.value}/messages`, {
      method: 'POST',
      body: formData,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json',
      },
    })

    const data = await response.json()

    if (data.success) {
      messages.value.push(data.data)
      messageInput.value = ''
      selectedFile.value = null
      emit('message-sent', data.data)
      
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('Error sending message:', error)
  } finally {
    sending.value = false
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

// Lifecycle
onMounted(() => {
  fetchMessages()
  // Poll for new messages every 3 seconds
  setInterval(fetchMessages, 3000)
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
  font-size: 12px;
  margin-bottom: 4px;
  opacity: 0.8;
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
</style>
