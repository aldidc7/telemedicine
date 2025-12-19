<!-- 📁 resources/js/views/pasien/ChatPage.vue -->
<template>
  <div class="bg-linear-to-br from-slate-50 to-white h-screen flex flex-col rounded-2xl overflow-hidden shadow-lg">
    <!-- Header with Doctor Info -->
    <div class="bg-linear-to-r from-indigo-600 to-purple-600 text-white p-6 shadow-md">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-2xl font-bold mb-1">Dr. {{ konsultasi.dokter?.name || 'Dokter' }}</h2>
          <p class="text-indigo-100 text-sm">{{ konsultasi.jenis_keluhan || 'Konsultasi' }}</p>
          <div class="flex items-center gap-2 mt-2">
            <span :class="['inline-block w-2 h-2 rounded-full', isOnline ? 'bg-green-400' : 'bg-gray-400']"></span>
            <span class="text-indigo-100 text-xs">{{ isOnline ? 'Online' : 'Offline' }}</span>
            <span v-if="typingStatus" class="text-indigo-100 text-xs ml-2">{{ typingStatus }}</span>
          </div>
        </div>
        <div class="text-right">
          <p class="text-indigo-100 text-xs">ID Konsultasi</p>
          <p class="text-sm font-mono">#{{ konsultasi.id }}</p>
        </div>
      </div>
    </div>

    <!-- Messages Container -->
    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
      <!-- Date Divider -->
      <div v-if="pesan.length > 0" class="flex items-center gap-4">
        <div class="flex-1 h-px bg-gray-300"></div>
        <p class="text-xs text-gray-500 font-medium">{{ formatDate(new Date()) }}</p>
        <div class="flex-1 h-px bg-gray-300"></div>
      </div>

      <!-- Empty State -->
      <div v-if="pesan.length === 0" class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <p class="text-gray-500 font-medium">Belum ada pesan</p>
          <p class="text-gray-400 text-sm mt-1">Mulai percakapan dengan dokter sekarang</p>
        </div>
      </div>

      <!-- Messages -->
      <div
        v-for="(msg, index) in pesan"
        :key="msg.id"
        :class="['flex', msg.pengirim_id === authStore.user?.id ? 'justify-end' : 'justify-start']"
      >
        <div class="max-w-md">
          <!-- Message Bubble -->
          <div
            :class="[
              'px-4 py-3 rounded-2xl text-sm break-all',
              msg.pengirim_id === authStore.user?.id
                ? 'bg-linear-to-r from-indigo-500 to-purple-600 text-white rounded-br-none shadow-md'
                : 'bg-gray-100 text-gray-900 rounded-bl-none shadow-sm'
            ]"
          >
            <p class="leading-relaxed">{{ msg.pesan }}</p>
            
            <!-- File Attachment Preview -->
            <div v-if="msg.file_url" class="mt-2 pt-2 border-t" :class="[msg.pengirim_id === authStore.user?.id ? 'border-purple-400' : 'border-gray-300']">
              <a :href="msg.file_url" target="_blank" class="flex items-center gap-2 text-xs hover:underline">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM3.5 5a2.5 2.5 0 100 5A2.5 2.5 0 003.5 5z" />
                </svg>
                {{ msg.file_name || 'File' }}
              </a>
            </div>
          </div>

          <!-- Time & Read Status -->
          <div :class="['flex items-center gap-2 mt-1 text-xs', msg.pengirim_id === authStore.user?.id ? 'justify-end text-purple-600' : 'text-gray-500']">
            <span>{{ formatTime(msg.created_at) }}</span>
            <svg v-if="msg.pengirim_id === authStore.user?.id && msg.is_read" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Typing Indicator -->
      <div v-if="showTypingIndicator && isOnline" class="flex items-center gap-2">
        <span class="text-xs text-gray-500">Dokter sedang mengetik</span>
        <div class="flex items-center gap-1">
          <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
          <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s;"></span>
          <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
        </div>
      </div>

      <!-- Loading Messages -->
      <div v-if="isLoadingMessages" class="text-center">
        <p class="text-xs text-gray-500">Memuat pesan...</p>
      </div>
    </div>

    <!-- Input Area -->
    <div class="border-t border-gray-200 bg-white p-4 space-y-3">
      <!-- File Upload Preview -->
      <div v-if="selectedFile" class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM3.5 5a2.5 2.5 0 100 5A2.5 2.5 0 003.5 5z" />
        </svg>
        <div class="flex-1 min-w-0">
          <p class="text-xs text-blue-900 truncate font-medium">{{ selectedFile.name }}</p>
          <p class="text-xs text-blue-700">{{ formatFileSize(selectedFile.size) }}</p>
        </div>
        <button @click="selectedFile = null" class="text-blue-600 hover:text-blue-800">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- Message Input -->
      <div class="flex gap-3">
        <!-- File Upload Button -->
        <label class="cursor-pointer text-indigo-600 hover:text-indigo-700 transition">
          <input type="file" @change="handleFileSelect" class="hidden" />
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </label>

        <!-- Text Input -->
        <input
          v-model="messageBaru"
          @keyup.enter.exact="kirimPesan"
          @input="onTyping"
          type="text"
          placeholder="Ketik pesan Anda..."
          class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
        />

        <!-- Send Button -->
        <button
          @click="kirimPesan"
          :disabled="!messageBaru.trim() && !selectedFile"
          :class="['text-white px-6 py-3 rounded-lg transition font-semibold flex items-center gap-2', !messageBaru.trim() && !selectedFile ? 'bg-gray-400 cursor-not-allowed' : 'bg-linear-to-r from-indigo-600 to-purple-600 hover:shadow-lg']"
        >
          <svg v-if="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Kirim
        </button>
      </div>

      <!-- Keyboard Shortcut Hint -->
      <p class="text-xs text-gray-500 text-center">💡 Tekan Enter untuk mengirim, Shift+Enter untuk baris baru</p>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, watch, nextTick, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { konsultasiAPI } from '@/api/konsultasi'
