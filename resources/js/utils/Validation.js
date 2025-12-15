/**
 * ============================================
 * VALIDATION UTILITIES
 * ============================================
 * 
 * Centralized input validation & sanitization
 */

class Validator {
  /**
   * Email validation
   */
  static email(value) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return regex.test(value)
  }

  /**
   * Password validation (min 8 chars, uppercase, number)
   */
  static password(value) {
    return value.length >= 8 && 
           /[A-Z]/.test(value) && 
           /[0-9]/.test(value)
  }

  /**
   * Phone number validation (Indonesian format)
   */
  static phone(value) {
    const regex = /^(\+62|0)[0-9]{9,12}$/
    return regex.test(value)
  }

  /**
   * NIK validation (16 digits)
   */
  static nik(value) {
    return /^\d{16}$/.test(value)
  }

  /**
   * URL validation
   */
  static url(value) {
    try {
      new URL(value)
      return true
    } catch {
      return false
    }
  }

  /**
   * Required field
   */
  static required(value) {
    return value != null && value !== '' && value !== undefined
  }

  /**
   * Min length
   */
  static minLength(value, min) {
    return value && value.length >= min
  }

  /**
   * Max length
   */
  static maxLength(value, max) {
    return !value || value.length <= max
  }

  /**
   * Min value (for numbers)
   */
  static minValue(value, min) {
    return Number(value) >= min
  }

  /**
   * Max value (for numbers)
   */
  static maxValue(value, max) {
    return Number(value) <= max
  }

  /**
   * Numeric
   */
  static numeric(value) {
    return /^\d+$/.test(value)
  }

  /**
   * Alphanumeric
   */
  static alphanumeric(value) {
    return /^[a-zA-Z0-9]+$/.test(value)
  }

  /**
   * Match pattern
   */
  static pattern(value, regex) {
    return regex.test(value)
  }
}

/**
 * Sanitizer class
 */
class Sanitizer {
  /**
   * Remove HTML tags
   */
  static stripTags(text) {
    const div = document.createElement('div')
    div.textContent = text
    return div.innerHTML
  }

  /**
   * Escape HTML entities
   */
  static escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }
    return text.replace(/[&<>"']/g, m => map[m])
  }

  /**
   * Trim whitespace
   */
  static trim(text) {
    return text.trim()
  }

  /**
   * Convert to lowercase
   */
  static toLowerCase(text) {
    return text.toLowerCase()
  }

  /**
   * Remove special characters
   */
  static removeSpecialChars(text) {
    return text.replace(/[^a-zA-Z0-9\s]/g, '')
  }

  /**
   * Remove numbers
   */
  static removeNumbers(text) {
    return text.replace(/[0-9]/g, '')
  }

  /**
   * Truncate text
   */
  static truncate(text, length = 100, suffix = '...') {
    if (text.length <= length) return text
    return text.slice(0, length) + suffix
  }

  /**
   * Sanitize object (remove null/undefined, escape strings)
   */
  static sanitizeObject(obj) {
    const sanitized = {}
    for (const key in obj) {
      if (obj[key] != null && obj[key] !== '') {
        sanitized[key] = typeof obj[key] === 'string' 
          ? this.escapeHtml(obj[key].trim())
          : obj[key]
      }
    }
    return sanitized
  }
}

export { Validator, Sanitizer }
