import client from './client'

export const authApi = {
  register(data) {
    return client.post('/auth/register', data)
  },

  login(identifier, password) {
    return client.post('/auth/login', { email: identifier, password })
  },

  getProfile() {
    return client.get('/auth/me')
  },

  refreshToken() {
    return client.post('/auth/refresh')
  },

  logout() {
    return client.post('/auth/logout')
  },

  // Profile Completion - Issue #4
  getProfileCompletion() {
    return client.get('/auth/profile-completion')
  },

  // Password Reset - Issue #7
  forgotPassword(payload) {
    return client.post('/auth/forgot-password', payload)
  },

  resetPassword(payload) {
    return client.post('/auth/reset-password', payload)
  },

  // OTP Verification - WhatsApp Password Reset
  verifyOtp(payload) {
    return client.post('/auth/verify-otp', payload)
  },

  resendOtp(payload) {
    return client.post('/auth/resend-otp', payload)
  },

  // Session Management - Issue #6
  getSessions() {
    return client.get('/sessions')
  },

  logoutSession(sessionId) {
    return client.post(`/sessions/${sessionId}/logout`)
  },

  logoutAll() {
    return client.post('/auth/logout-all')
  },

  // Consent Management - Issue #3
  getConsentStatus() {
    return client.get('/auth/consent-status')
  },

  acceptConsent(consentType) {
    return client.post('/auth/accept-consent', { consent_type: consentType })
  }
}