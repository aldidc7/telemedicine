<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-lg w-full bg-white rounded-xl shadow-lg p-8">
      <h1 class="text-2xl font-bold text-indigo-700 mb-2">Verifikasi Data Dokter</h1>
      <p class="text-gray-700 mb-4">
        Untuk menjaga keamanan dan kepercayaan pasien, Anda wajib melengkapi dan mengunggah data serta dokumen verifikasi (SIP, STR, KTP, dsb) sebelum dapat menggunakan fitur konsultasi.
      </p>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-blue-800 text-sm">
          <strong>Kenapa harus verifikasi?</strong><br>
          Data dan dokumen Anda akan diverifikasi oleh admin untuk memastikan hanya dokter asli yang dapat memberikan layanan konsultasi. Ini penting untuk melindungi pasien dari dokter gadungan dan menjaga kualitas layanan.
        </p>
      </div>
      <!-- Form Lengkapi Data (contoh sederhana) -->
      <form @submit.prevent="submitVerification" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nomor SIP</label>
          <input v-model="form.sip" type="text" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Masukkan nomor SIP" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nomor STR</label>
          <input v-model="form.str" type="text" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Masukkan nomor STR" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Upload KTP</label>
          <input type="file" @change="handleFileUpload($event, 'ktp')" accept="image/*,application/pdf" class="w-full" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Upload Ijazah</label>
          <input type="file" @change="handleFileUpload($event, 'ijazah')" accept="image/*,application/pdf" class="w-full" />
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700 transition">Kirim Data Verifikasi</button>
      </form>
      <div v-if="status === 'pending'" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
        Data Anda sedang menunggu verifikasi admin. Anda akan mendapat notifikasi setelah diverifikasi.
      </div>
      <div v-if="status === 'verified'" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
        Data Anda sudah diverifikasi. Anda sudah bisa menerima pasien.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import { onMounted } from 'vue'

const form = ref({
  sip: '',
  str: '',
  ktp: null,
  ijazah: null,
})
const status = ref('') // 'pending', 'verified', ''
const loading = ref(false)
const error = ref('')

async function fetchVerificationStatus() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch('/api/v1/doctor/verification/status', {
      headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (data.success) {
      if (data.data.is_fully_verified || data.data.overall_status === 'approved') {
        status.value = 'verified'
      } else if (data.data.overall_status === 'pending') {
        status.value = 'pending'
      } else {
        status.value = ''
      }
    } else {
      error.value = data.pesan || 'Gagal mengambil status verifikasi.'
    }
  } catch (e) {
    error.value = 'Gagal mengambil status verifikasi.'
  } finally {
    loading.value = false
  }
}

function handleFileUpload(event, type) {
  const file = event.target.files[0]
  if (type === 'ktp') form.value.ktp = file
  if (type === 'ijazah') form.value.ijazah = file
}

async function uploadDocument(type, file) {
  const formData = new FormData()
  formData.append('document_type', type)
  formData.append('file', file)
  try {
    const res = await fetch('/api/v1/doctor/verification/upload', {
      method: 'POST',
      body: formData
    })
    const data = await res.json()
    if (!data.success) {
      alert(data.pesan || 'Gagal upload dokumen ' + type)
    }
  } catch (e) {
    alert('Gagal upload dokumen ' + type)
  }
}

async function submitVerification() {
  loading.value = true
  error.value = ''
  // Upload KTP
  if (form.value.ktp) await uploadDocument('ktp', form.value.ktp)
  // Upload Ijazah
  if (form.value.ijazah) await uploadDocument('ijazah', form.value.ijazah)
  // Simpan SIP/STR ke profile (opsional, jika ada endpoint)
  // Setelah upload, refresh status
  await fetchVerificationStatus()
  loading.value = false
}

onMounted(fetchVerificationStatus)
</script>
