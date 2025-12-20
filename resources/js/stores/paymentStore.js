import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const usePaymentStore = defineStore('payment', () => {
  const currentPayment = ref(null)
  const lastPayment = ref(null)
  const payments = ref([])
  const invoices = ref([])
  const loading = ref(false)

  // Computed properties
  const isPending = computed(() => currentPayment.value?.status === 'pending')
  const isProcessing = computed(() => currentPayment.value?.status === 'processing')
  const isCompleted = computed(() => currentPayment.value?.status === 'completed')
  const isFailed = computed(() => currentPayment.value?.status === 'failed')
  const isRefunded = computed(() => currentPayment.value?.status === 'refunded')

  // Tax calculations
  const calculateTaxes = (amount) => {
    const pph = Math.round(amount * 0.15) // 15%
    const ppn = Math.round(amount * 0.11) // 11%
    return {
      pph,
      ppn,
      total: pph + ppn,
      rate: 0.26,
    }
  }

  // Get total with taxes
  const getTotalWithTax = (amount) => {
    const taxes = calculateTaxes(amount)
    return amount + taxes.total
  }

  // Format currency
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount)
  }

  // Set current payment
  const setCurrentPayment = (payment) => {
    currentPayment.value = payment
  }

  // Set last payment
  const setLastPayment = (payment) => {
    lastPayment.value = payment
    currentPayment.value = payment
  }

  // Update payment status
  const updatePaymentStatus = (paymentId, status) => {
    if (currentPayment.value?.id === paymentId) {
      currentPayment.value.status = status
    }
    const payment = payments.value.find(p => p.id === paymentId)
    if (payment) {
      payment.status = status
    }
  }

  // Clear current payment
  const clearCurrentPayment = () => {
    currentPayment.value = null
  }

  // Set payments list
  const setPayments = (paymentsList) => {
    payments.value = paymentsList
  }

  // Add payment to list
  const addPayment = (payment) => {
    payments.value.unshift(payment)
  }

  // Set invoices list
  const setInvoices = (invoicesList) => {
    invoices.value = invoicesList
  }

  // Add invoice to list
  const addInvoice = (invoice) => {
    invoices.value.unshift(invoice)
  }

  // Get payment by ID
  const getPaymentById = (id) => {
    return payments.value.find(p => p.id === id)
  }

  // Get invoice by ID
  const getInvoiceById = (id) => {
    return invoices.value.find(i => i.id === id)
  }

  // Filter payments by status
  const filterByStatus = (status) => {
    return payments.value.filter(p => p.status === status)
  }

  // Filter payments by method
  const filterByMethod = (method) => {
    return payments.value.filter(p => p.payment_method === method)
  }

  // Get payment statistics
  const getStatistics = () => {
    return {
      totalPayments: payments.value.length,
      totalAmount: payments.value.reduce((sum, p) => sum + p.amount, 0),
      completedAmount: payments.value
        .filter(p => p.status === 'completed')
        .reduce((sum, p) => sum + p.amount, 0),
      pendingAmount: payments.value
        .filter(p => p.status === 'pending')
        .reduce((sum, p) => sum + p.amount, 0),
      failedAmount: payments.value
        .filter(p => ['failed', 'refunded'].includes(p.status))
        .reduce((sum, p) => sum + p.amount, 0),
      byMethod: {
        stripe: payments.value.filter(p => p.payment_method === 'stripe').length,
        gcash: payments.value.filter(p => p.payment_method === 'gcash').length,
        bank_transfer: payments.value.filter(p => p.payment_method === 'bank_transfer').length,
        e_wallet: payments.value.filter(p => p.payment_method === 'e_wallet').length,
      },
    }
  }

  // Clear all data
  const clearAll = () => {
    currentPayment.value = null
    lastPayment.value = null
    payments.value = []
    invoices.value = []
  }

  return {
    // State
    currentPayment,
    lastPayment,
    payments,
    invoices,
    loading,

    // Computed
    isPending,
    isProcessing,
    isCompleted,
    isFailed,
    isRefunded,

    // Methods
    calculateTaxes,
    getTotalWithTax,
    formatCurrency,
    setCurrentPayment,
    setLastPayment,
    updatePaymentStatus,
    clearCurrentPayment,
    setPayments,
    addPayment,
    setInvoices,
    addInvoice,
    getPaymentById,
    getInvoiceById,
    filterByStatus,
    filterByMethod,
    getStatistics,
    clearAll,
  }
})
