/**
 * ============================================
 * REQUEST LOGGER
 * ============================================
 * 
 * Log all API requests untuk debugging
 */
class RequestLogger {
  constructor() {
    this.logs = []
  }

  /**
   * Setup interceptor
   */
  static setup(client) {
    const logger = new RequestLogger()

    // Request interceptor
    client.interceptors.request.use(config => {
      const timestamp = new Date().toISOString()
      logger.logRequest(timestamp, config.method.toUpperCase(), config.url, config.data)
      
      // Attach timestamp to config untuk performance tracking
      config.metadata = { startTime: performance.now() }
      return config
    })

    // Response interceptor
    client.interceptors.response.use(
      response => {
        const duration = performance.now() - response.config.metadata.startTime
        logger.logResponse(response.config.url, response.status, duration)
        return response
      },
      error => {
        if (error.config) {
          const duration = error.config.metadata 
            ? performance.now() - error.config.metadata.startTime 
            : 0
          logger.logError(error.config.url, error.response?.status, duration, error)
        }
        return Promise.reject(error)
      }
    )
  }

  /**
   * Log request
   */
  logRequest(timestamp, method, url, data) {
    const log = {
      type: 'REQUEST',
      timestamp,
      method,
      url: url.replace(import.meta.env.VITE_API_URL, ''),
      data: this.sanitizeData(data),
    }
    this.logs.push(log)
    
    if (import.meta.env.DEV) {
      console.log(`[${method}] ${log.url}`, log.data)
    }
  }

  /**
   * Log response
   */
  logResponse(url, status, duration) {
    const log = {
      type: 'RESPONSE',
      timestamp: new Date().toISOString(),
      url: url.replace(import.meta.env.VITE_API_URL, ''),
      status,
      duration: `${duration.toFixed(2)}ms`,
    }
    this.logs.push(log)
    
    if (import.meta.env.DEV && status >= 400) {
      console.warn(`[${status}] ${log.url}`)
    }
  }

  /**
   * Log error
   */
  logError(url, status, duration, error) {
    const log = {
      type: 'ERROR',
      timestamp: new Date().toISOString(),
      url: url.replace(import.meta.env.VITE_API_URL, ''),
      status: status || 'Network Error',
      duration: `${duration.toFixed(2)}ms`,
      message: error.message,
    }
    this.logs.push(log)
    
    if (import.meta.env.DEV) {
      console.error(`[ERROR] ${log.url}`, error)
    }
  }

  /**
   * Get all logs
   */
  getLogs() {
    return this.logs
  }

  /**
   * Clear logs
   */
  clearLogs() {
    this.logs = []
  }

  /**
   * Sanitize sensitive data
   */
  sanitizeData(data) {
    if (!data) return data
    
    const sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card']
    const sanitized = { ...data }
    
    sensitiveKeys.forEach(key => {
      if (sanitized[key]) {
        sanitized[key] = '***REDACTED***'
      }
    })
    
    return sanitized
  }
}

export default RequestLogger
