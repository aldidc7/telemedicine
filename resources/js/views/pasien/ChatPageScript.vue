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