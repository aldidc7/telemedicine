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
        message: 'Tidak ada respons dari server. Silakan periksa koneksi Anda.',
        errors: {},
      }
    } else {
      // Error in request setup
      return {
        success: false,
        status: 0,
        message: error.message || 'Terjadi kesalahan yang tidak terduga',
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
   * Get user-friendly error message (Pesan error yang ramah ke pengguna)
   */
  static getUserMessage(error) {
    // Handle null/undefined error
    if (!error) {
      return 'Terjadi kesalahan. Silakan coba lagi.'
    }

    const { status, message } = this.handle(error)

    const messages = {
      400: 'Permintaan tidak valid. Silakan periksa input Anda.',
      401: 'Sesi Anda telah berakhir. Silakan login kembali.',
      403: 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
      404: 'Resource tidak ditemukan.',
      409: 'Resource ini sudah ada.',
      422: 'Silakan periksa input Anda dan coba lagi.',
      429: 'Terlalu banyak permintaan. Tunggu sebentar dan coba lagi.',
      500: 'Error server. Silakan coba lagi nanti.',
      502: 'Layanan tidak tersedia sementara. Silakan coba lagi nanti.',
      503: 'Layanan sedang dalam pemeliharaan. Silakan coba lagi nanti.',
    }

    return messages[status] || message || 'Terjadi kesalahan. Silakan coba lagi.'
  }
}

export default ErrorHandler
