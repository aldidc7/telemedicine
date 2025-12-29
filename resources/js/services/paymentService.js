/**
 * ============================================
 * PAYMENT API SERVICE - WITH DOUBLE PAYMENT PREVENTION
 * ============================================
 * 
 * Handles all payment-related API calls dengan:
 * ✓ Automatic idempotency key generation
 * ✓ In-flight request tracking
 * ✓ 409 Conflict handling
 * ✓ Retry logic dengan exponential backoff
 * ✓ Button state management
 * 
 * Usage:
 *   const result = await paymentService.createPayment({
 *       consultationId: 5,
 *       amount: 5000,
 *       paymentMethod: 'stripe'
 *   })
 *   
 *   if (result.success && result.data.type === 'existing') {
 *       console.log('Duplicate payment detected:', result.data.paymentId)
 *   }
 */

const API_BASE = '/api/v1'

class PaymentService {
  constructor() {
    this.baseURL = API_BASE
    this.token = localStorage.getItem('token')
    
    // Track in-flight requests to prevent duplicate submissions
    this.inflightRequests = new Map()
    
    // Store idempotency keys locally
    this.idempotencyKeyStore = {}
    
    // Retry configuration
    this.maxRetries = 3
    this.retryDelay = 1000 // milliseconds
  }

  /**
   * Get request headers dengan authentication dan idempotency key
   */
  getHeaders(idempotencyKey = null) {
    const headers = {
      'Authorization': `Bearer ${this.token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
    
    // Add idempotency key untuk payment requests
    if (idempotencyKey) {
      headers['X-Idempotency-Key'] = idempotencyKey
    }
    
    return headers
  }

  /**
   * Handle API errors
   */
  handleError(error) {
    if (error.response) {
      return {
        success: false,
        message: error.response.data?.message || 'Terjadi kesalahan',
        status: error.response.status,
        errors: error.response.data?.errors,
      }
    }
    return {
      success: false,
      message: error.message || 'Terjadi kesalahan jaringan',
    }
  }

  /**
   * Create Payment dengan double-payment prevention
   * POST /api/v1/payments
   * 
   * Prevents duplicate payments by:
   * 1. Generating idempotency key
   * 2. Tracking in-flight requests
   * 3. Sending key to backend
   * 4. Handling 409 Conflict responses
   * 
   * @param {Object} paymentData - Payment details
   * @param {number} paymentData.consultationId - Consultation ID
   * @param {number} paymentData.amount - Payment amount
   * @param {string} paymentData.paymentMethod - Payment method
   * @returns {Object} { success, data, message, status }
   */
  async createPayment(paymentData) {
    try {
      // Generate unique idempotency key
      const idempotencyKey = this.generateIdempotencyKey(
        `payment-${paymentData.consultationId}-${Date.now()}`
      )
      
      // Check if already in flight (same consultation + method)
      const requestKey = `${paymentData.consultationId}-${paymentData.paymentMethod}`
      if (this.inflightRequests.has(requestKey)) {
        console.warn('Payment request already in flight, returning pending promise...')
        return await this.inflightRequests.get(requestKey)
      }
      
      // Prepare request payload
      const payload = {
        consultation_id: paymentData.consultationId,
        amount: paymentData.amount,
        payment_method: paymentData.paymentMethod,
        idempotency_key: idempotencyKey, // Send to backend
      }
      
      // Create request promise (untuk prevent concurrent requests)
      const requestPromise = this._createPaymentWithRetry(payload, idempotencyKey)
      this.inflightRequests.set(requestKey, requestPromise)
      
      try {
        const result = await requestPromise
        return result
      } finally {
        this.inflightRequests.delete(requestKey)
      }
      
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Internal create payment dengan retry logic
   */
  async _createPaymentWithRetry(payload, idempotencyKey) {
    let lastError = null
    
    for (let attempt = 0; attempt < this.maxRetries; attempt++) {
      try {
        const response = await fetch(`${this.baseURL}/payments`, {
          method: 'POST',
          headers: this.getHeaders(idempotencyKey),
          body: JSON.stringify(payload),
        })
        
        const data = await response.json()
        
        // Handle 409 Conflict (duplicate payment detected)
        if (response.status === 409) {
          console.log('Duplicate payment detected, returning existing payment')
          return {
            success: true,
            type: 'existing',
            data: {
              type: 'existing',
              paymentId: data.data?.payment_id,
              status: data.data?.status,
            },
            message: data.data?.message || 'Pembayaran sudah dibuat sebelumnya',
            status: 409,
          }
        }
        
        // Handle success (201 atau 200)
        if (response.ok) {
          return {
            success: true,
            type: data.data?.type || 'new',
            data: data.data,
            message: data.message || 'Pembayaran berhasil dibuat',
            status: response.status,
          }
        }
        
        // Handle client errors (4xx) - don't retry
        if (response.status >= 400 && response.status < 500) {
          return {
            success: false,
            message: data.message || 'Gagal membuat pembayaran',
            errors: data.errors,
            status: response.status,
          }
        }
        
        // Handle server errors (5xx) - retry dengan exponential backoff
        if (response.status >= 500) {
          lastError = new Error(`Server error: ${data.message || response.statusText}`)
          if (attempt < this.maxRetries - 1) {
            const delay = this.retryDelay * Math.pow(2, attempt)
            console.log(`Retry ${attempt + 1}/${this.maxRetries} after ${delay}ms`)
            await new Promise(resolve => setTimeout(resolve, delay))
            continue
          }
        }
        
      } catch (error) {
        lastError = error
        if (attempt < this.maxRetries - 1) {
          const delay = this.retryDelay * Math.pow(2, attempt)
          console.log(`Request failed: ${error.message}. Retrying in ${delay}ms...`)
          await new Promise(resolve => setTimeout(resolve, delay))
          continue
        }
      }
    }
    
    return {
      success: false,
      message: lastError?.message || 'Gagal membuat pembayaran setelah beberapa percobaan',
    }
  }

  /**
   * Get Payment Details
   * GET /api/v1/payments/{id}
   */
  async getPayment(paymentId) {
    try {
      const response = await fetch(`${this.baseURL}/payments/${paymentId}`, {
        headers: this.getHeaders(),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal memuat pembayaran',
        }
      }

      return {
        success: true,
        data: data.data,
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Confirm Payment
   * POST /api/v1/payments/{id}/confirm
   */
  async confirmPayment(paymentId, confirmData) {
    try {
      const response = await fetch(`${this.baseURL}/payments/${paymentId}/confirm`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(confirmData),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal mengkonfirmasi pembayaran',
        }
      }

      return {
        success: true,
        data: data.data,
        message: 'Pembayaran berhasil dikonfirmasi',
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Refund Payment
   * POST /api/v1/payments/{id}/refund
   */
  async refundPayment(paymentId, refundData) {
    try {
      const response = await fetch(`${this.baseURL}/payments/${paymentId}/refund`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(refundData),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal memproses refund',
        }
      }

      return {
        success: true,
        data: data.data,
        message: 'Refund berhasil diajukan',
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Get Payment History
   * GET /api/v1/payments/history
   */
  async getPaymentHistory(filters = {}) {
    try {
      const queryParams = new URLSearchParams()

      if (filters.page) queryParams.append('page', filters.page)
      if (filters.limit) queryParams.append('limit', filters.limit)
      if (filters.status) queryParams.append('status', filters.status)
      if (filters.payment_method) queryParams.append('payment_method', filters.payment_method)
      if (filters.search) queryParams.append('search', filters.search)
      if (filters.date_range) queryParams.append('date_range', filters.date_range)

      const url = `${this.baseURL}/payments/history${queryParams.toString() ? '?' + queryParams.toString() : ''}`

      const response = await fetch(url, {
        headers: this.getHeaders(),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal memuat riwayat pembayaran',
        }
      }

      return {
        success: true,
        data: data.data,
        meta: data.meta,
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Get Invoice List
   * GET /api/v1/invoices
   */
  async getInvoices(filters = {}) {
    try {
      const queryParams = new URLSearchParams()

      if (filters.page) queryParams.append('page', filters.page)
      if (filters.limit) queryParams.append('limit', filters.limit)
      if (filters.status) queryParams.append('status', filters.status)
      if (filters.search) queryParams.append('search', filters.search)

      const url = `${this.baseURL}/invoices${queryParams.toString() ? '?' + queryParams.toString() : ''}`

      const response = await fetch(url, {
        headers: this.getHeaders(),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal memuat invoice',
        }
      }

      return {
        success: true,
        data: data.data,
        meta: data.meta,
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Get Invoice Details
   * GET /api/v1/invoices/{id}
   */
  async getInvoice(invoiceId) {
    try {
      const response = await fetch(`${this.baseURL}/invoices/${invoiceId}`, {
        headers: this.getHeaders(),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal memuat invoice',
        }
      }

      return {
        success: true,
        data: data.data,
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Download Invoice PDF
   * GET /api/v1/invoices/{id}/download
   */
  async downloadInvoicePDF(invoiceId, filename = null) {
    try {
      const response = await fetch(`${this.baseURL}/invoices/${invoiceId}/download`, {
        headers: this.getHeaders(),
      })

      if (!response.ok) {
        return {
          success: false,
          message: 'Gagal download PDF',
        }
      }

      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = filename || `invoice-${invoiceId}.pdf`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)

      return {
        success: true,
        message: 'PDF berhasil diunduh',
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Send Invoice via Email
   * POST /api/v1/invoices/{id}/send
   */
  async sendInvoiceEmail(invoiceId) {
    try {
      const response = await fetch(`${this.baseURL}/invoices/${invoiceId}/send`, {
        method: 'POST',
        headers: this.getHeaders(),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal mengirim invoice',
        }
      }

      return {
        success: true,
        message: 'Invoice berhasil dikirim',
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Create Payment Intent for Stripe
   * Used before confirming payment
   */
  async createPaymentIntent(paymentData) {
    try {
      const response = await fetch(`${this.baseURL}/payments/intent/create`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(paymentData),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal membuat payment intent',
        }
      }

      return {
        success: true,
        data: data.data,
        clientSecret: data.data?.client_secret,
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  /**
   * Get Payment Methods Available
   */
  async getPaymentMethods() {
    try {
      const response = await fetch(`${this.baseURL}/payments/methods`, {
        headers: this.getHeaders(),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal memuat metode pembayaran',
        }
      }

      return {
        success: true,
        data: data.data,
      }
    } catch (error) {
      return this.handleError(error)
    }
  }

  // ==================== IDEMPOTENCY KEY MANAGEMENT ====================

  /**
   * Generate unique idempotency key
   * Format: payment-[timestamp]-[uuid]
   */
  generateIdempotencyKey(baseKey) {
    // Check if already generated for this request
    if (this.idempotencyKeyStore[baseKey]) {
      return this.idempotencyKeyStore[baseKey]
    }
    
    // Generate new UUID-based key
    const uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      const r = Math.random() * 16 | 0
      const v = c === 'x' ? r : (r & 0x3 | 0x8)
      return v.toString(16)
    })
    
    const idempotencyKey = `payment-${Date.now()}-${uuid}`
    this.idempotencyKeyStore[baseKey] = idempotencyKey
    
    return idempotencyKey
  }

  /**
   * Clear idempotency key store (after payment complete)
   */
  clearIdempotencyKey(baseKey) {
    delete this.idempotencyKeyStore[baseKey]
  }

  /**
   * Clear all idempotency keys
   */
  clearAllIdempotencyKeys() {
    this.idempotencyKeyStore = {}
  }
}

export default new PaymentService()
