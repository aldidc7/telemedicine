<!-- [SETTINGS] resources/js/views/pasien/SettingsPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Back Button -->
      <router-link to="/profile" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-bold mb-8 transition group">
        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Profil</span>
      </router-link>

      <!-- Header -->
      <div class="mb-8">
        <div class="inline-flex items-center gap-4 mb-6">
          <div class="w-14 h-14 bg-linear-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div>
            <h1 class="text-4xl font-black text-gray-900">Pengaturan</h1>
            <p class="text-gray-600 mt-2">Kelola preferensi dan informasi akun Anda</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-8 border-b border-gray-200 sticky top-16 bg-white bg-opacity-95 backdrop-blur-sm -mx-4 sm:mx-0 px-4 sm:px-0 py-4 sm:py-2 z-20">
        <button
          @click="activeTab = 'general'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'general' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Umum
        </button>
        <button
          @click="activeTab = 'medical'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'medical' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          Informasi Medis
        </button>
      </div>

      <!-- General Settings Tab -->
      <div v-if="activeTab === 'general'" class="space-y-6">
        <!-- Notifications Section -->
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            Notifikasi
          </h3>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Email Notifikasi Konsultasi</p>
                <p class="text-sm text-gray-600 mt-1">Terima email saat ada permintaan konsultasi baru</p>
              </div>
              <div class="relative">
                <input type="checkbox" v-model="settings.emailNotifications" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
              </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Push Notification</p>
                <p class="text-sm text-gray-600 mt-1">Aktifkan notifikasi push di browser</p>
              </div>
              <input type="checkbox" v-model="settings.pushNotifications" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">SMS Reminder</p>
                <p class="text-sm text-gray-600 mt-1">Pengingat konsultasi via SMS</p>
              </div>
              <input type="checkbox" v-model="settings.smsReminder" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>
          </div>
        </div>

        <!-- Privacy Section -->
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            Privasi & Keamanan
          </h3>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Profil Publik</p>
                <p class="text-sm text-gray-600 mt-1">Izinkan dokter melihat profil Anda</p>
              </div>
              <input type="checkbox" v-model="settings.publicProfile" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Riwayat Tersimpan</p>
                <p class="text-sm text-gray-600 mt-1">Simpan riwayat konsultasi</p>
              </div>
              <input type="checkbox" v-model="settings.saveHistory" class="w-5 h-5 accent-indigo-600 cursor-pointer" disabled/>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex gap-4">
          <button
            @click="saveSettings"
            class="flex-1 px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
          >
            Simpan Perubahan
          </button>
        </div>
      </div>

      <!-- Medical Info Tab -->
      <div v-else-if="activeTab === 'medical'" class="space-y-6">
        <!-- Kondisi Medis -->
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            Kondisi Medis
          </h3>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Kondisi Medis yang Relevan</label>
              <textarea
                v-model="medicalInfo.conditions"
                rows="4"
                placeholder="Contoh: Diabetes, Hipertensi, Asma..."
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition"
              ></textarea>
              <p class="text-xs text-gray-500 mt-2">Informasi ini membantu dokter memahami riwayat medis Anda</p>
            </div>
          </div>
        </div>

        <!-- Alergi -->
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 5v.01M12 3a9 9 0 110 18 9 9 0 010-18z"/></svg>
            </div>
            Alergi & Obat
          </h3>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Alergi Obat</label>
              <textarea
                v-model="medicalInfo.allergies"
                rows="3"
                placeholder="Contoh: Penisilin, Aspirin..."
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Obat yang Sedang Dikonsumsi</label>
              <textarea
                v-model="medicalInfo.medications"
                rows="3"
                placeholder="Contoh: Metformin 500mg, Lisinopril 10mg..."
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex gap-4">
          <button
            @click="saveMedicalInfo"
            class="flex-1 px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
          >
            Simpan Informasi Medis
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('general')

const settings = ref({
  emailNotifications: true,
  pushNotifications: true,
  smsReminder: false,
  publicProfile: true,
  saveHistory: true
})

const medicalInfo = ref({
  conditions: '',
  allergies: '',
  medications: ''
})

const saveSettings = async () => {
  // TODO: Save to API
  alert('Pengaturan disimpan!')
}

const saveMedicalInfo = async () => {
  // TODO: Save to API
  alert('Informasi medis disimpan!')
}
</script>
