/**
 * Composable for Real-time Analytics Updates
 * Provides hooks for auto-refresh, WebSocket integration, and cache management
 */

import { ref, computed, onMounted, onUnmounted } from 'vue'
import analyticsWebSocket from '@/services/AnalyticsWebSocket'

export function useRealtimeAnalytics() {
  // Auto-refresh state
  const autoRefreshEnabled = ref(true)
  const autoRefreshInterval = ref(30) // seconds
  const lastUpdated = ref(new Date())
  const updateStatus = ref('idle') // idle, updating, error
  
  // WebSocket state
  const wsConnected = ref(false)
  const wsStatus = ref('disconnected')
  
  let autoRefreshTimer = null
  let unsubscribeHandlers = []

  // Computed properties
  const formattedLastUpdated = computed(() => {
    const now = new Date()
    const diff = Math.floor((now - lastUpdated.value) / 1000)
    
    if (diff < 60) return `${diff}s ago`
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
    return `${Math.floor(diff / 3600)}h ago`
  })

  const isLiveUpdating = computed(() => {
    return autoRefreshEnabled.value || wsConnected.value
  })

  /**
   * Initialize auto-refresh timer
   * @param {function} fetchCallback - Callback to execute on refresh
   */
  const initializeAutoRefresh = (fetchCallback) => {
    if (autoRefreshTimer) clearInterval(autoRefreshTimer)
    
    autoRefreshTimer = setInterval(() => {
      if (autoRefreshEnabled.value) {
        executeRefresh(fetchCallback)
      }
    }, autoRefreshInterval.value * 1000)
  }

  /**
   * Stop auto-refresh timer
   */
  const stopAutoRefresh = () => {
    if (autoRefreshTimer) {
      clearInterval(autoRefreshTimer)
      autoRefreshTimer = null
    }
  }

  /**
   * Toggle auto-refresh
   */
  const toggleAutoRefresh = (fetchCallback) => {
    autoRefreshEnabled.value = !autoRefreshEnabled.value
    if (autoRefreshEnabled.value) {
      initializeAutoRefresh(fetchCallback)
    } else {
      stopAutoRefresh()
    }
  }

  /**
   * Update refresh interval
   */
  const updateRefreshInterval = (newInterval, fetchCallback) => {
    autoRefreshInterval.value = newInterval
    initializeAutoRefresh(fetchCallback)
  }

  /**
   * Execute refresh with status tracking
   */
  const executeRefresh = async (fetchCallback) => {
    try {
      updateStatus.value = 'updating'
      await fetchCallback()
      lastUpdated.value = new Date()
      updateStatus.value = 'idle'
    } catch (error) {
      console.error('Refresh failed:', error)
      updateStatus.value = 'error'
      setTimeout(() => { updateStatus.value = 'idle' }, 3000)
    }
  }

  /**
   * Initialize WebSocket connection
   * @param {string} token - Authentication token
   */
  const initializeWebSocket = async (token) => {
    try {
      if (!analyticsWebSocket.isConnected()) {
        await analyticsWebSocket.connect(token)
        wsConnected.value = true
        wsStatus.value = 'connected'
        console.log('[Composable] WebSocket connected')
      }
    } catch (error) {
      console.error('[Composable] WebSocket connection failed:', error)
      wsStatus.value = 'failed'
    }
  }

  /**
   * Subscribe to WebSocket event
   */
  const subscribeToUpdate = (eventType, callback) => {
    const unsubscribe = analyticsWebSocket.subscribe(eventType, (payload) => {
      lastUpdated.value = new Date()
      callback(payload)
    })
    unsubscribeHandlers.push(unsubscribe)
    return unsubscribe
  }

  /**
   * Monitor WebSocket connection status
   */
  const monitorWebSocketStatus = () => {
    const statusInterval = setInterval(() => {
      wsStatus.value = analyticsWebSocket.getStatus()
      wsConnected.value = analyticsWebSocket.isConnected()
    }, 5000)

    return () => clearInterval(statusInterval)
  }

  /**
   * Cleanup all subscriptions and timers
   */
  const cleanup = () => {
    stopAutoRefresh()
    unsubscribeHandlers.forEach(unsubscribe => unsubscribe())
    unsubscribeHandlers = []
    if (wsConnected.value) {
      analyticsWebSocket.disconnect()
      wsConnected.value = false
    }
  }

  return {
    // State
    autoRefreshEnabled,
    autoRefreshInterval,
    lastUpdated,
    updateStatus,
    wsConnected,
    wsStatus,

    // Computed
    formattedLastUpdated,
    isLiveUpdating,

    // Methods
    initializeAutoRefresh,
    stopAutoRefresh,
    toggleAutoRefresh,
    updateRefreshInterval,
    executeRefresh,
    initializeWebSocket,
    subscribeToUpdate,
    monitorWebSocketStatus,
    cleanup,

    // WebSocket reference
    analyticsWebSocket,
  }
}
