import { defineStore } from 'pinia'
import { ref } from 'vue'
import { konsultasiApi } from '@/api/konsultasi'

export const useKonsultasiStore = defineStore('konsultasi', () => {
  const konsultasiList = ref([])
  const selectedKonsultasi = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  const getList = async (params = {}) => {
    isLoading.value = true
    error.value = null
    try {
      const { data } = await konsultasiApi.getList(params)
      konsultasiList.value = data.data
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (formData) => {
    isLoading.value = true
    error.value = null
    try {
      const { data } = await konsultasiApi.create(formData)
      konsultasiList.value.push(data.data)
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const getDetail = async (id) => {
    isLoading.value = true
    error.value = null
    try {
      const { data } = await konsultasiApi.getDetail(id)
      selectedKonsultasi.value = data.data
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const terima = async (id) => {
    try {
      const { data } = await konsultasiApi.terima(id)
      if (selectedKonsultasi.value?.id === id) {
        selectedKonsultasi.value = data.data
      }
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    }
  }

  const tolak = async (id, formData = {}) => {
    try {
      const { data } = await konsultasiApi.tolak(id, formData)
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    }
  }

  const selesaikan = async (id, formData = {}) => {
    try {
      const { data } = await konsultasiApi.selesaikan(id, formData)
      if (selectedKonsultasi.value?.id === id) {
        selectedKonsultasi.value = data.data
      }
      return data
    } catch (err) {
      error.value = err.response?.data?.pesan
      throw err
    }
  }

  return {
    konsultasiList,
    selectedKonsultasi,
    isLoading,
    error,
    getList,
    create,
    getDetail,
    terima,
    tolak,
    selesaikan
  }
})