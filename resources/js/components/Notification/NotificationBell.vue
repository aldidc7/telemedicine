<template>
  <div class="notification-bell">
    <!-- Bell Icon -->
    <button
      @click="toggleDropdown"
      :class="['bell-button', { active: showDropdown }]"
      title="Notifikasi"
    >
      <i class="fas fa-bell"></i>
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
    </button>

    <!-- Dropdown Menu -->
    <transition name="dropdown">
      <div v-show="showDropdown" class="notification-dropdown">
        <!-- Header -->
        <div class="dropdown-header">
          <h3>Notifikasi</h3>
          <button @click="toggleDropdown" class="close-btn">✕</button>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
          <div class="stat">
            <span class="stat-label">Belum Dibaca</span>
            <span class="stat-value">{{ unreadCount }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Total</span>
            <span class="stat-value">{{ totalCount }}</span>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="notifications-container">
          <div v-if="notifications.length === 0" class="empty-message">
            Tidak ada notifikasi
          </div>

          <div v-for="notification in notifications.slice(0, 5)" :key="notification.id" class="notification-item-small">
            <div class="item-left">
              <div :class="['item-icon', getNotificationClass(notification.type)]">
                <i :class="getIconClass(notification.type)"></i>
              </div>
            </div>
            <div class="item-content" @click="handleNotificationClick(notification)">
              <p class="item-title">{{ notification.title }}</p>
              <p class="item-message">{{ truncate(notification.message, 50) }}</p>
              <span class="item-time">{{ formatTime(notification.created_at) }}</span>
            </div>
            <button
              @click.stop="deleteNotification(notification.id)"
              class="item-delete"
              title="Hapus"
            >
              ✕
            </button>
          </div>

          <!-- View All Link -->
          <div v-if="notifications.length > 0" class="dropdown-footer">
            <router-link to="/notifications" class="view-all-link">
              Lihat Semua Notifikasi →
            </router-link>
          </div>
        </div>

        <!-- Actions -->
        <div class="dropdown-actions">
          <button v-if="unreadCount > 0" @click="markAllAsRead" class="action-btn mark-read">
            Tandai Semua Dibaca
          </button>
          <button v-if="notifications.length > 0" @click="clearAll" class="action-btn clear">
            Hapus Semua
          </button>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <div v-show="showDropdown" class="dropdown-overlay" @click="showDropdown = false"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import notificationService from '@/services/notificationService'
import { formatDistanceToNow } from 'date-fns'
import id from 'date-fns/locale/id'

const router = useRouter()

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const totalCount = ref(0)
let refreshInterval = null

const loadNotifications = async () => {
  try {
    const response = await notificationService.getNotifications(1, 10)
    notifications.value = response.notifications
    totalCount.value = response.pagination.total
  } catch (error) {
    console.error('Failed to load notifications:', error)
  }
}

const loadUnreadCount = async () => {
  try {
    const response = await notificationService.getUnreadCount()
    unreadCount.value = response.unread_count
  } catch (error) {
    console.error('Failed to load unread count:', error)
  }
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    loadNotifications()
  }
}

const markAllAsRead = async () => {
  try {
    await notificationService.markAllAsRead()
    await loadNotifications()
    await loadUnreadCount()
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const deleteNotification = async (id) => {
  try {
    await notificationService.deleteNotification(id)
    await loadNotifications()
    await loadUnreadCount()
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
}

const clearAll = async () => {
  if (!confirm('Hapus semua notifikasi?')) return

  try {
    await notificationService.clearAll()
    notifications.value = []
    unreadCount.value = 0
  } catch (error) {
    console.error('Failed to clear notifications:', error)
  }
}

const handleNotificationClick = (notification) => {
  if (notification.action_url) {
    showDropdown.value = false
    router.push(notification.action_url)
  }
}

const formatTime = (date) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: id })
}

const truncate = (text, length) => {
  return text.length > length ? text.substring(0, length) + '...' : text
}

const getNotificationClass = (type) => {
  const classes = {
    appointment: 'icon-blue',
    appointment_reminder: 'icon-warning',
    appointment_cancelled: 'icon-error',
    message: 'icon-success',
    payment_success: 'icon-success',
    payment_failed: 'icon-error',
    consultation_started: 'icon-blue',
  }
  return classes[type] || 'icon-default'
}