import { pesanAPI } from '@/api/pesan'

const route = useRoute()
const authStore = useAuthStore()

const konsultasi = ref({})
const pesan = ref([])
const messageBaru = ref('')
const messagesContainer = ref(null)

// New features
const isOnline = ref(true)
const showTypingIndicator = ref(false)
const typingStatus = ref('')
const selectedFile = ref(null)
const isLoadingMessages = ref(false)
const isSubmitting = ref(false)

let pollInterval = null
let typingTimeout = null
let typingIndicatorTimeout = null

onMounted(() => {
  loadData()
  // Polling setiap 3 detik untuk new messages
  pollInterval = setInterval(loadData, 3000)
  
  // Simulate online status (in production, use WebSocket/Pusher)
  window.addEventListener('online', () => { isOnline.value = true })
  window.addEventListener('offline', () => { isOnline.value = false })
})

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval)
  if (typingTimeout) clearTimeout(typingTimeout)
  if (typingIndicatorTimeout) clearTimeout(typingIndicatorTimeout)
  window.removeEventListener('online', () => { isOnline.value = true })
  window.removeEventListener('offline', () => { isOnline.value = false })
})

watch(pesan, () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
})

const loadData = async () => {
  isLoadingMessages.value = true
  try {
    const konsultasiRes = await konsultasiAPI.getDetail(route.params.konsultasiId)
    konsultasi.value = konsultasiRes.data.data

    const pesanRes = await pesanAPI.getList(route.params.konsultasiId)
    pesan.value = pesanRes.data.data || []
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    isLoadingMessages.value = false
  }
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const onTyping = () => {
  // Clear previous timeout
  if (typingTimeout) clearTimeout(typingTimeout)
  
  // Show typing indicator for other user
  typingStatus.value = 'Menunggu respons...'
  
  // Clear after user stops typing
  typingTimeout = setTimeout(() => {
    typingStatus.value = ''
  }, 3000)
}

const handleFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
      alert('File terlalu besar. Maksimal 10MB.')
      event.target.value = ''
      return
    }
    selectedFile.value = file
  }
}

const kirimPesan = async () => {
  if (!messageBaru.value.trim() && !selectedFile.value) return

  isSubmitting.value = true
  try {
    const formData = new FormData()
    formData.append('konsultasi_id', route.params.konsultasiId)
    formData.append('pesan', messageBaru.value || '(File)')
    formData.append('tipe_pesan', selectedFile.value ? 'file' : 'text')
    
    if (selectedFile.value) {
      formData.append('file', selectedFile.value)
    }

    await pesanAPI.create(formData)
    messageBaru.value = ''
    selectedFile.value = null
    typingStatus.value = ''
    await loadData()
  } catch (error) {
    console.error('Error sending message:', error)
    alert('Gagal mengirim pesan. Silakan coba lagi.')
  } finally {
    isSubmitting.value = false
  }
}
</script>