<template>
  <div class="video-call-container">
    <!-- Jitsi Container -->
    <div id="jitsi-container" class="jitsi-container"></div>

    <!-- Fallback UI (if Jitsi SDK not loaded) -->
    <div v-if="!jitsiLoaded" class="fallback-ui">
      <div class="loader">
        <div class="spinner"></div>
        <p>Memuat Jitsi Meet...</p>
      </div>
    </div>

    <!-- Connection Status -->
    <div v-if="connectionStatus !== 'connected'" :class="['connection-status', connectionStatus]">
      <span class="status-indicator"></span>
      <span class="status-text">{{ getStatusText }}</span>
    </div>

    <!-- Toolbar (Custom if needed) -->
    <div class="custom-toolbar" v-if="showToolbar">
      <div class="toolbar-info">
        <span class="timer">{{ callDuration }}</span>
        <span class="participant-count">{{ participantCount }} peserta</span>
      </div>
      <div class="toolbar-controls">
        <button @click="toggleChat" class="toolbar-btn" title="Chat">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"></path>
          </svg>
        </button>
        <button @click="toggleParticipants" class="toolbar-btn" title="Peserta">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM9 6a3 3 0 11-6 0 3 3 0 016 0zm12 0a3 3 0 11-6 0 3 3 0 016 0zm0 0a3 3 0 11-6 0 3 3 0 016 0zM6 12a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z"></path>
          </svg>
        </button>
        <button @click="endCall" class="toolbar-btn end-btn" title="End Call">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="error-alert">
      <button @click="error = null" class="close-btn">✕</button>
      <span>{{ error }}</span>
    </div>

    <!-- Success Alert -->
    <div v-if="success" class="success-alert">
      <span>{{ success }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Refs
const jitsiLoaded = ref(false)
const connectionStatus = ref('connecting') // connecting, connected, disconnected
const error = ref(null)
const success = ref(null)
const callDuration = ref('00:00')
const participantCount = ref(1)
const showToolbar = ref(true)

// State
let jitsiAPI = null
let callTimer = null
let sessionId = null
let jitsiToken = null

const getStatusText = computed(() => {
  const statuses = {
    connecting: 'Menghubungkan...',
    connected: 'Terhubung',
    disconnected: 'Putus sambungan'
  }
  return statuses[connectionStatus.value] || 'Siap'
})

/**
 * Load Jitsi SDK
 */
const loadJitsiSDK = () => {
  return new Promise((resolve, reject) => {
    if (window.JitsiMeetExternalAPI) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = 'https://meet.jit.si/external_api.js'
    script.async = true
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })
}

/**
 * Initialize Jitsi Meeting
 */
const initializeJitsi = async () => {
  try {
    // Load SDK
    await loadJitsiSDK()

    // Get session and token
    const sessionId = route.params.sessionId
    if (!sessionId) {
      throw new Error('Session ID tidak ditemukan')
    }

    // Fetch Jitsi token
    const tokenResponse = await fetch(
      `/api/v1/video-sessions/${sessionId}/jitsi-token`,
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json',
        }
      }
    )

    if (!tokenResponse.ok) {
      throw new Error('Gagal mendapatkan Jitsi token')
    }

    const tokenData = await tokenResponse.json()
    jitsiToken = tokenData.data.token
    const roomName = tokenData.data.room_name
    const serverUrl = tokenData.data.server_url
    const userName = tokenData.data.participant_name
    const isDoctor = tokenData.data.is_doctor

    // Initialize Jitsi API
    const options = {
      roomName: roomName,
      jwt: jitsiToken,
      height: '100%',
      parentNode: document.getElementById('jitsi-container'),
      configOverwrite: {
        startAudioOnly: false,
        startVideoMuted: !isDoctor,
        startScreenSharing: false,
        disableAudioLevels: false,
        disableSimulcast: false,
        enableLayerSuspension: true,
        resolution: 720,
        constraints: {
          video: {
            height: {
              ideal: 720,
              max: 720,
              min: 360,
            },
            width: {
              ideal: 1280,
              max: 1280,
              min: 640,
            },
          },
        },
        audioQuality: {
          stereo: 8000,
          mono: 16000,
        },
        disableSpeakerIndicator: false,
      },
      interfaceConfigOverwrite: {
        DISABLE_AUDIO_LEVELS: false,
        SHOW_JITSI_WATERMARK: false,
        SHOW_WATERMARK_FOR_GUESTS: false,
        DISABLE_INVITE: false,
        DISABLE_PROFILE: false,
        HIDE_INVITE_MORE_HEADER: false,
        DEFAULT_BACKGROUND: '#000000',
        MOBILE_APP_PROMO: false,
        LANG_DETECTION: true,
        DEFAULT_LANGUAGE: 'id',
        SHOW_CHROME_EXTENSION_BANNER: false,
        SETTINGS_SECTIONS: [
          'devices',
          'language',
          'moderator',
          'profile',
          'about',
          'shortcuts'
        ],
        TOOLBAR_BUTTONS: [
          'microphone',
          'camera',
          'desktop',
          'fullscreen',
          'fodeviceselection',
          'hangup',
          'chat',
          'recording',
          'livestreaming',
          'etherpad',
          'sharedvideo',
          'settings',
          'raisehand',
          'videoquality',
          'filmstrip',
          'invite',
          'feedback',
          'stats',
          'shortcuts',
          'tileview',
          'download',
          'help',
          'mute-everyone',
          'mute-video-everyone'
        ],
      },
      userInfo: {
        displayName: userName,
      },
    }

    // Create Jitsi API instance
    jitsiAPI = new window.JitsiMeetExternalAPI(
      serverUrl.replace('https://', ''),
      options
    )

    jitsiLoaded.value = true

    // Register event listeners
    jitsiAPI.addEventListener('videoConferenceJoined', () => {
      connectionStatus.value = 'connected'
      startCallTimer()
      success.value = 'Terhubung ke video call'
      setTimeout(() => { success.value = null }, 3000)
    })

    jitsiAPI.addEventListener('videoConferenceLocked', () => {
      // Handle room locked
    })

    jitsiAPI.addEventListener('videoConferenceLeft', () => {
      connectionStatus.value = 'disconnected'
      stopCallTimer()
    })

    jitsiAPI.addEventListener('participantJoined', (id) => {
      participantCount.value++
      logEvent('participant_joined', { participant_id: id })
    })

    jitsiAPI.addEventListener('participantLeft', (id) => {
      participantCount.value = Math.max(1, participantCount.value - 1)
      logEvent('participant_left', { participant_id: id })
    })

    jitsiAPI.addEventListener('audioAvailabilityChanged', (available) => {
      logEvent('audio_availability', { available })
    })

    jitsiAPI.addEventListener('audioMuted', () => {
      logEvent('audio_muted')
    })

    jitsiAPI.addEventListener('audioUnmuted', () => {
      logEvent('audio_unmuted')
    })

    jitsiAPI.addEventListener('videoMuted', () => {
      logEvent('video_muted')
    })

    jitsiAPI.addEventListener('videoUnmuted', () => {
      logEvent('video_unmuted')
    })

    jitsiAPI.addEventListener('connectionStatusChanged', (connectionStatus) => {
      logEvent('connection_status', { status: connectionStatus })
    })

    jitsiAPI.addEventListener('dominantSpeakerChanged', (id) => {
      logEvent('dominant_speaker', { participant_id: id })
    })

    jitsiAPI.addEventListener('error', (error) => {
      console.error('Jitsi Error:', error)
      logEvent('jitsi_error', { error: error.toString() })
      error.value = `Jitsi Error: ${error.toString()}`
    })

  } catch (err) {
    console.error('Failed to initialize Jitsi:', err)
    error.value = `Gagal menginisialisasi Jitsi: ${err.message}`
    connectionStatus.value = 'disconnected'
  }
}

