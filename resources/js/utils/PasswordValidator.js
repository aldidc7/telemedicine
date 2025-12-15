/**
 * ============================================
 * PASSWORD STRENGTH VALIDATOR
 * ============================================
 * 
 * User-friendly password validation dengan feedback
 * Tidak perlu complex requirements, fokus pada length
 */

class PasswordValidator {
  /**
   * Check password strength
   * Returns: weak, fair, good, strong
   * 
   * @param {string} password
   * @returns {Object} { strength, score, feedback }
   */
  static checkStrength(password) {
    let score = 0
    const feedback = []

    // Check length (most important)
    if (password.length >= 8) {
      score += 30
      feedback.push('✓ Panjang cukup')
    } else {
      feedback.push(`✗ Minimal 8 karakter (saat ini: ${password.length})`)
      return {
        strength: 'weak',
        score: Math.min(score, 100),
        feedback,
        isValid: false,
      }
    }

    // Bonus untuk lebih panjang
    if (password.length >= 12) {
      score += 15
      feedback.push('✓ Panjang sangat baik')
    }

    // Bonus untuk variasi karakter (optional)
    if (/[a-z]/.test(password)) {
      score += 10
      feedback.push('✓ Mengandung huruf kecil')
    }

    if (/[A-Z]/.test(password)) {
      score += 10
      feedback.push('✓ Mengandung huruf besar')
    }

    if (/[0-9]/.test(password)) {
      score += 10
      feedback.push('✓ Mengandung angka')
    }

    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
      score += 15
      feedback.push('✓ Mengandung karakter spesial')
    }

    // Determine strength level
    let strength = 'weak'
    if (score >= 80) {
      strength = 'strong'
    } else if (score >= 60) {
      strength = 'good'
    } else if (score >= 40) {
      strength = 'fair'
    }

    return {
      strength,
      score: Math.min(score, 100),
      feedback,
      isValid: password.length >= 8,
    }
  }

  /**
   * Get strength color untuk UI
   * 
   * @param {string} strength
   * @returns {string} color class
   */
  static getStrengthColor(strength) {
    const colors = {
      weak: 'text-red-500',
      fair: 'text-yellow-500',
      good: 'text-blue-500',
      strong: 'text-green-500',
    }
    return colors[strength] || 'text-gray-500'
  }

  /**
   * Get strength label bahasa Indonesia
   * 
   * @param {string} strength
   * @returns {string} label
   */
  static getStrengthLabel(strength) {
    const labels = {
      weak: 'Lemah',
      fair: 'Cukup',
      good: 'Baik',
      strong: 'Sangat Kuat',
    }
    return labels[strength] || 'Tidak Diketahui'
  }

  /**
   * Validate password matches confirmation
   * 
   * @param {string} password
   * @param {string} confirmation
   * @returns {Object} { isValid, message }
   */
  static checkMatch(password, confirmation) {
    if (!password || !confirmation) {
      return {
        isValid: false,
        message: 'Masukkan kedua password',
      }
    }

    if (password !== confirmation) {
      return {
        isValid: false,
        message: 'Password tidak sama',
      }
    }

    return {
      isValid: true,
      message: 'Password cocok',
    }
  }

  /**
   * Check password best practices (gentle suggestions)
   * 
   * @param {string} password
   * @returns {Array} suggestions
   */
  static getSuggestions(password) {
    const suggestions = []

    if (!/[a-z]/.test(password)) {
      suggestions.push('💡 Coba tambahkan huruf kecil untuk keamanan lebih baik')
    }

    if (!/[A-Z]/.test(password)) {
      suggestions.push('💡 Coba tambahkan huruf besar untuk keamanan lebih baik')
    }

    if (!/[0-9]/.test(password)) {
      suggestions.push('💡 Coba tambahkan angka untuk keamanan lebih baik')
    }

    if (password.length < 12) {
      suggestions.push('💡 Semakin panjang password semakin aman (target: 12+ karakter)')
    }

    return suggestions
  }

  /**
   * Validate password meets minimum requirements
   * 
   * @param {string} password
   * @returns {boolean}
   */
  static isValid(password) {
    return password && password.length >= 8
  }
}

export default PasswordValidator
