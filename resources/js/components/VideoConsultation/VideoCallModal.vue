<template>
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-linear-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between border-b">
        <div class="flex items-center gap-3">
          <video class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
          </video>
          <h2 class="text-xl font-bold text-white">Video Consultation</h2>
        </div>
        <button
          v-if="canClose"
          @click="closeModal"
          class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-full transition"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Status -->
        <div v-if="videoStatus" class="mb-6 p-4 rounded-lg" :class="statusClass">
          <p class="font-semibold">{{ statusMessage }}</p>
        </div>

        <!-- Video Container -->
        <div v-if="showVideoArea" class="mb-6">
          <div id="jitsi-container" class="w-full bg-black rounded-lg overflow-hidden" style="height: 500px;"></div>
        </div>

        <!-- Controls -->
        <div v-if="showControls" class="flex gap-3 justify-center">
          <!-- Toggle Recording -->
          <button
            v-if="canToggleRecording"
            @click="toggleRecording"
            :disabled="isDisabled"
            :class="[
              'px-4 py-2 rounded-lg font-semibold flex items-center gap-2 transition',
              isRecording
                ? 'bg-red-600 text-white hover:bg-red-700'
                : 'bg-gray-600 text-white hover:bg-gray-700',
              isDisabled ? 'opacity-50 cursor-not-allowed' : ''
            ]"
          >
            <span v-if="isRecording" class="w-3 h-3 bg-white rounded-full animate-pulse"></span>
            <span v-else class="w-3 h-3 bg-gray-400 rounded-full"></span>
            {{ isRecording ? 'Stop Recording' : 'Start Recording' }}
          </button>

          <!-- End Call -->
          <button
            @click="endCall"
            :disabled="isDisabled"
            class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            End Call
          </button>

          <!-- Mute -->
          <button
            @click="toggleMute"
            :disabled="isDisabled"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isMuted ? '🔇 Unmute' : '🔊 Mute' }}
          </button>
        </div>

        <!-- Duration & Quality -->
        <div v-if="isConnected" class="mt-6 grid grid-cols-2 gap-4 text-sm">
          <div class="bg-gray-100 p-3 rounded">
            <p class="text-gray-600">Duration</p>
            <p class="text-lg font-semibold">{{ formatDuration(callDuration) }}</p>
          </div>
          <div class="bg-gray-100 p-3 rounded">
            <p class="text-gray-600">Quality</p>
            <p class="text-lg font-semibold">{{ videoQuality }}</p>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          <p class="font-semibold">Error</p>
          <p>{{ errorMessage }}</p>
        </div>

        <!-- Info -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-sm text-gray-700">
            <strong>Note:</strong> This is a secure video consultation. All data is encrypted in transit.
            Recording (if enabled) is securely stored and will be automatically deleted after 30 days.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import type { Ref } from 'vue'

interface Props {
  consultationId: number
  roomName: string
  userName: string
  userEmail: string
  jwtToken: string
  onConsultationEnd?: () => void
}

const props = withDefaults(defineProps<Props>(), {})

const emit = defineEmits<{
  'recording-started': []
  'recording-stopped': [duration: number]
  'call-ended': []
  'error': [message: string]
}>()

// State
const showModal: Ref<boolean> = ref(true)
const isConnected: Ref<boolean> = ref(false)
const isRecording: Ref<boolean> = ref(false)
const isMuted: Ref<boolean> = ref(false)
const videoStatus: Ref<string | null> = ref('Connecting...')
const errorMessage: Ref<string | null> = ref(null)
const callDuration: Ref<number> = ref(0)
const videoQuality: Ref<string> = ref('HD')
let jitsiApi: any = null
let durationInterval: NodeJS.Timeout | null = null

// Computed
const isDisabled = computed(() => !isConnected.value)
const canClose = computed(() => !isConnected.value)
const canToggleRecording = computed(() => isConnected.value)
const showVideoArea = computed(() => true)
const showControls = computed(() => isConnected.value)
const statusClass = computed(() => {
  if (videoStatus.value?.includes('Error')) {
    return 'bg-red-100 border border-red-400 text-red-700'
  }
  if (videoStatus.value?.includes('Connected')) {
    return 'bg-green-100 border border-green-400 text-green-700'
  }
  return 'bg-blue-100 border border-blue-400 text-blue-700'
})
const statusMessage = computed(() => {
  if (videoStatus.value?.includes('Connected')) {
    return '✅ Connected - Video call is active'
  }
  return `⏳ ${videoStatus.value}`
})

