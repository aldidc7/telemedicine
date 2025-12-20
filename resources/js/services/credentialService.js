import axios from 'axios'

const API_BASE = '/api/v1'

const credentialService = {
  /**
   * Submit credentials for verification (doctor only)
   */
  submitCredentials(formData) {
    return axios.post(`${API_BASE}/doctors/credentials/submit`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  /**
   * Get doctor's credentials status
   */
  getCredentialsStatus() {
    return axios.get(`${API_BASE}/doctors/credentials/status`)
  },

  /**
   * Get doctor verification status (public)
   */
  getDoctorVerificationStatus(doctorId) {
    return axios.get(`${API_BASE}/doctors/${doctorId}/verification`)
  },

  /**
   * Admin: Get pending verifications
   */
  getPendingVerifications(params = {}) {
    return axios.get(`${API_BASE}/admin/verifications/pending`, { params })
  },

  /**
   * Admin: Get verification detail
   */
  getVerificationDetail(verificationId) {
    return axios.get(`${API_BASE}/admin/verifications/${verificationId}`)
  },

  /**
   * Admin: Verify credentials
   */
  verifyCredentials(verificationId, data) {
    return axios.post(`${API_BASE}/admin/verifications/${verificationId}/verify`, data)
  },

  /**
   * Admin: Reject credential
   */
  rejectCredential(verificationId, data) {
    return axios.post(`${API_BASE}/admin/verifications/${verificationId}/reject`, data)
  },

  /**
   * Admin: Approve full verification
   */
  approveVerification(verificationId, data) {
    return axios.post(`${API_BASE}/admin/verifications/${verificationId}/approve`, data)
  },
}

export default credentialService
