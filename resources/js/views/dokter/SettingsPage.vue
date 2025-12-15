<!-- 📁 resources/js/views/dokter/SettingsPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Back Button -->
      <router-link to="/dokter/profile" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-bold mb-8 transition group">
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
            <h1 class="text-4xl font-black text-gray-900">Pengaturan Praktik</h1>
            <p class="text-gray-600 mt-2">Kelola jam praktik dan preferensi konsultasi</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-8 border-b border-gray-200 sticky top-16 bg-white bg-opacity-95 backdrop-blur-sm -mx-4 sm:mx-0 px-4 sm:px-0 py-4 sm:py-2 z-20">
        <button
          @click="activeTab = 'hours'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'hours' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Jam Praktik
        </button>
        <button
          @click="activeTab = 'rates'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'rates' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Tarif Konsultasi
        </button>
        <button
          @click="activeTab = 'notifications'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'notifications' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          Notifikasi
        </button>
      </div>

      <!-- Jam Praktik Tab -->
      <div v-if="activeTab === 'hours'" class="space-y-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6">⏰ Jam Praktik</h3>

          <div class="space-y-4">
            <div v-for="(day, index) in workDays" :key="index" class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div class="w-32">
                <p class="font-semibold text-gray-900">{{ day.label }}</p>
              </div>
              <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="day.active" class="w-4 h-4 accent-indigo-600"/>
                  <span class="text-sm font-medium text-gray-700">Aktif</span>
                </label>
                <input v-if="day.active" type="time" v-model="day.start" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-indigo-500"/>
                <span v-if="day.active" class="text-gray-500">-</span>
                <input v-if="day.active" type="time" v-model="day.end" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-indigo-500"/>
              </div>
            </div>
          </div>
        </div>

        <button
          @click="saveWorkHours"
          class="w-full px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
        >
          Simpan Jam Praktik
        </button>
      </div>

      <!-- Tarif Tab -->
      <div v-else-if="activeTab === 'rates'" class="space-y-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6">💰 Tarif Konsultasi</h3>

          <div class="space-y-6">
            <div v-for="type in consultationTypes" :key="type" class="p-6 bg-gray-50 rounded-2xl">
              <div class="flex items-center justify-between mb-4">
                <p class="font-bold text-gray-900 capitalize">{{ type === 'chat' ? '💬 Chat Text' : type === 'voice' ? '📞 Voice Call' : '📹 Video Call' }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-gray-700 font-semibold">Rp</span>
                <input
                  v-model.number="rates[type]"
                  type="number"
                  placeholder="0"
                  class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-0 transition text-lg font-bold"
                />
                <span class="text-gray-600 font-medium">/sesi</span>
              </div>
            </div>
          </div>
        </div>

        <button
          @click="saveRates"
          class="w-full px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
        >
          Simpan Tarif
        </button>
      </div>

      <!-- Notifikasi Tab -->
      <div v-else-if="activeTab === 'notifications'" class="space-y-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6">🔔 Preferensi Notifikasi</h3>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Notifikasi Konsultasi Baru</p>
                <p class="text-sm text-gray-600 mt-1">Alert saat ada permintaan konsultasi masuk</p>
              </div>
              <input type="checkbox" v-model="notifications.newConsultation" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Email Notifikasi</p>
                <p class="text-sm text-gray-600 mt-1">Terima email untuk update penting</p>
              </div>
              <input type="checkbox" v-model="notifications.emailNotifications" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Push Notification</p>
                <p class="text-sm text-gray-600 mt-1">Aktifkan notifikasi push di browser</p>
              </div>
              <input type="checkbox" v-model="notifications.pushNotifications" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">SMS Alert</p>
                <p class="text-sm text-gray-600 mt-1">Alert SMS untuk konsultasi mendesak</p>
              </div>
              <input type="checkbox" v-model="notifications.smsAlert" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>
          </div>
        </div>

        <button
          @click="saveNotifications"
          class="w-full px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
        >
          Simpan Preferensi Notifikasi
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('hours')

const workDays = ref([
  { label: 'Senin', active: true, start: '09:00', end: '17:00' },
  { label: 'Selasa', active: true, start: '09:00', end: '17:00' },
  { label: 'Rabu', active: true, start: '09:00', end: '17:00' },
  { label: 'Kamis', active: true, start: '09:00', end: '17:00' },
  { label: 'Jumat', active: true, start: '09:00', end: '17:00' },
  { label: 'Sabtu', active: false, start: '09:00', end: '12:00' },
  { label: 'Minggu', active: false, start: '00:00', end: '00:00' }
])

const consultationTypes = ref(['chat', 'voice', 'video'])

const rates = ref({
  chat: 50000,
  voice: 100000,
  video: 150000
})

const notifications = ref({
  newConsultation: true,
  emailNotifications: true,
  pushNotifications: true,
  smsAlert: false
})

const saveWorkHours = async () => {
  // TODO: Save to API
  alert('Jam praktik disimpan!')
}

const saveRates = async () => {
  // TODO: Save to API
  alert('Tarif disimpan!')
}

const saveNotifications = async () => {
  // TODO: Save to API
  alert('Preferensi notifikasi disimpan!')
}
</script>
