<template>
  <div class="video-call-page">
    <VideoCall v-if="sessionReady" />
    <div v-else class="loading-screen">
      <div class="spinner"></div>
      <p>Initializing video call...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import VideoCall from '@/components/VideoCall/VideoCall.vue'

const route = useRoute()
const router = useRouter()

const sessionReady = ref(false)

onMounted(async () => {
  try {
    // Get video session details
    const response = await fetch(`/api/v1/video-sessions/${route.params.sessionId}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) throw new Error('Gagal memuat session')

    const data = await response.json()
    const session = data.data

    // Start session if pending
    if (session.status === 'pending') {
      await fetch(`/api/v1/video-sessions/${session.id}/start`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json',
        }
      })
    }

    sessionReady.value = true
  } catch (error) {
    console.error('Error:', error)
    alert('Error initializing video call: ' + error.message)
    router.push('/consultations')
  }
})
</script>

<style scoped>
.video-call-page {
  width: 100%;
  height: 100vh;
  background: #000;
  overflow: hidden;
}

.loading-screen {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
