<!-- 📁 resources/js/views/admin/SettingsPage.vue -->
<template>
  <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Back Button -->
      <router-link to="/admin/dashboard" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-bold mb-8 transition group">
        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Dashboard</span>
      </router-link>

      <!-- Header -->
      <div class="mb-8">
        <div class="inline-flex items-center gap-4 mb-6">
          <div class="w-14 h-14 bg-linear-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div>
            <h1 class="text-4xl font-black text-gray-900">Pengaturan Admin</h1>
            <p class="text-gray-600 mt-2">Kelola preferensi sistem dan keamanan</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-8 border-b border-gray-200 sticky top-16 bg-white bg-opacity-95 backdrop-blur-sm -mx-4 sm:mx-0 px-4 sm:px-0 py-4 sm:py-2 z-20">
        <button
          @click="activeTab = 'notifications'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'notifications' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          Notifikasi
        </button>
        <button
          @click="activeTab = 'security'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'security' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          Keamanan
        </button>
        <button
          @click="activeTab = 'system'"
          :class="['px-4 py-2.5 rounded-lg font-semibold transition text-sm flex items-center gap-2',
            activeTab === 'system' ? 'bg-indigo-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100']"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Sistem
        </button>
      </div>

      <!-- Notifikasi Tab -->
      <div v-if="activeTab === 'notifications'" class="space-y-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6">🔔 Preferensi Notifikasi</h3>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Email Notifikasi Harian</p>
                <p class="text-sm text-gray-600 mt-1">Laporan aktivitas sistem setiap hari</p>
              </div>
              <input type="checkbox" v-model="notifications.dailyReport" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Alert Error Sistem</p>
                <p class="text-sm text-gray-600 mt-1">Notifikasi saat terjadi error di sistem</p>
              </div>
              <input type="checkbox" v-model="notifications.errorAlerts" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Alert User Baru</p>
                <p class="text-sm text-gray-600 mt-1">Notifikasi saat ada registrasi user baru</p>
              </div>
              <input type="checkbox" v-model="notifications.newUserAlert" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Alert Pembayaran</p>
                <p class="text-sm text-gray-600 mt-1">Notifikasi transaksi pembayaran penting</p>
              </div>
              <input type="checkbox" v-model="notifications.paymentAlert" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
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

      <!-- Keamanan Tab -->
      <div v-else-if="activeTab === 'security'" class="space-y-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6">🔐 Pengaturan Keamanan</h3>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Two-Factor Authentication</p>
                <p class="text-sm text-gray-600 mt-1">Aktifkan verifikasi dua langkah untuk akun admin</p>
              </div>
              <input type="checkbox" v-model="security.twoFactor" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Login Notifications</p>
                <p class="text-sm text-gray-600 mt-1">Notifikasi email saat ada login baru</p>
              </div>
              <input type="checkbox" v-model="security.loginNotifications" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">IP Whitelist</p>
                <p class="text-sm text-gray-600 mt-1">Hanya izinkan login dari IP tertentu</p>
              </div>
              <input type="checkbox" v-model="security.ipWhitelist" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
              <div>
                <p class="font-semibold text-gray-900">Audit Log</p>
                <p class="text-sm text-gray-600 mt-1">Catat semua aktivitas admin di sistem</p>
              </div>
              <input type="checkbox" v-model="security.auditLog" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
            </div>
          </div>
        </div>

        <button
          @click="saveSecurity"
          class="w-full px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
        >
          Simpan Pengaturan Keamanan
        </button>
      </div>

      <!-- Sistem Tab -->
      <div v-else-if="activeTab === 'system'" class="space-y-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-black text-gray-900 mb-6">⚙️ Pengaturan Sistem</h3>

          <div class="space-y-6">
            <div class="p-4 bg-gray-50 rounded-2xl">
              <label class="block">
                <p class="font-semibold text-gray-900 mb-2">Maintenance Mode</p>
                <p class="text-sm text-gray-600 mb-4">Aktifkan mode maintenance untuk melakukan update sistem</p>
                <input type="checkbox" v-model="system.maintenanceMode" class="w-5 h-5 accent-indigo-600 cursor-pointer"/>
              </label>
            </div>

            <div class="p-4 bg-gray-50 rounded-2xl">
              <label class="block">
                <p class="font-semibold text-gray-900 mb-2">API Rate Limiting</p>
                <p class="text-sm text-gray-600 mb-4">Batasi jumlah request per menit (masukkan angka)</p>
                <input
                  v-model.number="system.rateLimit"
                  type="number"
                  min="10"
                  max="10000"
                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-0 transition"
                  placeholder="1000"
                />
              </label>
            </div>

            <div class="p-4 bg-gray-50 rounded-2xl">
              <label class="block">
                <p class="font-semibold text-gray-900 mb-2">Log Retention (hari)</p>
                <p class="text-sm text-gray-600 mb-4">Berapa hari log disimpan di sistem</p>
                <input
                  v-model.number="system.logRetention"
                  type="number"
                  min="1"
                  max="365"
                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-0 transition"
                  placeholder="30"
                />
              </label>
            </div>

            <div class="p-4 bg-gray-50 rounded-2xl">
              <label class="block">
                <p class="font-semibold text-gray-900 mb-2">Cache Duration (menit)</p>
                <p class="text-sm text-gray-600 mb-4">Durasi caching untuk performa optimal</p>
                <input
                  v-model.number="system.cacheDuration"
                  type="number"
                  min="1"
                  max="1440"
                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-0 transition"
                  placeholder="60"
                />
              </label>
            </div>
          </div>
        </div>

        <button
          @click="saveSystem"
          class="w-full px-6 py-3 bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition shadow-lg hover:shadow-xl"
        >
          Simpan Pengaturan Sistem
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('notifications')

const notifications = ref({
  dailyReport: true,
  errorAlerts: true,
  newUserAlert: true,
  paymentAlert: true
})

const security = ref({
  twoFactor: false,
  loginNotifications: true,
  ipWhitelist: false,
  auditLog: true
})

const system = ref({
  maintenanceMode: false,
  rateLimit: 1000,
  logRetention: 30,
  cacheDuration: 60
})

const saveNotifications = async () => {
  // TODO: Save to API
  alert('Preferensi notifikasi disimpan!')
}

const saveSecurity = async () => {
  // TODO: Save to API
  alert('Pengaturan keamanan disimpan!')
}

const saveSystem = async () => {
  // TODO: Save to API
  alert('Pengaturan sistem disimpan!')
}
</script>
