<template>
  <div class="notification-listener">
    <!-- Silent component - only handles background WebSocket listening -->
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, inject } from 'vue'
import { useWebSocket } from '@/composables/useWebSocket'
import { notificationService } from '@/services/notificationService'

const props = defineProps({
  userId: {
    type: Number,
    required: true,
  },
  autoRefresh: {
    type: Boolean,
    default: true,
  },
  refreshInterval: {
    type: Number,
    default: 30000, // 30 seconds
  },
})

const emit = defineEmits([
  'notification-received',
  'notification-read',
  'notification-deleted',
  'connection-status-changed',
])

// Get global notification store if available
const notificationStore = inject('notificationStore', null)
const { onNotification, isConnected, on } = useWebSocket()

let unsubscribeNotification = null
let unsubscribeConnection = null
let unsubscribeNewMessage = null
let refreshTimer = null

/**
 * Handle new notification from WebSocket
 */
function handleNotificationReceived(notification) {
  console.log('📬 Notification received via WebSocket:', notification)
  
  // Update global store if available
  if (notificationStore && notificationStore.addNotification) {
    notificationStore.addNotification(notification)
  }

  // Emit event for parent component
  emit('notification-received', notification)

  // Show browser notification if permitted
  if ('Notification' in window && Notification.permission === 'granted') {
    showBrowserNotification(notification)
  }
}

/**
 * Handle message read receipt
 */
function handleMessageRead(data) {
  console.log('✓ Message marked as read:', data)
  emit('notification-read', data)
}

/**
 * Handle notification deleted
 */
function handleNotificationDeleted(data) {
  console.log('🗑️ Notification deleted:', data)
  emit('notification-deleted', data)
}

/**
 * Handle connection status changes
 */
function handleConnectionStatusChange(connected) {
  console.log(`🔌 WebSocket connection: ${connected ? '✅ Connected' : '❌ Disconnected'}`)
  emit('connection-status-changed', { connected })

  // Refresh notifications when reconnected
  if (connected && props.autoRefresh) {
    syncNotificationsWithServer()
  }
}

/**
 * Show browser notification
 */
function showBrowserNotification(notification) {
  const options = {
    icon: '/images/notification-icon.png',
    badge: '/images/notification-badge.png',
    tag: `notification-${notification.id}`,
    requireInteraction: notification.type === 'appointment' || notification.type === 'emergency',
    actions: [
      {
        action: 'open',
        title: 'Buka',
      },
      {
        action: 'close',
        title: 'Tutup',
      },
    ],
  }

  const browserNotification = new Notification(notification.title, {
    ...options,
    body: notification.message,
  })

  browserNotification.onclick = () => {
    window.focus()
    if (notification.action_url) {
      window.location.href = notification.action_url
    }
  }
}

/**
 * Request browser notification permission
 */
async function requestNotificationPermission() {
  if ('Notification' in window && Notification.permission === 'default') {
    try {
      const permission = await Notification.requestPermission()
      console.log(`Browser notifications permission: ${permission}`)
    } catch (error) {
      console.warn('Failed to request notification permission:', error)
    }
  }
}

/**
 * Sync notifications with server (e.g., on reconnect)
 */
async function syncNotificationsWithServer() {
  try {
    console.log('🔄 Syncing notifications with server...')
    const response = await notificationService.getNotifications(1, 10)
    
    if (response && response.data) {
      response.data.forEach(notification => {
        if (notificationStore && notificationStore.hasNotification) {
          // Only add if not already present
          if (!notificationStore.hasNotification(notification.id)) {
            notificationStore.addNotification(notification)
          }
        }
      })
    }
    console.log('✅ Notifications synced')
  } catch (error) {
    console.error('Failed to sync notifications:', error)
  }
}

/**
 * Auto-refresh notifications periodically
 */
function startAutoRefresh() {
  if (!props.autoRefresh) return

  refreshTimer = setInterval(async () => {
    try {
      await syncNotificationsWithServer()
    } catch (error) {
      console.error('Auto-refresh failed:', error)
    }
  }, props.refreshInterval)
}

/**
 * Stop auto-refresh
 */
function stopAutoRefresh() {
  if (refreshTimer) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
}

onMounted(async () => {
  console.log('🎧 Notification listener mounted for user:', props.userId)

  // Request browser notification permission
  await requestNotificationPermission()

  // Subscribe to real-time notifications
  unsubscribeNotification = onNotification(handleNotificationReceived)

  // Subscribe to notification-related events
  unsubscribeNewMessage = on('notification-created', handleNotificationReceived)

  // Watch connection status
  const checkConnection = () => {
    handleConnectionStatusChange(isConnected.value)
  }
  checkConnection()

  // Start auto-refresh
  startAutoRefresh()

  // Initial sync
  if (props.autoRefresh) {
    await syncNotificationsWithServer()
  }
})

onUnmounted(() => {
  console.log('🎧 Notification listener unmounted')

  // Cleanup subscriptions
  if (unsubscribeNotification) {
    unsubscribeNotification()
  }
  if (unsubscribeNewMessage) {
    unsubscribeNewMessage()
  }

  // Stop auto-refresh
  stopAutoRefresh()
})
</script>

<style scoped>
.notification-listener {
  display: none;
}
</style>