/**
 * Call Timer
 */
const startCallTimer = () => {
  let seconds = 0
  callTimer = setInterval(() => {
    seconds++
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60

    callDuration.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }, 1000)
}

const stopCallTimer = () => {
  if (callTimer) {
    clearInterval(callTimer)
  }
}

/**
 * Log Event
 */
const logEvent = async (eventType, data = {}) => {
  try {
    const sessionId = route.params.sessionId
    await fetch(`/api/v1/video-sessions/${sessionId}/jitsi-event`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        event_type: eventType,
        timestamp: new Date().toISOString(),
        data,
      }),
    })
  } catch (err) {
    console.error('Failed to log event:', err)
  }
}

/**
 * End Call
 */
const endCall = async () => {
  try {
    const sessionId = route.params.sessionId
    
    // End session
    const response = await fetch(`/api/v1/video-sessions/${sessionId}/end`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        reason: 'user_ended',
      }),
    })

    if (response.ok) {
      success.value = 'Video call berakhir'
    }

    // Dispose Jitsi
    if (jitsiAPI) {
      jitsiAPI.dispose()
    }

    stopCallTimer()

    // Redirect
    setTimeout(() => {
      router.push('/consultations')
    }, 1000)
  } catch (err) {
    console.error('Failed to end call:', err)
    error.value = `Gagal mengakhiri call: ${err.message}`
  }
}

