<template>
  <div class="video-call-container">
    <!-- Video Call Area -->
    <div class="video-grid" :class="{ 'screen-sharing': isScreenSharing }">
      <!-- Remote Video (Doctor/Practitioner) -->
      <div class="video-stream remote-stream">
        <video
          ref="remoteVideoRef"
          class="video-element"
          autoplay
          playsinline
          :muted="false"
        />
        <div class="participant-info doctor">
          <div class="avatar">{{ remoteParticipant?.name?.charAt(0) || 'D' }}</div>
          <div class="info">
            <p class="name">{{ remoteParticipant?.name || 'Doctor' }}</p>
            <p class="status">{{ remoteVideoActive ? 'Video On' : 'Video Off' }}</p>
          </div>
        </div>
        <div v-if="!remoteVideoActive" class="no-video">
          <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12 0H4v8h12V6z"></path>
          </svg>
        </div>
      </div>

      <!-- Local Video (Self) -->
      <div class="video-stream local-stream">
        <video
          ref="localVideoRef"
          class="video-element"
          autoplay
          muted
          playsinline
        />
        <div class="participant-info self">
          <div class="avatar">{{ localParticipant?.name?.charAt(0) || 'Y' }}</div>
          <p class="name">{{ localParticipant?.name || 'You' }}</p>
        </div>
        <div v-if="!localVideoActive" class="no-video">
          <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12 0H4v8h12V6z"></path>
          </svg>
        </div>
      </div>

      <!-- Screen Share View -->
      <div v-if="isScreenSharing" class="screen-share-container">
        <video
          ref="screenShareRef"
          class="screen-video"
          autoplay
          playsinline
        />
        <div class="screen-info">
          <span class="badge">Sharing Screen</span>
        </div>
      </div>
    </div>

    <!-- Control Panel -->
    <div class="control-panel">
      <!-- Timer -->
      <div class="call-timer">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
        </svg>
        <span class="time">{{ callDuration }}</span>
      </div>

      <!-- Control Buttons -->
      <div class="control-buttons">
        <!-- Microphone Toggle -->
        <button
          @click="toggleMicrophone"
          :class="['control-btn', { disabled: !audioActive }]"
          title="Microphone"
        >
          <svg v-if="audioActive" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 16.5a1 1 0 001 1h2a1 1 0 001-1v-1a1 1 0 10-2 0v1zm6-1.5a.5.5 0 10-1 0 .5.5 0 001 0zm0-3a.5.5 0 10-1 0 .5.5 0 001 0zm2.5 0a.5.5 0 10-1 0 .5.5 0 001 0z"></path>
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 4.332a6 6 0 110 8.536 6 6 0 010-8.536z" clip-rule="evenodd"></path>
          </svg>
          <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 9c0-4.447-2.239-8.334-5.63-10.577a1 1 0 121.42 1.023A8.014 8.014 0 0118 9a8.034 8.034 0 01-.592 3.117l1.338-1.338a1 1 0 111.414 1.414l-2-2M9 4.25V3a1 1 0 012 0v1.25a1 1 0 11-2 0z" clip-rule="evenodd"></path>
          </svg>
        </button>

        <!-- Camera Toggle -->
        <button
          @click="toggleCamera"
          :class="['control-btn', { disabled: !videoActive }]"
          title="Camera"
        >
          <svg v-if="videoActive" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12 0H4v8h12V6z"></path>
          </svg>
          <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 9c0-4.447-2.239-8.334-5.63-10.577a1 1 0 121.42 1.023A8.014 8.014 0 0118 9a8.034 8.034 0 01-.592 3.117l1.338-1.338a1 1 0 111.414 1.414l-2-2M9 4.25V3a1 1 0 012 0v1.25a1 1 0 11-2 0z" clip-rule="evenodd"></path>
          </svg>
        </button>

        <!-- Screen Share Toggle -->
        <button
          @click="toggleScreenShare"
          :class="['control-btn', { active: isScreenSharing }]"
          title="Share Screen"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path>
          </svg>
        </button>

        <!-- Settings -->
        <button
          @click="showSettings = !showSettings"
          class="control-btn"
          title="Settings"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>

      <!-- End Call Button -->
      <button
        @click="endCall"
        class="end-call-btn"
        title="End Call"
      >
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
        </svg>
      </button>
    </div>

    <!-- Chat Sidebar -->
    <div v-if="showChat" class="chat-sidebar">
      <div class="chat-header">
        <h3>Chat</h3>
        <button @click="showChat = false" class="close-btn">✕</button>
      </div>
      <div class="chat-messages">
        <div v-for="msg in messages" :key="msg.id" :class="['message', msg.senderType]">
          <p class="sender">{{ msg.senderName }}</p>
          <p class="text">{{ msg.text }}</p>
          <p class="time">{{ formatTime(msg.timestamp) }}</p>
        </div>
      </div>
      <div class="chat-input">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Send a message..."
          @keyup.enter="sendMessage"
          class="input"
        />
        <button @click="sendMessage" class="send-btn">Send</button>
      </div>
    </div>

    <!-- Settings Modal -->
    <div v-if="showSettings" class="settings-modal">
      <div class="modal-content">
        <h3 class="mb-4">Video Call Settings</h3>
        
        <div class="setting-group">
          <label>Camera</label>
          <select v-model="selectedCamera" @change="changeCamera" class="input">
            <option v-for="cam in availableCameras" :key="cam.deviceId" :value="cam.deviceId">
              {{ cam.label }}
            </option>
          </select>
        </div>

        <div class="setting-group">
          <label>Microphone</label>
          <select v-model="selectedMicrophone" @change="changeMicrophone" class="input">
            <option v-for="mic in availableMicrophones" :key="mic.deviceId" :value="mic.deviceId">
              {{ mic.label }}
            </option>
          </select>
        </div>

        <div class="setting-group">
          <label>Speaker</label>
          <select v-model="selectedSpeaker" @change="changeSpeaker" class="input">
            <option v-for="spk in availableSpeakers" :key="spk.deviceId" :value="spk.deviceId">
              {{ spk.label }}
            </option>
          </select>
        </div>

        <div class="button-group">
          <button @click="showSettings = false" class="cancel-btn">Close</button>
        </div>
      </div>
    </div>

    <!-- Quality Indicator -->
    <div class="quality-indicator">
      <span class="label">Connection:</span>
      <span :class="['quality', callQuality]">{{ callQuality }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Refs
const localVideoRef = ref(null)
const remoteVideoRef = ref(null)
const screenShareRef = ref(null)

// State
const audioActive = ref(true)
const videoActive = ref(true)
const remoteVideoActive = ref(true)
const isScreenSharing = ref(false)
const showChat = ref(false)
const showSettings = ref(false)
const callQuality = ref('good')
const callDuration = ref('00:00')

const localParticipant = ref({
  name: 'You',
  id: null,
})

const remoteParticipant = ref({
  name: 'Doctor',
  id: null,
})

const messages = ref([])
const newMessage = ref('')

const availableCameras = ref([])
const availableMicrophones = ref([])
const availableSpeakers = ref([])

const selectedCamera = ref('')
const selectedMicrophone = ref('')
const selectedSpeaker = ref('')

// WebRTC state
let localStream = null
let remoteStream = null
let peerConnection = null
let callTimer = null

// Timer
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

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// Media controls
const toggleMicrophone = async () => {
  if (localStream) {
    localStream.getAudioTracks().forEach(track => {
      track.enabled = !track.enabled
      audioActive.value = track.enabled
    })
  }
}

const toggleCamera = async () => {
  if (localStream) {
    localStream.getVideoTracks().forEach(track => {
      track.enabled = !track.enabled
      videoActive.value = track.enabled
    })
  }
}

const toggleScreenShare = async () => {
  if (!isScreenSharing.value) {
    try {
      const screenStream = await navigator.mediaDevices.getDisplayMedia({
        video: { cursor: 'always' },
        audio: false,
      })
      
      screenShareRef.value.srcObject = screenStream
      isScreenSharing.value = true
      
      // Stop screen share when user stops
      screenStream.getTracks()[0].onended = () => {
        isScreenSharing.value = false
        screenShareRef.value.srcObject = null
      }
    } catch (error) {
      console.error('Screen share error:', error)
    }
  } else {
    if (screenShareRef.value?.srcObject) {
      screenShareRef.value.srcObject.getTracks().forEach(track => track.stop())
    }
    screenShareRef.value.srcObject = null
    isScreenSharing.value = false
  }
}

const changeCamera = async () => {
  if (localStream) {
    localStream.getVideoTracks().forEach(track => track.stop())
    try {
      const newStream = await navigator.mediaDevices.getUserMedia({
        video: { deviceId: { exact: selectedCamera.value } },
        audio: true,
      })
      localStream = newStream
      localVideoRef.value.srcObject = newStream
    } catch (error) {
      console.error('Camera change error:', error)
    }
  }
}

const changeMicrophone = async () => {
  // Implement microphone switch
}

const changeSpeaker = async () => {
  // Implement speaker switch
}

const sendMessage = () => {
  if (newMessage.value.trim()) {
    messages.value.push({
      id: Date.now(),
      senderName: 'You',
      senderType: 'self',
      text: newMessage.value,
      timestamp: new Date(),
    })
    newMessage.value = ''
  }
}

const endCall = () => {
  if (localStream) {
    localStream.getTracks().forEach(track => track.stop())
  }
  if (peerConnection) {
    peerConnection.close()
  }
  if (callTimer) {
    clearInterval(callTimer)
  }
  router.push('/consultations')
}

const initializeMedia = async () => {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({
      video: true,
      audio: true,
    })
    
    localVideoRef.value.srcObject = localStream
    
    // Get available devices
    const devices = await navigator.mediaDevices.enumerateDevices()
    availableCameras.value = devices.filter(d => d.kind === 'videoinput')
    availableMicrophones.value = devices.filter(d => d.kind === 'audioinput')
    availableSpeakers.value = devices.filter(d => d.kind === 'audiooutput')
    
    if (availableCameras.value.length > 0) {
      selectedCamera.value = availableCameras.value[0].deviceId
    }
    if (availableMicrophones.value.length > 0) {
      selectedMicrophone.value = availableMicrophones.value[0].deviceId
    }
  } catch (error) {
    console.error('Media initialization error:', error)
    alert('Unable to access camera/microphone. Please check permissions.')
  }
}

