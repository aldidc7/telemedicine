<template>
  <Teleport to="body">
    <transition-group 
      name="notification" 
      tag="div" 
      class="notifications-container"
    >
      <div 
        v-for="notification in notifications" 
        :key="notification.id"
        :class="['notification', `notification-${notification.type}`]"
      >
        <!-- Icon -->
        <div class="notification-icon">
          <component v-if="notification.icon" :is="notification.icon" />
        </div>

        <!-- Content -->
        <div class="notification-content">
          <div class="notification-title">{{ notification.title }}</div>
          <div v-if="notification.message" class="notification-message">
            {{ notification.message }}
          </div>
        </div>

        <!-- Close button -->
        <button 
          class="notification-close"
          @click="removeNotification(notification.id)"
          aria-label="Close notification"
        >
          ✕
        </button>

        <!-- Auto-dismiss progress -->
        <div v-if="notification.autoDismiss" class="notification-progress" />
      </div>
    </transition-group>
  </Teleport>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useWebSocket } from '@/composables/useWebSocket'

const notifications = ref([])
let notificationId = 0

/**
 * Add notification to display
 */
const addNotification = (notification) => {
  const id = ++notificationId
  const config = {
    id,
    type: notification.type || 'info',
    title: notification.title || 'Notification',
    message: notification.message || '',
    autoDismiss: notification.autoDismiss !== false,
    duration: notification.duration || 4000,
    icon: notification.icon || null,
  }

  notifications.value.push(config)

  // Auto-dismiss
  if (config.autoDismiss) {
    setTimeout(() => {
      removeNotification(id)
    }, config.duration)
  }

  return id
}

/**
 * Remove notification
 */
const removeNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
}

/**
 * Setup WebSocket event listeners
 */
const setupWebSocketListeners = () => {
  const { 
    onAppointmentUpdate, 
    onMessageReceived, 
    onPrescriptionUpdate, 
    onRatingReceived, 
    onNotification 
  } = useWebSocket()

  // Appointment notifications
  onAppointmentUpdate((data) => {
    switch (data.type) {
      case 'confirmed':
        addNotification({
          type: 'success',
          title: 'Appointment Confirmed',
          message: `Your appointment with ${data.doctor_name} is confirmed for ${data.appointment_date}`,
        })
        break
      case 'cancelled':
        addNotification({
          type: 'warning',
          title: 'Appointment Cancelled',
          message: `Your appointment with ${data.doctor_name} has been cancelled`,
        })
        break
      case 'completed':
        addNotification({
          type: 'success',
          title: 'Appointment Completed',
          message: `Your appointment with ${data.doctor_name} has been completed`,
        })
        break
      case 'reminder':
        addNotification({
          type: 'info',
          title: 'Appointment Reminder',
          message: `Your appointment with ${data.doctor_name} starts in 30 minutes`,
          autoDismiss: true,
          duration: 5000,
        })
        break
    }
  })

  // Message notifications
  onMessageReceived((data) => {
    if (data.type === 'received') {
      addNotification({
        type: 'info',
        title: 'New Message',
        message: `${data.sender_name}: ${data.preview}`,
        autoDismiss: true,
        duration: 5000,
      })
    }
  })

  // Prescription notifications
  onPrescriptionUpdate((data) => {
    if (data.type === 'ready') {
      addNotification({
        type: 'success',
        title: 'Prescription Ready',
        message: `Your prescription from ${data.doctor_name} is ready for pickup`,
        autoDismiss: true,
        duration: 6000,
      })
    }
  })

  // Rating notifications
  onRatingReceived((data) => {
    addNotification({
      type: 'info',
      title: 'New Rating',
      message: `${data.patient_name} rated you ${data.rating} stars`,
      autoDismiss: true,
      duration: 5000,
    })
  })

  // General notifications
  onNotification((data) => {
    if (data.type === 'general') {
      addNotification({
        type: data.severity || 'info',
        title: data.title || 'Notification',
        message: data.message,
        autoDismiss: true,
        duration: 5000,
      })
    }
  })
}

onMounted(() => {
  setupWebSocketListeners()
})

// Expose for programmatic use
defineExpose({
  addNotification,
  removeNotification,
})
</script>

<style scoped>
.notifications-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
}

.notification {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 8px;
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  pointer-events: auto;
  animation: slideIn 0.3s ease-out;
  position: relative;
  overflow: hidden;
}

/* Types */
.notification-success {
  border-left: 4px solid #22c55e;
  background: linear-gradient(to right, rgba(34, 197, 94, 0.05), white);
}

.notification-error {
  border-left: 4px solid #ef4444;
  background: linear-gradient(to right, rgba(239, 68, 68, 0.05), white);
}

.notification-warning {
  border-left: 4px solid #f59e0b;
  background: linear-gradient(to right, rgba(245, 158, 11, 0.05), white);
}

.notification-info {
  border-left: 4px solid #3b82f6;
  background: linear-gradient(to right, rgba(59, 130, 246, 0.05), white);
}

/* Icon */
.notification-icon {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: currentColor;
}

.notification-success .notification-icon {
  color: #22c55e;
}

.notification-error .notification-icon {
  color: #ef4444;
}

.notification-warning .notification-icon {
  color: #f59e0b;
}

.notification-info .notification-icon {
  color: #3b82f6;
}

/* Content */
.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 2px;
  font-size: 14px;
}

.notification-message {
  color: #6b7280;
  font-size: 13px;
  line-height: 1.4;
  word-break: break-word;
}

/* Close button */
.notification-close {
  flex-shrink: 0;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 16px;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.notification-close:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #1f2937;
}

/* Progress bar */
.notification-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 2px;
  background: currentColor;
  opacity: 0.3;
  animation: progress 4s linear forwards;
}

/* Animations */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(400px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideOut {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(400px);
  }
}

@keyframes progress {
  from {
    width: 100%;
  }
  to {
    width: 0;
  }
}

.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(400px);
}
</style>
