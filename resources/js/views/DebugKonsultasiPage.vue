<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">🐛 Debug Konsultasi</h1>
        <p class="text-gray-600">Halaman untuk debug masalah konsultasi yang tidak muncul</p>
      </div>

      <!-- Status Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold text-sm text-gray-600 mb-2">👤 AUTH USER</h3>
          <div class="space-y-1 text-sm">
            <p><strong>Name:</strong> {{ authUser?.name }}</p>
            <p><strong>Email:</strong> {{ authUser?.email }}</p>
            <p><strong>Role:</strong> <span class="font-mono bg-blue-100 px-2 py-1 rounded">{{ authUser?.role }}</span></p>
          </div>
        </div>

        <div v-if="dokterInfo" class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold text-sm text-gray-600 mb-2">👨‍⚕️ DOKTER PROFILE</h3>
          <div class="space-y-1 text-sm">
            <p><strong>Dokter ID:</strong> <span class="font-mono">{{ dokterInfo.id }}</span></p>
            <p><strong>Specialization:</strong> {{ dokterInfo.specialization || '-' }}</p>
            <p><strong>Available:</strong> {{ dokterInfo.is_available ? '✅ Yes' : '❌ No' }}</p>
          </div>
        </div>

        <div v-if="pasienInfo" class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold text-sm text-gray-600 mb-2">👤 PASIEN PROFILE</h3>
          <div class="space-y-1 text-sm">
            <p><strong>Pasien ID:</strong> <span class="font-mono">{{ pasienInfo.id }}</span></p>
            <p><strong>NIK:</strong> {{ pasienInfo.nik }}</p>
          </div>
        </div>
      </div>

      <!-- API Test Section -->
      <div class="bg-white rounded-lg shadow p-8 mb-6">
        <h2 class="text-2xl font-bold mb-4">🔍 Test API Call</h2>
        <button
          @click="testApiCall"
          :disabled="loading"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-bold"
        >
          {{ loading ? '⏳ Testing...' : '▶ Call /konsultasi API' }}
        </button>

        <div v-if="apiError" class="mt-6 p-4 bg-red-100 border-2 border-red-500 text-red-800 rounded-lg">
          <strong>❌ API Error:</strong>
          <pre class="mt-2 text-sm whitespace-pre-wrap">{{ apiError }}</pre>
        </div>

        <div v-if="apiResponse" class="mt-6">
          <h3 class="font-bold mb-2">✅ API Response:</h3>
          <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto font-mono text-sm">
            <pre>{{ JSON.stringify(apiResponse, null, 2) }}</pre>
          </div>
        </div>
      </div>

      <!-- Konsultasi Data Table -->
      <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold mb-4">📋 Konsultasi Data</h2>
        
        <div class="grid grid-cols-3 gap-4 mb-6">
          <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Total Konsultasi</p>
            <p class="text-3xl font-bold text-blue-600">{{ konsultasiList.length }}</p>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Active</p>
            <p class="text-3xl font-bold text-green-600">{{ konsultasiList.filter(k => k.status === 'active').length }}</p>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-3xl font-bold text-yellow-600">{{ konsultasiList.filter(k => k.status === 'pending').length }}</p>
          </div>
        </div>

        <div v-if="konsultasiList.length === 0" class="p-8 bg-yellow-50 text-yellow-800 rounded-lg text-center">
          ⚠️ <strong>No consultations found!</strong><br>
          <p class="text-sm mt-2">Check the API call above to diagnose the issue.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-200">
              <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Patient ID</th>
                <th class="p-3 text-left">Doctor ID</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Created</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="k in konsultasiList" :key="k.id" class="border-t hover:bg-gray-50">
                <td class="p-3 font-mono"><strong>{{ k.id }}</strong></td>
                <td class="p-3 font-mono">{{ k.patient_id }}</td>
                <td class="p-3 font-mono">{{ k.doctor_id }}</td>
                <td class="p-3"><span :class="['px-3 py-1 rounded-full text-white text-xs font-bold',
                  k.status === 'pending' ? 'bg-yellow-500' :
                  k.status === 'active' ? 'bg-blue-500' :
                  k.status === 'closed' ? 'bg-green-500' :
                  'bg-red-500'
                ]">{{ k.status }}</span></td>
                <td class="p-3 text-gray-600">{{ new Date(k.created_at).toLocaleString('id-ID') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Troubleshooting Guide -->
      <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-8 mt-6">
        <h3 class="font-bold text-lg mb-4">🔧 Troubleshooting Guide</h3>
        <ul class="space-y-2 text-sm">
          <li><strong>✓ Check 1:</strong> Ensure you're logged in as DOKTER (check role above)</li>
          <li><strong>✓ Check 2:</strong> Make sure your dokter profile exists (should see Dokter Profile section above)</li>
          <li><strong>✓ Check 3:</strong> Click "Call /konsultasi API" and check for errors</li>
          <li><strong>✓ Check 4:</strong> If no data, ask pasien to create new consultation</li>
          <li><strong>✓ Check 5:</strong> Browser console (F12) for detailed error logs</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { konsultasiAPI } from '@/api/konsultasi'
import { dokterAPI } from '@/api/dokter'
import { pasienAPI } from '@/api/pasien'

const authStore = useAuthStore()
const authUser = ref(null)
const dokterInfo = ref(null)
const pasienInfo = ref(null)
const loading = ref(false)
const apiError = ref(null)
const apiResponse = ref(null)
const konsultasiList = ref([])

onMounted(async () => {
  authUser.value = authStore.user
  
  // Get dokter info if dokter
  if (authUser.value?.role === 'dokter') {
    try {
      const res = await dokterAPI.getByUserId(authUser.value.id)
      dokterInfo.value = res.data?.data || res.data
      console.log('✅ Dokter info loaded:', dokterInfo.value)
    } catch (e) {
      console.error('❌ Error fetching dokter info:', e)
    }
  }

  // Get pasien info if pasien
  if (authUser.value?.role === 'pasien') {
    try {
      const res = await pasienAPI.getList()
      pasienInfo.value = res.data?.data?.[0] || res.data?.[0]
      console.log('✅ Pasien info loaded:', pasienInfo.value)
    } catch (e) {
      console.error('❌ Error fetching pasien info:', e)
    }
  }
})

const testApiCall = async () => {
  loading.value = true
  apiError.value = null
  apiResponse.value = null
  
  try {
    console.log('🔄 Calling /konsultasi API...')
    const response = await konsultasiAPI.getList({})
    apiResponse.value = response.data
    konsultasiList.value = response.data?.data || response.data || []
    console.log('✅ API call success:', {
      totalRecords: konsultasiList.value.length,
      response: apiResponse.value
    })
  } catch (error) {
    const errorMsg = error.response?.data?.pesan || error.message
    apiError.value = JSON.stringify({
      message: errorMsg,
      status: error.response?.status,
      fullResponse: error.response?.data
    }, null, 2)
    console.error('❌ API Error:', error)
  } finally {
    loading.value = false
  }
}
</script>
