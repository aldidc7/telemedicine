/**
 * Payment API Service
 * Handles all payment-related API calls
 */

const API_BASE = '/api/v1'

class PaymentService {
  constructor() {
    this.baseURL = API_BASE
    this.token = localStorage.getItem('token')
  }

  /**
   * Get request headers with authentication
   */
  getHeaders() {
    return {
      'Authorization': `Bearer ${this.token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
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
   * Create Payment
   * POST /api/v1/payments
   */
  async createPayment(paymentData) {
    try {
      const response = await fetch(`${this.baseURL}/payments`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(paymentData),
      })

      const data = await response.json()

      if (!response.ok) {
        return {
          success: false,
          message: data.message || 'Gagal membuat pembayaran',
          errors: data.errors,
        }
      }

      return {
        success: true,
        data: data.data,
        message: 'Pembayaran berhasil dibuat',
      }
    } catch (error) {
      return this.handleError(error)
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
}

export default new PaymentService()
