/**
 * WebSocket Service for Real-time Analytics Updates
 * Provides bi-directional communication for live metric updates
 */

class AnalyticsWebSocket {
  constructor() {
    this.ws = null
    this.url = null
    this.reconnectAttempts = 0
    this.maxReconnectAttempts = 5
    this.reconnectDelay = 3000
    this.listeners = new Map()
    this.isIntentionallyClosed = false
  }

  /**
   * Connect to WebSocket server
   * @param {string} token - Authentication token
   * @returns {Promise}
   */
  connect(token) {
    return new Promise((resolve, reject) => {
      try {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:'
        const host = window.location.host
        this.url = `${protocol}//${host}/ws/analytics?token=${token}`

        this.ws = new WebSocket(this.url)

        this.ws.onopen = () => {
          console.log('[Analytics WS] Connected')
          this.reconnectAttempts = 0
          resolve()
        }

        this.ws.onmessage = (event) => {
          this.handleMessage(event.data)
        }

        this.ws.onerror = (error) => {
          console.error('[Analytics WS] Error:', error)
          reject(error)
        }

        this.ws.onclose = () => {
          console.log('[Analytics WS] Connection closed')
          if (!this.isIntentionallyClosed) {
            this.attemptReconnect()
          }
        }
      } catch (error) {
        reject(error)
      }
    })
  }

  /**
   * Handle incoming WebSocket messages
   * @param {string} data - Message data
   */
  handleMessage(data) {
    try {
      const message = JSON.parse(data)
      const { type, payload } = message

      // Emit event to all listeners
      if (this.listeners.has(type)) {
        const callbacks = this.listeners.get(type)
        callbacks.forEach(callback => callback(payload))
      }
    } catch (error) {
      console.error('[Analytics WS] Failed to parse message:', error)
    }
  }

  /**
   * Subscribe to WebSocket events
   * @param {string} eventType - Event type to listen for
   * @param {function} callback - Callback function
   * @returns {function} Unsubscribe function
   */
  subscribe(eventType, callback) {
    if (!this.listeners.has(eventType)) {
      this.listeners.set(eventType, [])
    }

    this.listeners.get(eventType).push(callback)

    // Return unsubscribe function
    return () => {
      const callbacks = this.listeners.get(eventType)
      const index = callbacks.indexOf(callback)
      if (index > -1) {
        callbacks.splice(index, 1)
      }
    }
  }

  /**
   * Send message to server
   * @param {string} type - Message type
   * @param {object} payload - Message payload
   */
  send(type, payload = {}) {
    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify({ type, payload }))
    } else {
      console.warn('[Analytics WS] WebSocket not connected')
    }
  }

  /**
   * Request specific analytics metrics
   * @param {string[]} metrics - Array of metric names
   */
  requestMetrics(metrics) {
    this.send('REQUEST_METRICS', { metrics })
  }

  /**
   * Subscribe to consultation updates
   * @param {function} callback
   * @returns {function} Unsubscribe function
   */
  onConsultationUpdate(callback) {
    return this.subscribe('CONSULTATION_UPDATE', callback)
  }

  /**
   * Subscribe to doctor performance updates
   * @param {function} callback
   * @returns {function} Unsubscribe function
   */
  onDoctorPerformanceUpdate(callback) {
    return this.subscribe('DOCTOR_PERFORMANCE_UPDATE', callback)
  }

  /**
   * Subscribe to revenue updates
   * @param {function} callback
   * @returns {function} Unsubscribe function
   */
  onRevenueUpdate(callback) {
    return this.subscribe('REVENUE_UPDATE', callback)
  }

  /**
   * Subscribe to health trends updates
   * @param {function} callback
   * @returns {function} Unsubscribe function
   */
  onHealthTrendsUpdate(callback) {
    return this.subscribe('HEALTH_TRENDS_UPDATE', callback)
  }

  /**
   * Attempt to reconnect to WebSocket
   */
  attemptReconnect() {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.error('[Analytics WS] Max reconnection attempts reached')
      return
    }

    this.reconnectAttempts++
    console.log(
      `[Analytics WS] Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`
    )

    setTimeout(() => {
      if (this.ws && this.ws.readyState === WebSocket.CLOSED) {
        this.connect(this.currentToken).catch(error => {
          console.error('[Analytics WS] Reconnection failed:', error)
        })
      }
    }, this.reconnectDelay)
  }

  /**
   * Disconnect from WebSocket
   */
  disconnect() {
    this.isIntentionallyClosed = true
    if (this.ws) {
      this.ws.close()
      this.ws = null
    }
    this.listeners.clear()
  }

  /**
   * Check if connected
   * @returns {boolean}
   */
  isConnected() {
    return this.ws && this.ws.readyState === WebSocket.OPEN
  }

  /**
   * Get connection status
   * @returns {string}
   */
  getStatus() {
    if (!this.ws) return 'disconnected'
    switch (this.ws.readyState) {
      case WebSocket.CONNECTING:
        return 'connecting'
      case WebSocket.OPEN:
        return 'connected'
      case WebSocket.CLOSING:
        return 'closing'
      case WebSocket.CLOSED:
        return 'disconnected'
      default:
        return 'unknown'
    }
  }
}

// Export singleton instance
export default new AnalyticsWebSocket()
