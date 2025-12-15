<template>
  <div 
    :class="[
      'connection-indicator',
      `connection-${connectionState}`,
      { 'connection-pulse': isConnecting }
    ]"
    :title="statusMessage"
  >
    <!-- Status dot -->
    <div class="status-dot" />

    <!-- Status label (on hover) -->
    <div class="status-label">
      <span v-if="isConnecting" class="text-xs">Menghubungkan...</span>
      <span v-else-if="isConnected" class="text-xs">Terhubung</span>
      <span v-else class="text-xs">Terputus</span>
    </div>

    <!-- Error message -->
    <div v-if="lastError" class="error-tooltip">
      {{ lastError }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useWebSocket } from '@/composables/useWebSocket'

const { isConnected, isConnecting, lastError } = useWebSocket()

const connectionState = computed(() => {
  if (isConnecting.value) return 'connecting'
  if (isConnected.value) return 'connected'
  return 'disconnected'
})

const statusMessage = computed(() => {
  if (isConnecting.value) return 'Menghubungkan ke WebSocket...'
  if (isConnected.value) return 'Real-time terhubung'
  if (lastError.value) return `Error: ${lastError.value}`
  return 'Real-time terputus'
})
</script>

<style scoped>
.connection-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  position: relative;
}

/* Connected state */
.connection-connected {
  background-color: rgba(34, 197, 94, 0.1);
  color: rgb(34, 197, 94);
}

/* Connecting state */
.connection-connecting {
  background-color: rgba(59, 130, 246, 0.1);
  color: rgb(59, 130, 246);
}

.connection-pulse .status-dot {
  animation: pulse 2s infinite;
}

/* Disconnected state */
.connection-disconnected {
  background-color: rgba(239, 68, 68, 0.1);
  color: rgb(239, 68, 68);
}

/* Status dot */
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: currentColor;
}

/* Status label */
.status-label {
  display: none;
}

.connection-indicator:hover .status-label {
  display: block;
}

/* Error tooltip */
.error-tooltip {
  display: none;
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  white-space: nowrap;
  margin-bottom: 4px;
}

.connection-disconnected:hover .error-tooltip {
  display: block;
}

/* Pulse animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
