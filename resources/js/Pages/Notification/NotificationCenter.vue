<template>
  <div class="notification-center">
    <!-- Header -->
    <div class="notification-header">
      <h2 class="text-2xl font-bold">Pusat Notifikasi</h2>
      <div class="header-actions">
        <button v-if="hasUnread" @click="markAllAsRead" class="btn-mark-all-read">
          Tandai Semua Dibaca
        </button>
        <button v-if="notifications.length > 0" @click="clearAll" class="btn-clear-all">
          Hapus Semua
        </button>
      </div>
    </div>

    <!-- Filter/Tabs -->
    <div class="notification-filters">
      <button
        v-for="filter in filters"
        :key="filter"
        @click="activeFilter = filter"
        :class="['filter-btn', { active: activeFilter === filter }]"
      >
        {{ filter === 'all' ? 'Semua' : filter === 'unread' ? 'Belum Dibaca' : 'Dibaca' }}
      </button>
    </div>

    <!-- Unread Count Badge -->
    <div v-if="unreadCount > 0" class="unread-badge">
      {{ unreadCount }} notifikasi belum dibaca
    </div>

    <!-- Notifications List -->
    <div class="notifications-list">
      <div v-if="filteredNotifications.length === 0" class="empty-state">
        <p>Tidak ada notifikasi</p>
      </div>

      <transition-group name="notification-list" tag="div">
        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          :class="['notification-item', { unread: !notification.read_at }]"
          @click="handleNotificationClick(notification)"
        >
          <!-- Icon/Avatar -->
          <div class="notification-icon" :class="getNotificationIcon(notification.type)">
            <i :class="getNotificationIconClass(notification.type)"></i>
          </div>

          <!-- Content -->
          <div class="notification-content">
            <div class="notification-header-row">
              <h3 class="notification-title">{{ notification.title }}</h3>
              <span class="notification-time">
                {{ formatTime(notification.created_at) }}
              </span>
            </div>
            <p class="notification-message">{{ notification.message }}</p>
            <div v-if="notification.data" class="notification-data">
              <span v-if="notification.data.doctor_name" class="tag">
                {{ notification.data.doctor_name }}
              </span>
              <span v-if="notification.data.amount" class="tag">
                Rp {{ formatCurrency(notification.data.amount) }}
              </span>
            </div>
          </div>

          <!-- Status Indicators -->
          <div class="notification-actions">
            <button
              v-if="!notification.read_at"
              @click.stop="markAsRead(notification)"
              class="btn-action btn-read"
              title="Tandai dibaca"
            >
              ○
            </button>
            <button
              @click.stop="deleteNotification(notification)"
              class="btn-action btn-delete"
              title="Hapus"
            >
              ✕
            </button>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Pagination -->
    <div v-if="totalNotifications > 0" class="notification-pagination">
      <button
        :disabled="currentPage === 1"
        @click="previousPage"
        class="btn-pagination"
      >
        Sebelumnya
      </button>
      <span class="page-info">
        Halaman {{ currentPage }} dari {{ totalPages }}
      </span>
      <button
        :disabled="currentPage === totalPages"
        @click="nextPage"
        class="btn-pagination"
      >
        Berikutnya
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import notificationService from '@/services/notificationService'
import { formatDistanceToNow, format } from 'date-fns'
import id from 'date-fns/locale/id'

const router = useRouter()

const notifications = ref([])
const unreadCount = ref(0)
const activeFilter = ref('all')
const currentPage = ref(1)
const perPage = ref(10)
const totalNotifications = ref(0)
const loading = ref(false)

const filters = ['all', 'unread', 'read']

const hasUnread = computed(() => unreadCount.value > 0)
const totalPages = computed(() => Math.ceil(totalNotifications.value / perPage.value))

const filteredNotifications = computed(() => {
  if (activeFilter.value === 'all') return notifications.value
  if (activeFilter.value === 'unread') return notifications.value.filter(n => !n.read_at)
  return notifications.value.filter(n => n.read_at)
})

const loadNotifications = async () => {
  try {
    loading.value = true
    const response = await notificationService.getNotifications(currentPage.value, perPage.value)
    notifications.value = response.notifications
    totalNotifications.value = response.pagination.total
  } catch (error) {
    console.error('Failed to load notifications:', error)
  } finally {
    loading.value = false
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

const markAsRead = async (notification) => {
  try {
    await notificationService.markAsRead(notification.id)
    notification.read_at = new Date()
    await loadUnreadCount()
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await notificationService.markAllAsRead()
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date()
    })
    unreadCount.value = 0
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const deleteNotification = async (notification) => {
  try {
    await notificationService.deleteNotification(notification.id)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
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
  if (!notification.read_at) {
    markAsRead(notification)
  }
  if (notification.action_url) {
    router.push(notification.action_url)
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadNotifications()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadNotifications()
  }
}

const formatTime = (date) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: id })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID').format(amount)
}

