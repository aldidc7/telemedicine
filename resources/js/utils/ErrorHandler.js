import client from '../api/client'

/**
 * ============================================
 * ERROR HANDLER SERVICE (Frontend)
 * ============================================
 * 
 * Centralized error handling untuk semua API calls
 */
class ErrorHandler {
  /**
   * Handle API error dengan consistent formatting
   * 
   * @param {Error|AxiosError} error
   * @param {string} context - Untuk debugging
   * @returns {Object} Standardized error object
   */
  static handle(error, context = '') {
    console.error(`[${context}] Error:`, error)

    if (error.response) {
      // Server responded with error status
      return {
        success: false,
        status: error.response.status,
        message: error.response.data?.pesan || 'An error occurred',
        errors: error.response.data?.data?.errors || {},
        data: error.response.data,
      }
    } else if (error.request) {
      // Request made but no response
      return {
        success: false,
        status: 0,
        message: 'No response from server. Please check your connection.',
        errors: {},
      }
    } else {
      // Error in request setup
      return {
        success: false,
        status: 0,
        message: error.message || 'An unexpected error occurred',
        errors: {},
      }
    }
  }

  /**
   * Check if error is retryable
   */
  static isRetryable(status) {
    const retryableStatuses = [408, 429, 500, 502, 503, 504]
    return retryableStatuses.includes(status)
  }

  /**
   * Get user-friendly error message
   */
  static getUserMessage(error) {
    const { status, message } = this.handle(error)

    const messages = {
      400: 'Invalid request. Please check your input.',
      401: 'Your session has expired. Please login again.',
      403: 'You do not have permission to perform this action.',
      404: 'Resource not found.',
      409: 'This resource already exists.',
      422: 'Please check your input and try again.',
      429: 'Too many requests. Please wait a moment and try again.',
      500: 'Server error. Please try again later.',
      502: 'Service temporarily unavailable. Please try again later.',
      503: 'Service is under maintenance. Please try again later.',
    }

    return messages[status] || message || 'An error occurred. Please try again.'
  }
}

export default ErrorHandler
