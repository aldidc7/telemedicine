/**
 * Currency Formatter
 * Converts number to Indonesian currency format (IDR)
 */
export const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return 'Rp 0'
  
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

/**
 * Date Formatter
 * Converts date string to readable Indonesian format
 * Example: 2025-12-21 -> 21 Desember 2025
 */
export const formatDate = (dateString) => {
  if (!dateString) return '-'

  const date = new Date(dateString)
  const options = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }

  return new Intl.DateTimeFormat('id-ID', options).format(date)
}

/**
 * Time Formatter
 * Converts time string to readable format
 * Example: 14:30:45 -> 14:30
 */
export const formatTime = (dateString) => {
  if (!dateString) return '-'

  const date = new Date(dateString)
  const options = {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'Asia/Jakarta',
  }

  return new Intl.DateTimeFormat('id-ID', options).format(date)
}

/**
 * DateTime Formatter
 * Combines date and time
 */
export const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return `${formatDate(dateString)} ${formatTime(dateString)}`
}

/**
 * Status Label Formatter
 * Converts status code to readable label
 */
export const getStatusLabel = (status, type = 'payment') => {
  const paymentStatuses = {
    pending: 'Menunggu',
    processing: 'Diproses',
    completed: 'Selesai',
    failed: 'Gagal',
    refunded: 'Dikembalikan',
  }

  const invoiceStatuses = {
    draft: 'Draft',
    sent: 'Terkirim',
    overdue: 'Jatuh Tempo',
    paid: 'Terbayar',
    cancelled: 'Dibatalkan',
  }

  const consultationStatuses = {
    pending: 'Menunggu',
    in_progress: 'Berlangsung',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  }

  const statusMap = {
    payment: paymentStatuses,
    invoice: invoiceStatuses,
    consultation: consultationStatuses,
  }

  return statusMap[type]?.[status] || status
}

/**
 * Phone Number Formatter
 * Formats phone number to standard format
 * Example: 081234567890 -> 0812-3456-7890
 */
export const formatPhoneNumber = (phone) => {
  if (!phone) return ''

  const cleaned = phone.replace(/\D/g, '')
  if (cleaned.length === 10) {
    return `${cleaned.slice(0, 3)}-${cleaned.slice(3, 7)}-${cleaned.slice(7)}`
  }
  if (cleaned.length === 11) {
    return `${cleaned.slice(0, 4)}-${cleaned.slice(4, 8)}-${cleaned.slice(8)}`
  }
  if (cleaned.length === 12) {
    return `${cleaned.slice(0, 2)}-${cleaned.slice(2, 6)}-${cleaned.slice(6, 10)}-${cleaned.slice(10)}`
  }

  return phone
}

/**
 * Truncate Text
 * Shortens text to specified length with ellipsis
 */
export const truncate = (text, length = 50) => {
  if (!text) return ''
  return text.length > length ? `${text.substring(0, length)}...` : text
}

/**
 * Capitalize
 * Capitalizes first letter
 */
export const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

/**
 * Camel Case to Readable
 * Converts camelCase or snake_case to readable format
 * Example: payment_method -> Payment Method
 */
export const camelCaseToReadable = (str) => {
  if (!str) return ''

  return str
    .replace(/_/g, ' ')
    .replace(/([a-z])([A-Z])/g, '$1 $2')
    .split(' ')
    .map(word => capitalize(word))
    .join(' ')
}

/**
 * Percentage Formatter
 * Formats number as percentage
 */
export const formatPercentage = (value, decimals = 2) => {
  if (!value && value !== 0) return '0%'
  return `${parseFloat(value).toFixed(decimals)}%`
}

/**
 * File Size Formatter
 * Converts bytes to human readable format
 */
export const formatFileSize = (bytes) => {
  if (!bytes || bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Relative Time Formatter
 * Shows relative time like "2 hours ago"
 */
export const formatRelativeTime = (dateString) => {
  if (!dateString) return '-'

  const date = new Date(dateString)
  const now = new Date()
  const secondsAgo = Math.floor((now - date) / 1000)

  const intervals = {
    year: 31536000,
    month: 2592000,
    week: 604800,
    day: 86400,
    hour: 3600,
    minute: 60,
  }

  for (const [unit, seconds] of Object.entries(intervals)) {
    const interval = Math.floor(secondsAgo / seconds)
    if (interval >= 1) {
      return `${interval} ${unit}${interval !== 1 ? 's' : ''} yang lalu`
    }
  }

  return 'Baru saja'
}

/**
 * JSON Formatter
 * Pretty prints JSON
 */
export const formatJSON = (obj) => {
  return JSON.stringify(obj, null, 2)
}

/**
 * Medical Record ID Formatter
 * Formats MRN (Medical Record Number)
 * Example: 20231234567890 -> MR-2023-1234-567890
 */
export const formatMRN = (mrn) => {
  if (!mrn) return '-'

  const cleaned = mrn.toString().padStart(14, '0')
  return `MR-${cleaned.slice(0, 4)}-${cleaned.slice(4, 8)}-${cleaned.slice(8)}`
}

/**
 * NIM Formatter
 * Formats Doctor/Staff ID
 * Example: 2025001234 -> 2025-001234
 */
export const formatNIM = (nim) => {
  if (!nim) return '-'

  const cleaned = nim.toString().padStart(10, '0')
  return `${cleaned.slice(0, 4)}-${cleaned.slice(4)}`
}

/**
 * Address Formatter
 * Formats address for display
 */
export const formatAddress = (address) => {
  if (!address) return '-'

  return address
    .split(',')
    .map(part => part.trim())
    .join(', ')
}

/**
 * Tax Rate Formatter
 * Formats tax types and rates
 */
export const getTaxLabel = (taxType) => {
  const labels = {
    pph: 'PPh (Pajak Penghasilan)',
    ppn: 'PPN (Pajak Pertambahan Nilai)',
    other: 'Pajak Lainnya',
  }

  return labels[taxType?.toLowerCase()] || taxType
}

export const getTaxRate = (taxType) => {
  const rates = {
    pph: 15,
    ppn: 11,
  }

  return rates[taxType?.toLowerCase()] || 0
}
