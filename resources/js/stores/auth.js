import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const isLoading = ref(false)
  const error = ref(null)
  const initialized = ref(false)
  const consentRequired = ref(false)
  const acceptedConsents = ref([])

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRole = computed(() => user.value?.role)
  const isPasien = computed(() => userRole.value === 'pasien')
  const isDokter = computed(() => userRole.value === 'dokter')
  const isAdmin = computed(() => userRole.value === 'admin')
  const hasConsent = computed(() => acceptedConsents.value.length === 3) // All 3 consents

  const register = async (name, email, password, passwordConfirmation, role, noTelepon, alamat, tglLahir = null, nik = null, jenisKelamin = null, golonganDarah = null, namaKontakDarurat = null, noKontakDarurat = null) => {
    isLoading.value = true
    error.value = null
    try {
      const formData = {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        role,
        no_telepon: noTelepon,
        alamat
      }

      // Add required fields for pasien
      if (role === 'pasien') {
        if (nik) formData.nik = nik
        if (tglLahir) formData.tgl_lahir = tglLahir
        if (jenisKelamin) formData.jenis_kelamin = jenisKelamin
      }

      // Add optional fields
      // Add optional fields
      // Spesialisasi akan diisi di profil edit dokter
      if (golonganDarah) formData.golongan_darah = golonganDarah
      if (namaKontakDarurat) formData.nama_kontak_darurat = namaKontakDarurat
      if (noKontakDarurat) formData.no_kontak_darurat = noKontakDarurat

      const { data } = await authApi.register(formData)
      token.value = data.data.token
      user.value = data.data.user
      localStorage.setItem('token', data.data.token)
      
      // Check consent status
      await checkConsentStatus()
      
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan || err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const login = async (identifier, password) => {
    isLoading.value = true
    error.value = null
    try {
      const { data } = await authApi.login(identifier, password)
      token.value = data.data.token
      user.value = data.data.user
      localStorage.setItem('token', data.data.token)
      
      // Check consent status
      await checkConsentStatus()
      
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan || err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const checkConsentStatus = async () => {
    try {
      const { data } = await authApi.getConsentStatus()
      acceptedConsents.value = data.data.accepted_consents || []
      consentRequired.value = data.data.requires_consent || false
    } catch (err) {
      console.error('Check consent status error:', err)
      // If not admin, require consent
      if (user.value?.role !== 'admin') {
        consentRequired.value = true
      }
    }
  }

  const getProfile = async () => {
    try {
      const { data } = await authApi.getProfile()
      user.value = data.data
      await checkConsentStatus()
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    }
  }

  const logout = async () => {
    try {
      await authApi.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      user.value = null
      token.value = null
      consentRequired.value = false
      acceptedConsents.value = []
      localStorage.removeItem('token')
    }
  }

  const initializeAuth = async () => {
    initialized.value = false
    try {
      // Jika ada token di localStorage, coba load user data dengan timeout
      if (token.value) {
        const timeoutPromise = new Promise((resolve, reject) => {
          setTimeout(() => {
            reject(new Error('Profile fetch timeout'))
          }, 10000) // 10 second timeout untuk getProfile
        })
        
        try {
          const { data } = await Promise.race([authApi.getProfile(), timeoutPromise])
          user.value = data.data
          await checkConsentStatus()
          return true
        } catch (err) {
          // Token invalid atau timeout, clear dan continue silently
          token.value = null
          user.value = null
          localStorage.removeItem('token')
          return false
        }
      }
      return false
    } finally {
      initialized.value = true
    }
  }

  return {
    user,
    token,
    isLoading,
    error,
    initialized,
    consentRequired,
    acceptedConsents,
    hasConsent,
    isAuthenticated,
    userRole,
    isPasien,
    isDokter,
    isAdmin,
    register,
    login,
    getProfile,
    logout,
    initializeAuth,
    checkConsentStatus
  }
})