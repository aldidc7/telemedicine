<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-gray-50 to-green-100 py-12 px-4">
    <div class="max-w-3xl w-full bg-white rounded-xl shadow-lg p-8">
      <h1 class="text-2xl font-bold text-green-700 mb-2">Verifikasi Dokter - Admin</h1>
      <p class="text-gray-700 mb-4">
        Berikut adalah daftar dokter yang menunggu verifikasi. Silakan cek data dan dokumen yang diunggah, lalu setujui atau tolak pendaftaran dokter.
      </p>
      <table class="w-full table-auto border mb-6">
        <thead>
          <tr class="bg-green-50">
            <th class="px-4 py-2 text-left">Nama</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Nomor SIP</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="dokter in daftarDokter" :key="dokter.id" class="border-b">
            <td class="px-4 py-2">{{ dokter.name }}</td>
            <td class="px-4 py-2">{{ dokter.email }}</td>
            <td class="px-4 py-2">{{ dokter.sip }}</td>
            <td class="px-4 py-2">
              <span v-if="dokter.status === 'pending'" class="text-yellow-700">Menunggu</span>
              <span v-else-if="dokter.status === 'verified'" class="text-green-700">Terverifikasi</span>
              <span v-else class="text-red-700">Ditolak</span>
            </td>
            <td class="px-4 py-2">
              <button v-if="dokter.status === 'pending'" @click="verifikasi(dokter, true)" class="bg-green-600 text-white px-3 py-1 rounded mr-2 hover:bg-green-700">Setujui</button>
              <button v-if="dokter.status === 'pending'" @click="verifikasi(dokter, false)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Tolak</button>
              <span v-if="dokter.status !== 'pending'">-</span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="daftarDokter.length === 0" class="text-center text-gray-500 py-8">Tidak ada dokter yang menunggu verifikasi.</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import { onMounted } from 'vue'

const daftarDokter = ref([])
const loading = ref(false)
const error = ref('')

async function fetchPendingDoctors() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch('/api/v1/admin/verification/pending', {
      headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (data.success) {
      daftarDokter.value = (data.data || []).map(d => ({
        id: d.id,
        name: d.name,
        email: d.email,
        sip: d.license_number,
        status: 'pending',
      }))
    } else {
      error.value = data.pesan || 'Gagal mengambil data dokter.'
    }
  } catch (e) {
    error.value = 'Gagal mengambil data dokter.'
  } finally {
    loading.value = false
  }
}

async function verifikasi(dokter, setujui) {
  const url = `/api/v1/admin/verification/${dokter.id}/${setujui ? 'approve' : 'reject'}`
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (data.success) {
      dokter.status = setujui ? 'verified' : 'rejected'
    } else {
      alert(data.pesan || 'Gagal memproses verifikasi.')
    }
  } catch (e) {
    alert('Gagal memproses verifikasi.')
  }
}

onMounted(fetchPendingDoctors)
</script>
