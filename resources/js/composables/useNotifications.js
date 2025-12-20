import { ref, computed, onMounted, onUnmounted } from 'vue'
import { notificationService } from '@/services/notificationService'
import { useWebSocket } from '@/composables/useWebSocket'

/**
 * Composable for managing notifications in Vue components
 * 
 * Usage:
 * const { notifications, unreadCount, markAsRead, deleteNotification } = useNotifications()
 */
export function useNotifications() {
  const notifications = ref([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const error = ref(null)
  
  const { onNotification, onAppointmentUpdate, onPresenceChange } = useWebSocket()
  
  let unsubscribeNotification = null
  let unsubscribeAppointment = null
  let unsubscribePresence = null

  /**
   * Fetch notifications from server
   */
  const fetchNotifications = async (page = 1, perPage = 10) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await notificationService.getNotifications(page, perPage)
      notifications.value = response.data || []
      
      // Update unread count
      await fetchUnreadCount()
      
      return response
    } catch (err) {
      error.value = err.message
      console.error('Failed to fetch notifications:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch unread count
   */
  const fetchUnreadCount = async () => {
    try {
      const count = await notificationService.getUnreadCount()
      unreadCount.value = count
      return count
    } catch (err) {
      console.error('Failed to fetch unread count:', err)
      return 0
    }
  }

  /**
   * Mark single notification as read
   */
  const markAsRead = async (notificationId) => {
    try {
      await notificationService.markAsRead(notificationId)
      
      // Update local state
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.is_read = true
      }
      
      await fetchUnreadCount()
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
    }
  }

  /**
   * Mark all notifications as read
   */
  const markAllAsRead = async () => {
    try {
      await notificationService.markAllAsRead()
      
      // Update local state
      notifications.value.forEach(n => {
        n.is_read = true
      })
      
      unreadCount.value = 0
    } catch (err) {
      console.error('Failed to mark all as read:', err)
    }
  }

  /**
   * Delete single notification
   */
  const deleteNotification = async (notificationId) => {
    try {
      await notificationService.deleteNotification(notificationId)
      
      // Remove from local state
      notifications.value = notifications.value.filter(n => n.id !== notificationId)
      
      await fetchUnreadCount()
    } catch (err) {
      console.error('Failed to delete notification:', err)
    }
  }

  /**
   * Clear all notifications
   */
  const clearAll = async () => {
    try {
      await notificationService.clearAll()
      
      // Clear local state
      notifications.value = []
      unreadCount.value = 0
    } catch (err) {
      console.error('Failed to clear all notifications:', err)
    }
  }

  /**
   * Get notification by ID
   */
  const getNotification = (id) => {
    return notifications.value.find(n => n.id === id)
  }

  /**
   * Filter notifications by type
   */
  const filterByType = (type) => {
    return notifications.value.filter(n => n.type === type)
  }

  /**
   * Filter unread notifications
   */
  const getUnreadNotifications = () => {
    return notifications.value.filter(n => !n.is_read)
  }

  /**
   * Handle new notification from WebSocket
   */
  const handleNewNotification = async (notification) => {
    console.log('📬 New notification received:', notification)
    
    // Add to beginning of list
    notifications.value.unshift(notification)
    
    // Update unread count
    await fetchUnreadCount()
  }

  /**
   * Handle appointment update from WebSocket
   */
  const handleAppointmentUpdate = async (data) => {
    console.log('📅 Appointment update received:', data)
    
    // If there's an appointment notification, update it
    const appointmentNotif = notifications.value.find(
      n => n.type === 'appointment_update' && n.notifiable_id === data.id
    )
    
    if (appointmentNotif) {
      appointmentNotif.data = { ...appointmentNotif.data, ...data }
    }
  }

  /**
   * Initialize notifications
   */
  const init = async () => {
    await fetchNotifications()
    
    // Subscribe to WebSocket events
    unsubscribeNotification = onNotification(handleNewNotification)
    unsubscribeAppointment = onAppointmentUpdate(handleAppointmentUpdate)
  }

  /**
   * Cleanup
   */
  const cleanup = () => {
    if (unsubscribeNotification) unsubscribeNotification()
    if (unsubscribeAppointment) unsubscribeAppointment()
    if (unsubscribePresence) unsubscribePresence()
  }

  // Computed properties
  const hasUnread = computed(() => unreadCount.value > 0)
  const readNotifications = computed(() => notifications.value.filter(n => n.is_read))
  const unreadNotifications = computed(() => notifications.value.filter(n => !n.is_read))
  const notificationCount = computed(() => notifications.value.length)

  onMounted(() => {
    init()
  })

  onUnmounted(() => {
    cleanup()
  })

  return {
    // State
    notifications,
    unreadCount,
    isLoading,
    error,
    
    // Computed
    hasUnread,
    readNotifications,
    unreadNotifications,
    notificationCount,
    
    // Methods
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearAll,
    getNotification,
    filterByType,
    getUnreadNotifications,
    init,
    cleanup,
  }
}

export default useNotifications
