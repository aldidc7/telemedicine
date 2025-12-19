<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Rekam Medis Saya</h1>
      <p class="text-gray-600">Lihat dan kelola riwayat rekam medis Anda</p>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
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
            <option value="1">Dr. Ahmad Zaki</option>
            <option value="2">Dr. Siti Nurhaliza</option>
            <option value="3">Dr. Budi Santoso</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Records List -->
    <div class="space-y-4">
      <div v-if="records.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-4 text-gray-600 text-lg">Belum ada rekam medis</p>
      </div>

      <div v-for="record in records" :key="record.id" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span :class="getTypeColor(record.type)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ translateType(record.type) }}
                </span>
                <span class="text-sm text-gray-600">{{ formatDate(record.created_at) }}</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900">{{ record.diagnosis }}</h3>
            </div>
            <button @click="openRecord(record)" class="text-blue-600 hover:text-blue-800">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
              <p class="text-sm text-gray-600">Dokter</p>
              <p class="font-semibold text-gray-900">{{ record.doctor_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Gejala</p>
              <p class="font-semibold text-gray-900">{{ record.symptoms }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Pengobatan</p>
              <p class="font-semibold text-gray-900">{{ record.treatment || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Status</p>
              <p class="font-semibold text-green-600">Selesai</p>
            </div>
          </div>

          <div v-if="record.notes" class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600 font-medium mb-2">Catatan Dokter:</p>
            <p class="text-gray-900">{{ record.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="records.length > 0" class="mt-8 flex items-center justify-between">
      <p class="text-sm text-gray-600">Menampilkan {{ records.length }} rekam medis</p>
      <div class="flex gap-2">
        <button :disabled="currentPage === 1" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">Sebelumnya</button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">{{ currentPage }}</button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">Berikutnya</button>
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

const searchQuery = ref('')
const selectedType = ref('')
const selectedDoctor = ref('')
const currentPage = ref(1)
const showDetail = ref(false)
const selectedRecord = ref(null)

const records = ref([])
const allRecords = ref([])

onMounted(() => {
  loadRecords()
})

const loadRecords = async () => {
  try {
    // TODO: Replace with actual API call
    allRecords.value = [
      {
        id: 1,
        diagnosis: 'Demam Tinggi',
        type: 'consultation',
        symptoms: 'Demam, Pusing',
        treatment: 'Istirahat, Minum Air Hangat',
        doctor_name: 'Dr. Ahmad Zaki',
        notes: 'Pasien harus istirahat 3 hari dan kontrol ke klinik jika demam tidak turun',
        prescriptions: ['Paracetamol 500mg (3x sehari)', 'Vitamin C 500mg (2x sehari)'],
        created_at: new Date('2025-12-15'),
      },
      {
        id: 2,
        diagnosis: 'Batuk Pilek',
        type: 'checkup',
        symptoms: 'Batuk, Pilek',
        treatment: 'Minum Obat Batuk',
        doctor_name: 'Dr. Siti Nurhaliza',
        notes: 'Kondisi mulai membaik, lanjutkan minum obat',
        prescriptions: ['Obat Batuk (3x sehari)', 'Madu (2x sehari)'],
        created_at: new Date('2025-12-10'),
      },
    ]
    records.value = allRecords.value
  } catch (error) {
    console.error('Error loading records:', error)
  }
}

const filteredRecords = computed(() => {
  return allRecords.value.filter(record => {
    const matchesSearch = searchQuery.value === '' || record.diagnosis.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesType = selectedType.value === '' || record.type === selectedType.value
    const matchesDoctor = selectedDoctor.value === '' || record.doctor_name.includes(selectedDoctor.value)
    return matchesSearch && matchesType && matchesDoctor
  })
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

const downloadRecord = () => {
  // TODO: Implement PDF download
  alert('Fitur download akan tersedia segera')
}
</script>