onMounted(async () => {
  await initializeMedia()
  startCallTimer()
})

onUnmounted(() => {
  if (localStream) {
    localStream.getTracks().forEach(track => track.stop())
  }
  if (callTimer) {
    clearInterval(callTimer)
  }
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
}

.video-grid {
  flex: 1;
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: 1fr;
  gap: 8px;
  padding: 16px;
  position: relative;
}

.video-grid.screen-sharing {
  grid-template-columns: 1fr;
  grid-template-rows: 1fr auto;
}

.video-stream {
  position: relative;
  background: #1a1a1a;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-element {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-video {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  color: #999;
}

.participant-info {
  position: absolute;
  bottom: 12px;
  left: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(0, 0, 0, 0.6);
  padding: 8px 12px;
  border-radius: 6px;
  color: white;
  z-index: 10;
}

.participant-info.doctor {
  left: 12px;
  bottom: 12px;
}

.participant-info.self {
  left: auto;
  right: 12px;
  bottom: 12px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
}

.info {
  display: flex;
  flex-direction: column;
  font-size: 12px;
}

.info .name {
  font-weight: 600;
  margin: 0;
}

.info .status {
  color: #aaa;
  margin: 0;
}

.control-panel {
  background: rgba(0, 0, 0, 0.8);
  padding: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
  backdrop-filter: blur(10px);
}

.call-timer {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fff;
  font-weight: 600;
  font-size: 18px;
}

.control-buttons {
  display: flex;
  gap: 12px;
  align-items: center;
}

.control-btn {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #333;
  border: 2px solid #555;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.control-btn:hover:not(.disabled) {
  background: #444;
  border-color: #666;
}

.control-btn.active {
  background: #4CAF50;
  border-color: #45a049;
}

.control-btn.disabled {
  background: #d32f2f;
  border-color: #b71c1c;
}

.end-call-btn {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #d32f2f;
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  margin-left: auto;
}

.end-call-btn:hover {
  background: #b71c1c;
  transform: scale(1.05);
}

.quality-indicator {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(0, 0, 0, 0.6);
  padding: 8px 12px;
  border-radius: 6px;
  color: white;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
  z-index: 20;
}

.quality {
  padding: 2px 6px;
  border-radius: 3px;
  font-weight: 600;
}

.quality.excellent {
  background: #4CAF50;
  color: white;
}

.quality.good {
  background: #2196F3;
  color: white;
}

.quality.fair {
  background: #FF9800;
  color: white;
}

.quality.poor {
  background: #f44336;
  color: white;
}

.chat-sidebar {
  position: absolute;
  right: 16px;
  bottom: 80px;
  width: 300px;
  height: 400px;
  background: rgba(0, 0, 0, 0.9);
  border: 1px solid #444;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  z-index: 15;
}

.chat-header {
  padding: 12px;
  border-bottom: 1px solid #444;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.close-btn {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
  font-size: 18px;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
}

.message {
  margin-bottom: 12px;
  padding: 8px;
  border-radius: 6px;
  font-size: 12px;
  color: white;
}

.message.self {
  background: #667eea;
  margin-left: 20px;
  text-align: right;
}

.message.remote {
  background: #444;
  margin-right: 20px;
}

.sender {
  font-weight: 600;
  margin: 0 0 4px 0;
}

.text {
  margin: 0 0 4px 0;
}

.time {
  font-size: 10px;
  color: #aaa;
  margin: 0;
}

.chat-input {
  border-top: 1px solid #444;
  padding: 8px;
  display: flex;
  gap: 4px;
}

.input {
  flex: 1;
  background: #333;
  border: 1px solid #555;
  color: white;
  padding: 6px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.send-btn {
  background: #667eea;
  border: none;
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
}

.settings-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-content {
  background: #1a1a1a;
  padding: 20px;
  border-radius: 8px;
  color: white;
  max-width: 400px;
}

.setting-group {
  margin-bottom: 16px;
}

.setting-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  font-size: 14px;
}

.button-group {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.cancel-btn {
  flex: 1;
  background: #666;
  border: none;
  color: white;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.screen-share-container {
  position: relative;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  grid-column: 1;
  grid-row: 1;
}

.screen-video {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.screen-info {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 10;
}

.badge {
  background: rgba(76, 175, 80, 0.8);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}
</style>