/**
 * Toggle Chat (Jitsi handles this)
 */
const toggleChat = () => {
  if (jitsiAPI) {
    jitsiAPI.toggleChat()
  }
}

/**
 * Toggle Participants (Jitsi handles this)
 */
const toggleParticipants = () => {
  if (jitsiAPI) {
    jitsiAPI.toggleFilmStrip()
  }
}

// Lifecycle
onMounted(async () => {
  await initializeJitsi()
})

onUnmounted(() => {
  if (jitsiAPI) {
    jitsiAPI.dispose()
  }
  stopCallTimer()
})
</script>

<style scoped>
.video-call-container {
  position: relative;
  width: 100%;
  height: 100vh;
  background: #000;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.jitsi-container {
  flex: 1;
  width: 100%;
  height: 100%;
}

.fallback-ui {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.9);
  z-index: 100;
}

.loader {
  text-align: center;
  color: white;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.connection-status {
  position: absolute;
  top: 16px;
  right: 16px;
  padding: 8px 12px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  z-index: 50;
  transition: all 0.3s;
}

.connection-status.connecting {
  background: rgba(255, 193, 7, 0.8);
  color: white;
}

.connection-status.connected {
  background: rgba(76, 175, 80, 0.8);
  color: white;
}

.connection-status.disconnected {
  background: rgba(244, 67, 54, 0.8);
  color: white;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.custom-toolbar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.8);
  padding: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 40;
}

.toolbar-info {
  display: flex;
  gap: 20px;
  align-items: center;
  color: white;
  font-weight: 600;
}

.timer {
  font-size: 14px;
}

.participant-count {
  font-size: 12px;
  color: #aaa;
}

.toolbar-controls {
  display: flex;
  gap: 8px;
}

.toolbar-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.toolbar-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.toolbar-btn.end-btn {
  background: rgba(244, 67, 54, 0.8);
}

.toolbar-btn.end-btn:hover {
  background: rgba(244, 67, 54, 1);
}

.error-alert {
  position: absolute;
  bottom: 80px;
  left: 16px;
  background: rgba(244, 67, 54, 0.95);
  color: white;
  padding: 12px 16px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 30;
  max-width: 300px;
}

.success-alert {
  position: absolute;
  top: 16px;
  left: 16px;
  background: rgba(76, 175, 80, 0.95);
  color: white;
  padding: 12px 16px;
  border-radius: 6px;
  z-index: 30;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.close-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 18px;
  font-weight: bold;
  padding: 0;
  margin-left: auto;
}
</style>
