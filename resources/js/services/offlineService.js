// Offline Mode & Caching Service
// Menyediakan local caching untuk offline access

import axios from 'axios'

const CACHE_PREFIX = 'telemedicine_cache_'
const CACHE_EXPIRY_KEY = 'telemedicine_cache_expiry_'
const SYNC_QUEUE_KEY = 'telemedicine_sync_queue'

const CACHE_DURATION = {
  SHORT: 5 * 60 * 1000, // 5 minutes
  MEDIUM: 30 * 60 * 1000, // 30 minutes
  LONG: 24 * 60 * 60 * 1000, // 24 hours
}

export const cacheService = {
  /**
   * Get cached data
   */
  get(key) {
    const cached = localStorage.getItem(CACHE_PREFIX + key)
    const expiry = localStorage.getItem(CACHE_EXPIRY_KEY + key)

    if (!cached || !expiry) return null

    if (Date.now() > parseInt(expiry)) {
      this.remove(key)
      return null
    }

    try {
      return JSON.parse(cached)
    } catch (e) {
      this.remove(key)
      return null
    }
  },

  /**
   * Set cache with expiry
   */
  set(key, data, duration = CACHE_DURATION.MEDIUM) {
    try {
      localStorage.setItem(CACHE_PREFIX + key, JSON.stringify(data))
      localStorage.setItem(CACHE_EXPIRY_KEY + key, (Date.now() + duration).toString())
    } catch (e) {
      console.warn('Cache storage full or disabled:', e)
    }
  },

  /**
   * Remove cache
   */
  remove(key) {
    localStorage.removeItem(CACHE_PREFIX + key)
    localStorage.removeItem(CACHE_EXPIRY_KEY + key)
  },

  /**
   * Clear all cache
   */
  clearAll() {
    const keys = Object.keys(localStorage)
    keys.forEach(key => {
      if (key.startsWith(CACHE_PREFIX)) {
        localStorage.removeItem(key)
        localStorage.removeItem(key.replace(CACHE_PREFIX, CACHE_EXPIRY_KEY))
      }
    })
  },

  /**
   * Get cache stats
   */
  getStats() {
    const keys = Object.keys(localStorage)
    const cacheKeys = keys.filter(k => k.startsWith(CACHE_PREFIX))
    let totalSize = 0

    cacheKeys.forEach(key => {
      totalSize += localStorage.getItem(key).length
    })

    return {
      itemCount: cacheKeys.length,
      estimatedSize: (totalSize / 1024).toFixed(2) + ' KB',
    }
  },
}

export const offlineService = {
  /**
   * Check if device is online
   */
  isOnline() {
    return navigator.onLine && window.online !== false
  },

  /**
   * Get data with fallback to cache
   */
  async fetch(url, options = {}) {
    const cacheKey = `GET_${url}`
    const cacheDuration = options.cacheDuration || CACHE_DURATION.MEDIUM

    try {
      if (this.isOnline()) {
        const response = await axios.get(url, options.axiosConfig || {})
        cacheService.set(cacheKey, response.data, cacheDuration)
        return response.data
      } else {
        // Online but try cache first
        const cached = cacheService.get(cacheKey)
        if (cached) return cached
        throw new Error('No cached data available')
      }
    } catch (error) {
      // Offline or error - try cache
      const cached = cacheService.get(cacheKey)
      if (cached) {
        console.warn('Using cached data for:', url)
        return cached
      }
      throw error
    }
  },

  /**
   * Post data with offline queue
   */
  async post(url, data, options = {}) {
    if (!this.isOnline()) {
      return this.queueOfflineRequest('POST', url, data, options)
    }

    try {
      const response = await axios.post(url, data, options.axiosConfig || {})
      return response.data
    } catch (error) {
      // On error, queue for retry
      return this.queueOfflineRequest('POST', url, data, options)
    }
  },

  /**
   * Queue request for offline sync
   */
  queueOfflineRequest(method, url, data, options) {
    const queue = JSON.parse(localStorage.getItem(SYNC_QUEUE_KEY) || '[]')

    queue.push({
      id: Date.now() + Math.random(),
      method,
      url,
      data,
      timestamp: Date.now(),
      options,
    })

    localStorage.setItem(SYNC_QUEUE_KEY, JSON.stringify(queue))

    return {
      offline: true,
      queued: true,
      message: 'Request queued. Will sync when online.',
    }
  },

  /**
   * Sync offline queue when coming back online
   */
  async syncQueue() {
    if (!this.isOnline()) return

    const queue = JSON.parse(localStorage.getItem(SYNC_QUEUE_KEY) || '[]')
    const results = []

    for (const request of queue) {
      try {
        let response
        if (request.method === 'POST') {
          response = await axios.post(request.url, request.data, request.options.axiosConfig || {})
        } else if (request.method === 'PUT') {
          response = await axios.put(request.url, request.data, request.options.axiosConfig || {})
        } else if (request.method === 'DELETE') {
          response = await axios.delete(request.url, request.options.axiosConfig || {})
        }

        results.push({
          id: request.id,
          success: true,
          response: response?.data,
        })

        // Remove from queue
        const index = queue.indexOf(request)
        queue.splice(index, 1)
      } catch (error) {
        results.push({
          id: request.id,
          success: false,
          error: error.message,
        })
      }
    }

    // Update queue
    localStorage.setItem(SYNC_QUEUE_KEY, JSON.stringify(queue))

    return results
  },

  /**
   * Get pending sync requests
   */
  getPendingRequests() {
    return JSON.parse(localStorage.getItem(SYNC_QUEUE_KEY) || '[]')
  },

  /**
   * Clear sync queue
   */
  clearQueue() {
    localStorage.removeItem(SYNC_QUEUE_KEY)
  },
}

/**
 * Listen to online/offline events
 */
export function setupOfflineListener(onStatusChange) {
  window.addEventListener('online', () => {
    console.log('Device is online')
    onStatusChange(true)
    // Try to sync queued requests
    offlineService.syncQueue()
  })

  window.addEventListener('offline', () => {
    console.log('Device is offline')
    onStatusChange(false)
  })
}

export default {
  cacheService,
  offlineService,
  setupOfflineListener,
  CACHE_DURATION,
}