const getNotificationIcon = (type) => {
  const icons = {
    appointment: 'icon-calendar',
    appointment_reminder: 'icon-clock',
    appointment_cancelled: 'icon-cancel',
    message: 'icon-message',
    payment_success: 'icon-success',
    payment_failed: 'icon-error',
    consultation_started: 'icon-video',
    credential_approved: 'icon-check',
    credential_rejected: 'icon-close',
  }
  return icons[type] || 'icon-notification'
}

const getNotificationIconClass = (type) => {
  const icons = {
    appointment: 'fas fa-calendar-alt',
    appointment_reminder: 'fas fa-clock',
    appointment_cancelled: 'fas fa-times-circle',
    message: 'fas fa-envelope',
    payment_success: 'fas fa-check-circle',
    payment_failed: 'fas fa-exclamation-circle',
    consultation_started: 'fas fa-video',
    credential_approved: 'fas fa-certificate',
    credential_rejected: 'fas fa-ban',
  }
  return icons[type] || 'fas fa-bell'
}

onMounted(() => {
  loadNotifications()
  loadUnreadCount()
  // Refresh every 30 seconds
  setInterval(loadUnreadCount, 30000)
})
</script>

<style scoped>
.notification-center {
  background-color: rgb(249, 250, 251);
  border-radius: 0.5rem;
  padding: 2rem;
  max-width: 56rem;
  margin: 0 auto;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgb(229, 231, 235);
}

.notification-header h2 {
  color: rgb(17, 24, 39);
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.btn-mark-all-read,
.btn-clear-all {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  border: 1px solid rgb(209, 213, 219);
  background-color: white;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-mark-all-read:hover {
  background-color: rgb(37, 99, 235);
  color: white;
  border-color: rgb(37, 99, 235);
}

.btn-clear-all:hover {
  background-color: rgb(239, 68, 68);
  color: white;
  border-color: rgb(239, 68, 68);
}

.notification-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  background-color: white;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.filter-btn:hover {
  border-color: rgb(37, 99, 235);
  color: rgb(37, 99, 235);
}

.filter-btn.active {
  background-color: rgb(37, 99, 235);
  color: white;
  border-color: rgb(37, 99, 235);
}

.unread-badge {
  background-color: rgb(254, 242, 242);
  border: 1px solid rgb(254, 202, 202);
  color: rgb(220, 38, 38);
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.notifications-list {
  background-color: white;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid rgb(229, 231, 235);
  min-height: 200px;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: rgb(107, 114, 128);
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgb(229, 231, 235);
  cursor: pointer;
  transition: background-color 0.2s;
  align-items: flex-start;
}

.notification-item:hover {
  background-color: rgb(249, 250, 251);
}

.notification-item.unread {
  background-color: rgb(239, 246, 255);
}

.notification-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
  font-size: 1.25rem;
}

.notification-icon.icon-calendar {
  background-color: rgb(37, 99, 235);
}

.notification-icon.icon-clock {
  background-color: rgb(202, 138, 4);
}

.notification-icon.icon-cancel {
  background-color: rgb(239, 68, 68);
}

.notification-icon.icon-message {
  background-color: rgb(34, 197, 94);
}

.notification-icon.icon-success {
  background-color: rgb(34, 197, 94);
}

.notification-icon.icon-error {
  background-color: rgb(239, 68, 68);
}

.notification-icon.icon-video {
  background-color: rgb(59, 130, 246);
}

.notification-icon.icon-check {
  background-color: rgb(34, 197, 94);
}

.notification-icon.icon-close {
  background-color: rgb(239, 68, 68);
}

.notification-icon.icon-notification {
  background-color: rgb(107, 114, 128);
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.notification-title {
  font-weight: 600;
  color: rgb(17, 24, 39);
  margin: 0;
  font-size: 0.95rem;
}

.notification-time {
  font-size: 0.8rem;
  color: rgb(107, 114, 128);
  white-space: nowrap;
  margin-left: 1rem;
}

.notification-message {
  color: rgb(75, 85, 99);
  margin: 0.25rem 0 0.5rem 0;
  font-size: 0.9rem;
  line-height: 1.4;
}

.notification-data {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.tag {
  display: inline-block;
  background-color: rgb(219, 234, 254);
  color: rgb(30, 58, 138);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
}

.btn-action {
  width: 1.5rem;
  height: 1.5rem;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgb(107, 114, 128);
  transition: color 0.2s;
}

.btn-action:hover {
  color: rgb(17, 24, 39);
}

.btn-read:hover {
  color: rgb(37, 99, 235);
}

.btn-delete:hover {
  color: rgb(239, 68, 68);
}

.notification-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid rgb(229, 231, 235);
}

.btn-pagination {
  padding: 0.5rem 1rem;
  border: 1px solid rgb(209, 213, 219);
  background-color: white;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.btn-pagination:hover:not(:disabled) {
  background-color: rgb(37, 99, 235);
  color: white;
  border-color: rgb(37, 99, 235);
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: rgb(107, 114, 128);
  font-size: 0.875rem;
}

.notification-list-enter-active,
.notification-list-leave-active {
  transition: all 0.3s ease;
}

.notification-list-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.notification-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
