/**
 * Analytics Cache Manager
 * Provides utilities for managing analytics cache state and invalidation
 */

class AnalyticsCacheManager {
  /**
   * Cache keys used by analytics
   */
  static CACHE_KEYS = {
    CONSULTATION_METRICS: 'analytics:consultation',
    DOCTOR_PERFORMANCE: 'analytics:doctors',
    HEALTH_TRENDS: 'analytics:health-trends',
    REVENUE_ANALYTICS: 'analytics:revenue',
    DASHBOARD_OVERVIEW: 'analytics:overview',
    DATE_RANGE: 'analytics:range',
  }

  /**
   * Cache TTL values (in seconds)
   */
  static TTL = {
    CONSULTATION_METRICS: 300, // 5 minutes
    DOCTOR_PERFORMANCE: 600, // 10 minutes
    HEALTH_TRENDS: 600, // 10 minutes
    REVENUE_ANALYTICS: 900, // 15 minutes
    DASHBOARD_OVERVIEW: 300, // 5 minutes
    DATE_RANGE: 1800, // 30 minutes
  }

  /**
   * Get formatted cache key with period
   * @param {string} baseKey - Base cache key
   * @param {string} identifier - Period or custom identifier
   * @returns {string}
   */
  static getKeyWithIdentifier(baseKey, identifier) {
    return `${baseKey}:${identifier}`
  }

  /**
   * Create cache invalidation strategy
   * @returns {object} Strategy with event handlers
   */
  static createInvalidationStrategy() {
    return {
      // Invalidate on new consultation
      onConsultationCreated: [
        this.CACHE_KEYS.CONSULTATION_METRICS,
        this.CACHE_KEYS.DASHBOARD_OVERVIEW,
        this.CACHE_KEYS.REVENUE_ANALYTICS,
      ],

      // Invalidate on consultation completion
      onConsultationCompleted: [
        this.CACHE_KEYS.CONSULTATION_METRICS,
        this.CACHE_KEYS.DASHBOARD_OVERVIEW,
        this.CACHE_KEYS.REVENUE_ANALYTICS,
        this.CACHE_KEYS.DOCTOR_PERFORMANCE,
      ],

      // Invalidate on rating
      onRatingCreated: [
        this.CACHE_KEYS.DOCTOR_PERFORMANCE,
        this.CACHE_KEYS.DASHBOARD_OVERVIEW,
      ],

      // Invalidate on health data update
      onHealthTrendUpdated: [
        this.CACHE_KEYS.HEALTH_TRENDS,
        this.CACHE_KEYS.DASHBOARD_OVERVIEW,
      ],
    }
  }

  /**
   * Format cache expiration time
   * @param {number} seconds
   * @returns {string}
   */
  static formatExpiration(seconds) {
    if (seconds < 60) return `${seconds}s`
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`
    return `${Math.floor(seconds / 3600)}h`
  }

  /**
   * Estimate cache freshness
   * @param {number} ttl - Time to live in seconds
   * @param {number} elapsedSeconds - Elapsed time in seconds
   * @returns {object} Freshness info
   */
  static estimateFreshness(ttl, elapsedSeconds) {
    const freshness = Math.max(0, ((ttl - elapsedSeconds) / ttl) * 100)
    
    return {
      percentage: Math.round(freshness),
      status: freshness > 75 ? 'fresh' : freshness > 25 ? 'stale' : 'expired',
      remainingSeconds: Math.max(0, ttl - elapsedSeconds),
    }
  }

  /**
   * Create optimal cache warming strategy
   * @returns {array} Array of cache keys to warm in order
   */
  static createWarmingStrategy() {
    // Warm cache in priority order
    return [
      { key: this.CACHE_KEYS.CONSULTATION_METRICS, ttl: this.TTL.CONSULTATION_METRICS, priority: 1 },
      { key: this.CACHE_KEYS.DOCTOR_PERFORMANCE, ttl: this.TTL.DOCTOR_PERFORMANCE, priority: 2 },
      { key: this.CACHE_KEYS.REVENUE_ANALYTICS, ttl: this.TTL.REVENUE_ANALYTICS, priority: 3 },
      { key: this.CACHE_KEYS.HEALTH_TRENDS, ttl: this.TTL.HEALTH_TRENDS, priority: 4 },
      { key: this.CACHE_KEYS.DASHBOARD_OVERVIEW, ttl: this.TTL.DASHBOARD_OVERVIEW, priority: 5 },
    ]
  }

  /**
   * Get cache key patterns for batch invalidation
   * @param {string} pattern - Pattern to match
   * @returns {array} Array of matching keys
   */
  static getKeysByPattern(pattern) {
    const allKeys = Object.values(this.CACHE_KEYS)
    return allKeys.filter(key => key.includes(pattern))
  }
}

export default AnalyticsCacheManager
