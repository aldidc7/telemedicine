<template>
  <div class="min-h-screen bg-linear-to-br from-blue-50 to-indigo-100 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Rekam Medis Saya</h1>
      <p class="text-gray-600">Lihat dan kelola riwayat rekam medis Anda</p>
    </div>

    <!-- Loading Spinner -->
    <div v-if="isLoading" class="bg-white rounded-lg shadow p-12 flex justify-center items-center">
      <div class="text-center">
        <div class="inline-block">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
        <p class="mt-4 text-gray-600">Memuat rekam medis...</p>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start gap-3">
      <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
      </svg>
      <div>
        <p class="font-medium text-red-800">Terjadi kesalahan</p>
        <p class="text-red-700 text-sm mt-1">{{ errorMessage }}</p>
      </div>
      <button @click="errorMessage = ''" class="ml-auto text-red-500 hover:text-red-700">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>

    <!-- Search & Filter -->
    <div v-if="!isLoading" class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Cari Diagnosis</label>
          <input v-model="searchQuery" type="text" placeholder="Cari diagnosis..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Rekam</label>
          <select v-model="selectedType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            <option value="">Semua Tipe</option>
            <option value="consultation">Konsultasi</option>
            <option value="checkup">Pemeriksaan Umum</option>
            <option value="follow_up">Follow Up</option>
            <option value="emergency">Darurat</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Dokter</label>
          <select v-model="selectedDoctor" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            <option value="">Semua Dokter</option>
            <option v-for="doctor in uniqueDoctors" :key="doctor" :value="doctor">
              {{ doctor }}
            </option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex justify-between items-center">
        <p class="text-sm text-gray-600">Menampilkan <span class="font-semibold">{{ filteredRecords.length }}</span> rekam medis</p>
        <button v-if="filteredRecords.length > 0" @click="downloadAllRecords" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8l-6-4m6 4l6-4" />
          </svg>
          Unduh Semua
        </button>
      </div>
    </div>

    <!-- Records List -->
    <div v-if="!isLoading" class="space-y-4">
      <div v-if="filteredRecords.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-4 text-gray-600 text-lg">{{ allRecords.length === 0 ? 'Belum ada rekam medis' : 'Tidak ada rekam medis yang sesuai dengan filter' }}</p>
      </div>

      <div v-for="record in filteredRecords" :key="record.id" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span :class="getTypeColor(record.type)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ translateType(record.type) }}
                </span>
                <span class="text-sm text-gray-600">{{ formatDate(record.created_at) }}</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900">{{ record.diagnosis || 'Konsultasi' }}</h3>
            </div>
            <button @click="openRecord(record)" class="text-blue-600 hover:text-blue-800 p-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
              <p class="text-sm text-gray-600">Dokter</p>
              <p class="font-semibold text-gray-900 truncate">{{ record.doctor?.name || record.doctor_name || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Gejala</p>
              <p class="font-semibold text-gray-900 truncate">{{ record.symptoms || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Pengobatan</p>
              <p class="font-semibold text-gray-900 truncate">{{ record.treatment || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Status</p>
              <p class="font-semibold text-green-600">Selesai</p>
            </div>
          </div>

          <div v-if="record.notes" class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600 font-medium mb-2">Catatan Dokter:</p>
            <p class="text-gray-900 line-clamp-2">{{ record.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetail && selectedRecord" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-900">Detail Rekam Medis</h2>
          <button @click="closeDetail" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600 mb-1">Diagnosis</p>
              <p class="text-lg font-semibold text-gray-900">{{ selectedRecord.diagnosis }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600 mb-1">Tipe</p>
              <p class="text-lg font-semibold">{{ translateType(selectedRecord.type) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600 mb-1">Gejala</p>
              <p class="text-gray-900">{{ selectedRecord.symptoms }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600 mb-1">Dokter</p>
              <p class="text-gray-900">{{ selectedRecord.doctor_name }}</p>
            </div>
          </div>

          <div>
            <p class="text-sm text-gray-600 mb-2">Pengobatan</p>
            <p class="text-gray-900">{{ selectedRecord.treatment || 'Tidak ada pengobatan yang dicatat' }}</p>
          </div>

          <div v-if="selectedRecord.prescriptions" class="border-t border-gray-200 pt-4">
            <p class="text-sm text-gray-600 mb-3 font-medium">Resep Obat:</p>
            <ul class="space-y-2">
              <li v-for="(item, index) in selectedRecord.prescriptions" :key="index" class="text-gray-900">
                {{ item }}
              </li>
            </ul>
          </div>

          <div v-if="selectedRecord.notes" class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600 font-medium mb-2">Catatan Tambahan:</p>
            <p class="text-gray-900">{{ selectedRecord.notes }}</p>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
            <button @click="downloadRecord" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8l-6-4m6 4l6-4" />
              </svg>
              Download
            </button>
            <button @click="closeDetail" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { pasienAPI } from '../../api/pasien'
import { useRouter } from 'vue-router'

const router = useRouter()

const searchQuery = ref('')
const selectedType = ref('')
const selectedDoctor = ref('')
const showDetail = ref(false)
const selectedRecord = ref(null)
const isLoading = ref(false)
const errorMessage = ref('')

const allRecords = ref([])

onMounted(() => {
  loadRecords()
})

const loadRecords = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await pasienAPI.getRekamMedis(null, {
      include: 'doctor',
      per_page: 100
    })
    allRecords.value = response.data?.data || response.data || []
  } catch (error) {
    console.error('Error loading records:', error)
    errorMessage.value = error.response?.data?.message || 'Gagal memuat rekam medis. Silakan coba lagi.'
    // Fallback to empty array
    allRecords.value = []
  } finally {
    isLoading.value = false
  }
}

const filteredRecords = computed(() => {
  return allRecords.value.filter(record => {
    const diagnosis = record.diagnosis || ''
    const matchesSearch = searchQuery.value === '' || diagnosis.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesType = selectedType.value === '' || record.type === selectedType.value
    const doctorName = record.doctor?.name || record.doctor_name || ''
    const matchesDoctor = selectedDoctor.value === '' || doctorName.includes(selectedDoctor.value)
    return matchesSearch && matchesType && matchesDoctor
  })
})

const uniqueDoctors = computed(() => {
  const doctors = new Set()
  allRecords.value.forEach(record => {
    const name = record.doctor?.name || record.doctor_name
    if (name) doctors.add(name)
  })
  return Array.from(doctors).sort()
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getTypeColor = (type) => {
  const colors = {
    'consultation': 'bg-blue-100 text-blue-800',
    'checkup': 'bg-green-100 text-green-800',
    'follow_up': 'bg-purple-100 text-purple-800',
    'emergency': 'bg-red-100 text-red-800',
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}

const translateType = (type) => {
  const translations = {
    'consultation': 'Konsultasi',
    'checkup': 'Pemeriksaan Umum',
    'follow_up': 'Follow Up',
    'emergency': 'Darurat',
  }
  return translations[type] || type
}

const openRecord = (record) => {
  selectedRecord.value = record
  showDetail.value = true
}

const closeDetail = () => {
  showDetail.value = false
  selectedRecord.value = null
}

// Generate PDF for single record
const generateRecordPDF = (record) => {
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')
  
  const width = 800
  const height = 1100
  canvas.width = width
  canvas.height = height
  
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, width, height)
  
  // Header
  ctx.fillStyle = '#1e40af'
  ctx.fillRect(0, 0, width, 80)
  
  ctx.fillStyle = '#ffffff'
  ctx.font = 'bold 24px Arial'
  ctx.fillText('REKAM MEDIS PASIEN', 50, 45)
  
  // Content
  ctx.fillStyle = '#000000'
  ctx.font = '14px Arial'
  
  let y = 120
  const lineHeight = 25
  const margin = 50
  
  // Title
  ctx.font = 'bold 16px Arial'
  ctx.fillText(`Diagnosis: ${record.diagnosis || '-'}`, margin, y)
  y += lineHeight + 10
  
  // Details
  ctx.font = '14px Arial'
  ctx.fillText(`Tipe: ${translateType(record.type)}`, margin, y)
  y += lineHeight
  
  ctx.fillText(`Tanggal: ${formatDate(record.created_at)}`, margin, y)
  y += lineHeight
  
  const doctorName = record.doctor?.name || record.doctor_name || '-'
  ctx.fillText(`Dokter: ${doctorName}`, margin, y)
  y += lineHeight
  
  ctx.fillText(`Gejala: ${record.symptoms || '-'}`, margin, y)
  y += lineHeight
  
  ctx.fillText(`Pengobatan: ${record.treatment || '-'}`, margin, y)
  y += lineHeight + 10
  
  // Notes
  if (record.notes) {
    ctx.font = 'bold 14px Arial'
    ctx.fillText('Catatan Dokter:', margin, y)
    y += lineHeight
    
    ctx.font = '12px Arial'
    const noteLines = ctx.measureText(record.notes).width > (width - 100) 
      ? wrapText(ctx, record.notes, width - 100)
      : [record.notes]
    
    noteLines.forEach(line => {
      ctx.fillText(line, margin, y)
      y += lineHeight
    })
    y += 10
  }
  
  // Prescriptions
  if (record.prescriptions && record.prescriptions.length > 0) {
    ctx.font = 'bold 14px Arial'
    ctx.fillText('Resep Obat:', margin, y)
    y += lineHeight
    
    ctx.font = '12px Arial'
    record.prescriptions.forEach(med => {
      ctx.fillText(`• ${med}`, margin + 20, y)
      y += lineHeight
    })
  }
  
  // Footer
  ctx.fillStyle = '#999999'
  ctx.font = '10px Arial'
  ctx.fillText(`Dicetak pada: ${new Date().toLocaleString('id-ID')}`, margin, height - 30)
  
  return canvas
}

const wrapText = (ctx, text, maxWidth) => {
  const words = text.split(' ')
  const lines = []
  let line = ''
  
  words.forEach(word => {
    const testLine = line + word + ' '
    if (ctx.measureText(testLine).width > maxWidth) {
      lines.push(line)
      line = word + ' '
    } else {
      line = testLine
    }
  })
  
  lines.push(line)
  return lines
}

const downloadRecord = () => {
  if (!selectedRecord.value) return
  
  try {
    const canvas = generateRecordPDF(selectedRecord.value)
    const link = document.createElement('a')
    link.href = canvas.toDataURL('image/png')
    link.download = `Rekam_Medis_${selectedRecord.value.diagnosis}_${formatDate(selectedRecord.value.created_at).replace(/\s+/g, '_')}.png`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (error) {
    console.error('Error downloading record:', error)
    errorMessage.value = 'Gagal mengunduh rekam medis'
  }
}

const downloadAllRecords = () => {
  try {
    if (filteredRecords.value.length === 0) {
      errorMessage.value = 'Tidak ada rekam medis untuk diunduh'
      return
    }
    
    // Download each record
    filteredRecords.value.forEach((record, index) => {
      setTimeout(() => {
        selectedRecord.value = record
        downloadRecord()
      }, index * 500) // Delay 500ms between downloads
    })
  } catch (error) {
    console.error('Error downloading all records:', error)
    errorMessage.value = 'Gagal mengunduh rekam medis'
  }
}
</script>
