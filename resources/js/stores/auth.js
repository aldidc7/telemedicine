import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const isLoading = ref(false)
  const error = ref(null)
  const initialized = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRole = computed(() => user.value?.role)
  const isPasien = computed(() => userRole.value === 'pasien')
  const isDokter = computed(() => userRole.value === 'dokter')
  const isAdmin = computed(() => userRole.value === 'admin')

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
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan || 'Login failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getProfile = async () => {
    try {
      const { data } = await authApi.getProfile()
      user.value = data.data
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
          }, 8000) // 8 second timeout untuk getProfile
        })
        
        const { data } = await Promise.race([authApi.getProfile(), timeoutPromise])
        user.value = data.data
        return true
      }
      return false
    } catch (err) {
      // Token invalid atau timeout, clear localStorage dan continue
      console.warn('Auth initialization error:', err.message)
      token.value = null
      user.value = null
      localStorage.removeItem('token')
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
    isAuthenticated,
    userRole,
    isPasien,
    isDokter,
    isAdmin,
    register,
    login,
    getProfile,
    logout,
    initializeAuth
  }
})