const getIconClass = (type) => {
  const icons = {
    appointment: 'fas fa-calendar-alt',
    appointment_reminder: 'fas fa-clock',
    appointment_cancelled: 'fas fa-times-circle',
    message: 'fas fa-envelope',
    payment_success: 'fas fa-check-circle',
    payment_failed: 'fas fa-exclamation-circle',
    consultation_started: 'fas fa-video',
  }
  return icons[type] || 'fas fa-bell'
}

onMounted(() => {
  loadUnreadCount()
  loadNotifications()
  // Refresh unread count every 15 seconds
  refreshInterval = setInterval(loadUnreadCount, 15000)
})

onBeforeUnmount(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
.notification-bell {
  position: relative;
  display: inline-block;
}

.bell-button {
  position: relative;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  border: none;
  background-color: transparent;
  cursor: pointer;
  color: rgb(75, 85, 99);
  font-size: 1.25rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bell-button:hover {
  background-color: rgb(229, 231, 235);
  color: rgb(37, 99, 235);
}

.bell-button.active {
  background-color: rgb(37, 99, 235);
  color: white;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: rgb(239, 68, 68);
  color: white;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
}

.notification-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 400px;
  max-height: 600px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  z-index: 50;
  margin-top: 0.5rem;
}

.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid rgb(229, 231, 235);
}

.dropdown-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: rgb(17, 24, 39);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: rgb(107, 114, 128);
}

.close-btn:hover {
  color: rgb(17, 24, 39);
}

.quick-stats {
  display: flex;
  padding: 1rem;
  gap: 1rem;
  border-bottom: 1px solid rgb(229, 231, 235);
  background-color: rgb(249, 250, 251);
}

.stat {
  flex: 1;
  text-align: center;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  color: rgb(107, 114, 128);
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: rgb(37, 99, 235);
}

.notifications-container {
  flex: 1;
  overflow-y: auto;
  max-height: 350px;
}

.empty-message {
  text-align: center;
  padding: 2rem 1rem;
  color: rgb(107, 114, 128);
}

.notification-item-small {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-bottom: 1px solid rgb(229, 231, 235);
  cursor: pointer;
  transition: background-color 0.2s;
  align-items: flex-start;
}

.notification-item-small:hover {
  background-color: rgb(249, 250, 251);
}

.item-left {
  flex-shrink: 0;
}

.item-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
}

.item-icon.icon-blue {
  background-color: rgb(37, 99, 235);
}

.item-icon.icon-warning {
  background-color: rgb(202, 138, 4);
}

.item-icon.icon-error {
  background-color: rgb(239, 68, 68);
}

.item-icon.icon-success {
  background-color: rgb(34, 197, 94);
}

.item-icon.icon-default {
  background-color: rgb(107, 114, 128);
}

.item-content {
  flex: 1;
  min-width: 0;
}

.item-title {
  margin: 0;
  font-weight: 600;
  font-size: 0.9rem;
  color: rgb(17, 24, 39);
}

.item-message {
  margin: 0.25rem 0;
  font-size: 0.8rem;
  color: rgb(107, 114, 128);
  line-height: 1.3;
  word-break: break-word;
}

.item-time {
  display: block;
  font-size: 0.7rem;
  color: rgb(156, 163, 175);
  margin-top: 0.25rem;
}

.item-delete {
  flex-shrink: 0;
  background: none;
  border: none;
  color: rgb(107, 114, 128);
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.2s;
}

.item-delete:hover {
  color: rgb(239, 68, 68);
}

.dropdown-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid rgb(229, 231, 235);
  text-align: center;
}

.view-all-link {
  color: rgb(37, 99, 235);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: color 0.2s;
}

.view-all-link:hover {
  color: rgb(29, 78, 216);
}

.dropdown-actions {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid rgb(229, 231, 235);
  background-color: rgb(249, 250, 251);
}

.action-btn {
  flex: 1;
  padding: 0.5rem 0.75rem;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.375rem;
  background-color: white;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
  transition: all 0.2s;
}

.action-btn:hover {
  background-color: rgb(37, 99, 235);
  color: white;
  border-color: rgb(37, 99, 235);
}

.action-btn.clear:hover {
  background-color: rgb(239, 68, 68);
  border-color: rgb(239, 68, 68);
}

.dropdown-overlay {
  position: fixed;
  inset: 0;
  z-index: 49;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