// Methods
const initializeJitsi = async () => {
  try {
    videoStatus.value = 'Initializing Jitsi...'

    // Load Jitsi Meet script
    if (!window.JitsiMeetExternalAPI) {
      const script = document.createElement('script')
      script.src = 'https://meet.jit.si/external_api.js'
      script.async = true
      script.onload = () => {
        createJitsiInstance()
      }
      script.onerror = () => {
        handleError('Failed to load Jitsi Meet library')
      }
      document.body.appendChild(script)
    } else {
      createJitsiInstance()
    }
  } catch (error) {
    handleError(`Initialization error: ${(error as Error).message}`)
  }
}

const createJitsiInstance = () => {
  try {
    const options = {
      roomName: props.roomName,
      width: '100%',
      height: 500,
      parentNode: document.getElementById('jitsi-container'),
      jwt: props.jwtToken,
      userInfo: {
        displayName: props.userName,
        email: props.userEmail,
      },
      configOverwrite: {
        startWithAudioMuted: false,
        startWithVideoMuted: false,
        disableSimulcast: false,
      },
      interfaceConfigOverwrite: {
        SHOW_JITSI_WATERMARK: false,
        MOBILE_APP_PROMO: false,
        DEFAULT_REMOTE_DISPLAY_NAME: 'Doctor',
      },
    }

    jitsiApi = new window.JitsiMeetExternalAPI('meet.jit.si', options)

    // Event listeners
    jitsiApi.addEventListener('videoConferenceJoined', onVideoConferenceJoined)
    jitsiApi.addEventListener('videoConferenceFailed', onVideoConferenceFailed)
    jitsiApi.addEventListener('readyToClose', onReadyToClose)
    jitsiApi.addEventListener('participantJoined', onParticipantJoined)
    jitsiApi.addEventListener('participantLeft', onParticipantLeft)

    videoStatus.value = 'Connecting to conference...'
  } catch (error) {
    handleError(`Failed to initialize Jitsi: ${(error as Error).message}`)
  }
}

const onVideoConferenceJoined = () => {
  videoStatus.value = 'Connected'
  isConnected.value = true
  startDurationTimer()
}

const onVideoConferenceFailed = (error: any) => {
  handleError(`Conference failed: ${error.message}`)
}

const onReadyToClose = () => {
  endCall()
}

const onParticipantJoined = (data: any) => {
  console.log('Participant joined:', data)
}

const onParticipantLeft = (data: any) => {
  console.log('Participant left:', data)
}

const toggleRecording = async () => {
  try {
    if (isRecording.value) {
      await stopRecording()
    } else {
      await startRecording()
    }
  } catch (error) {
    handleError(`Recording error: ${(error as Error).message}`)
  }
}

const startRecording = async () => {
  try {
    const response = await fetch(`/api/video-consultations/${props.consultationId}/recording/start`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })

    if (!response.ok) {
      throw new Error('Failed to start recording')
    }

    isRecording.value = true
    emit('recording-started')
  } catch (error) {
    handleError(`Failed to start recording: ${(error as Error).message}`)
  }
}

const stopRecording = async () => {
  try {
    const response = await fetch(`/api/video-consultations/${props.consultationId}/recording/stop`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })

    if (!response.ok) {
      throw new Error('Failed to stop recording')
    }

    isRecording.value = false
    emit('recording-stopped', callDuration.value)
  } catch (error) {
    handleError(`Failed to stop recording: ${(error as Error).message}`)
  }
}

const toggleMute = () => {
  if (jitsiApi) {
    isMuted.value = !isMuted.value
    jitsiApi.executeCommand('toggleAudio')
  }
}

const endCall = async () => {
  try {
    // Stop recording if active
    if (isRecording.value) {
      await stopRecording()
    }

    // Stop duration timer
    if (durationInterval) {
      clearInterval(durationInterval)
    }

    // Notify backend
    await fetch(`/api/video-consultations/${props.consultationId}/end`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
      },
    })

    // Dispose Jitsi
    if (jitsiApi) {
      jitsiApi.dispose()
    }

    emit('call-ended')
    closeModal()
  } catch (error) {
    handleError(`Error ending call: ${(error as Error).message}`)
  }
}

const closeModal = () => {
  showModal.value = false
  if (props.onConsultationEnd) {
    props.onConsultationEnd()
  }
}

const startDurationTimer = () => {
  durationInterval = setInterval(() => {
    callDuration.value++
  }, 1000)
}

const formatDuration = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60

  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${minutes}:${secs.toString().padStart(2, '0')}`
}

const handleError = (message: string) => {
  errorMessage.value = message
  videoStatus.value = `Error: ${message}`
  emit('error', message)
}

// Lifecycle
onMounted(() => {
  initializeJitsi()
})

onBeforeUnmount(() => {
  if (durationInterval) {
    clearInterval(durationInterval)
  }
  if (jitsiApi) {
    jitsiApi.dispose()
  }
})
</script>

<style scoped>
#jitsi-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

#jitsi-container iframe {
  width: 100%;
  height: 100%;
  border-radius: 0.5rem;
}
</style>
