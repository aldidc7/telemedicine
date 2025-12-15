<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-10">
        <div class="flex items-center gap-3 mb-2">
          <svg class="w-11 h-11 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
          </svg>
          <h1 class="text-4xl font-black bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Diagnostic Center
          </h1>
        </div>
        <p class="text-gray-600">Pemantauan status sistem dan debug information</p>
      </div>

      <!-- Status Overview KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Auth Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Authentication Status</p>
              <p class="text-4xl font-bold text-gray-900 mt-2">{{ authStore.isAuthenticated ? '✓' : '✗' }}</p>
              <p class="text-xs text-gray-500 mt-2">{{ authStore.isAuthenticated ? 'Logged In' : 'Not Logged In' }}</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5s-5 2.24-5 5v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Router Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Navigation Route</p>
              <p class="text-2xl font-bold text-gray-900 mt-2 truncate">{{ $route.path }}</p>
              <p class="text-xs text-gray-500 mt-2">{{ $route.name || 'Undefined' }}</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-emerald-100 to-emerald-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6-6-6z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- User Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Current User</p>
              <p class="text-2xl font-bold text-gray-900 mt-2 truncate">{{ authStore.user?.name || 'Anonymous' }}</p>
              <p class="text-xs text-gray-500 mt-2">{{ authStore.user?.role || 'Guest' }}</p>
            </div>
            <div class="w-16 h-16 bg-linear-to-br from-purple-100 to-purple-50 rounded-2xl flex items-center justify-center">
              <svg class="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Info Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <!-- Auth Store Details -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
          <div class="flex items-center gap-3 mb-6">
            <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zm5.6 8H19v6h-2.8z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900">Auth Store Data</h2>
          </div>
          <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Initialized</span>
                <span class="font-mono font-bold text-indigo-600">{{ authStore.initialized }}</span>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Authenticated</span>
                <span class="font-mono font-bold text-indigo-600">{{ authStore.isAuthenticated }}</span>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Token</span>
                <span class="font-mono font-bold text-indigo-600">{{ authStore.token ? '✓ Present' : '✗ Missing' }}</span>
              </div>
            </div>
            <div v-if="authStore.user" class="bg-indigo-50 p-4 rounded-xl border border-indigo-200 mt-4">
              <h3 class="font-bold text-indigo-900 mb-3">User Information</h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-indigo-700">ID</span>
                  <span class="font-mono text-indigo-900">{{ authStore.user.id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-indigo-700">Email</span>
                  <span class="font-mono text-indigo-900 truncate">{{ authStore.user.email }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-indigo-700">Role</span>
                  <span class="font-mono text-indigo-900">{{ authStore.user.role }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Router Details -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-lg transition">
          <div class="flex items-center gap-3 mb-6">
            <svg class="w-7 h-7 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900">Router State</h2>
          </div>
          <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Path</span>
                <span class="font-mono font-bold text-emerald-600">{{ $route.path }}</span>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Route Name</span>
                <span class="font-mono font-bold text-emerald-600">{{ $route.name || 'undefined' }}</span>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 font-medium">Matched Routes</span>
                <span class="font-mono font-bold text-emerald-600">{{ $route.matched.length }}</span>
              </div>
            </div>
            <div v-if="$route.params && Object.keys($route.params).length" class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 mt-4">
              <h3 class="font-bold text-emerald-900 mb-3">Route Parameters</h3>
              <div class="space-y-2 text-sm">
                <div v-for="(val, key) in $route.params" :key="key" class="flex justify-between">
                  <span class="text-emerald-700">{{ key }}</span>
                  <span class="font-mono text-emerald-900">{{ val }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-10 hover:shadow-lg transition">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          <h2 class="text-2xl font-bold text-gray-900">Quick Actions</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <button @click="goHome" class="bg-linear-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:shadow-lg border border-blue-400 flex items-center justify-center gap-3">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8h5z"/>
            </svg>
            <span>Go Home</span>
          </button>
          <button @click="goLogin" class="bg-linear-to-br from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:shadow-lg border border-emerald-400 flex items-center justify-center gap-3">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18 8h-1V6c0-2.76-2.24-5-5-5s-5 2.24-5 5v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
            </svg>
            <span>Login</span>
          </button>
          <button @click="copySystemInfo" class="bg-linear-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:shadow-lg border border-purple-400 flex items-center justify-center gap-3">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
            </svg>
            <span>Copy Info</span>
          </button>
          <button @click="clearStorage" class="bg-linear-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:shadow-lg border border-red-400 flex items-center justify-center gap-3">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-9l-1 1H5v2h14V4z"/>
            </svg>
            <span>Clear Cache</span>
          </button>
        </div>
      </div>

      <!-- Debugging Instructions -->
      <div class="bg-linear-to-br from-amber-50 to-orange-50 rounded-2xl shadow-sm border border-amber-200 p-8 hover:shadow-lg transition">
        <div class="flex items-start gap-4">
          <svg class="w-8 h-8 text-amber-600 shrink-0 mt-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
          </svg>
          <div class="grow">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Debugging Instructions</h3>
            <ol class="space-y-3 text-gray-700">
              <li class="flex gap-3">
                <span class="font-bold text-amber-600 shrink-0 w-6">1.</span>
                <span>Press <kbd class="bg-gray-900 text-white px-2 py-1 rounded text-sm font-mono">F12</kbd> to open Browser DevTools</span>
              </li>
              <li class="flex gap-3">
                <span class="font-bold text-amber-600 shrink-0 w-6">2.</span>
                <span>Go to <strong>Console</strong> tab to see any error messages</span>
              </li>
              <li class="flex gap-3">
                <span class="font-bold text-amber-600 shrink-0 w-6">3.</span>
                <span>Check <strong>Network</strong> tab for failed API requests</span>
              </li>
              <li class="flex gap-3">
                <span class="font-bold text-amber-600 shrink-0 w-6">4.</span>
                <span>Use <strong>Application → LocalStorage</strong> to inspect cached data</span>
              </li>
              <li class="flex gap-3">
                <span class="font-bold text-amber-600 shrink-0 w-6">5.</span>
                <span>Click <strong>Copy Info</strong> button to export diagnostic data</span>
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Copy Notification -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition duration-300 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
      <div v-if="copied" class="fixed bottom-6 right-6 bg-emerald-500 text-white px-6 py-4 rounded-2xl shadow-lg border border-emerald-400 font-semibold flex items-center gap-3">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
        </svg>
        System info copied to clipboard!
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const copied = ref(false)

const goHome = () => router.push('/')
const goLogin = () => router.push('/login')

const clearStorage = () => {
  localStorage.clear()
  sessionStorage.clear()
  location.reload()
}

const copySystemInfo = () => {
  const info = {
    auth: {
      initialized: authStore.initialized,
      authenticated: authStore.isAuthenticated,
      token: authStore.token ? 'Present' : 'Missing',
      user: authStore.user ? {
        id: authStore.user.id,
        name: authStore.user.name,
        email: authStore.user.email,
        role: authStore.user.role
      } : null
    },
    router: {
      path: router.currentRoute.value.path,
      name: router.currentRoute.value.name,
      matched: router.currentRoute.value.matched.length
    },
    environment: {
      apiUrl: import.meta.env.VITE_API_URL,
      isDev: import.meta.env.DEV,
      isProd: import.meta.env.PROD
    },
    userAgent: navigator.userAgent,
    timestamp: new Date().toISOString()
  }
  
  navigator.clipboard.writeText(JSON.stringify(info, null, 2))
  copied.value = true
  
  setTimeout(() => {
    copied.value = false
  }, 3000)
}
</script>
