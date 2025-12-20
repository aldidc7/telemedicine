<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div
        class="fixed inset-0 bg-black bg-opacity-50"
        @click="$emit('close')"
      ></div>

      <div class="flex min-h-screen items-center justify-center p-4">
        <div
          class="relative bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-full overflow-y-auto"
          @click.stop
        >
          <!-- Header -->
          <div class="sticky top-0 px-6 py-4 flex items-center justify-between" style="background: linear-gradient(to right, rgb(37, 99, 235), rgb(79, 70, 229));">
            <h2 class="text-xl font-bold text-white">Surat Rujukan Medis</h2>
            <button
              @click="$emit('close')"
              class="text-white hover:bg-white hover:bg-opacity-20 p-1 rounded transition"
            >
              <X class="w-6 h-6" />
            </button>
          </div>

          <!-- Referral Letter Content -->
          <div id="referral-letter" class="p-8 bg-white">
            <!-- Hospital Header -->
            <div class="border-b-2 border-gray-300 pb-6 mb-6">
              <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800">SURAT RUJUKAN MEDIS</h1>
                <p class="text-sm text-gray-600 mt-2">Telemedicine Platform - Sistem Penanganan Darurat</p>
              </div>
            </div>

            <!-- Referral Info -->
            <div class="grid grid-cols-2 gap-8 mb-8">
              <!-- From (Referring Doctor/Clinic) -->
              <div>
                <h3 class="font-bold text-gray-800 mb-2">DARI:</h3>
                <p class="text-gray-700">Platform Telemedicine</p>
                <p class="text-gray-600 text-sm mt-2">Sistem Kesehatan Digital Terintegrasi</p>
              </div>

              <!-- To (Receiving Hospital) -->
              <div>
                <h3 class="font-bold text-gray-800 mb-2">KEPADA:</h3>
                <p class="text-gray-700">{{ emergency.hospital_name }}</p>
                <p class="text-gray-600 text-sm">{{ emergency.hospital_address }}</p>
              </div>
            </div>

            <!-- Patient Information -->
            <div class="mb-8">
              <h3 class="font-bold text-gray-800 mb-3 border-b-2 border-gray-300 pb-2">DATA PASIEN</h3>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p class="text-gray-600">Nama Pasien</p>
                  <p class="font-semibold text-gray-800">{{ emergency.patient_name || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-gray-600">No. Identitas</p>
                  <p class="font-semibold text-gray-800">{{ emergency.patient_id || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-gray-600">Jenis Kelamin</p>
                  <p class="font-semibold text-gray-800">{{ emergency.patient_gender || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-gray-600">Umur</p>
                  <p class="font-semibold text-gray-800">{{ emergency.patient_age || 'N/A' }} tahun</p>
                </div>
                <div class="col-span-2">
                  <p class="text-gray-600">Alamat</p>
                  <p class="font-semibold text-gray-800">{{ emergency.patient_address || 'N/A' }}</p>
                </div>
              </div>
            </div>

            <!-- Clinical Information -->
            <div class="mb-8">
              <h3 class="font-bold text-gray-800 mb-3 border-b-2 border-gray-300 pb-2">INFORMASI KLINIK</h3>
              
              <div class="mb-4">
                <p class="text-gray-600 text-sm">Tingkat Kegawatan</p>
                <p :class="getLevelColor(emergency.level)" class="text-lg font-bold">
                  {{ getLevelLabel(emergency.level) }}
                </p>
              </div>

              <div class="mb-4">
                <p class="text-gray-600 font-semibold">Alasan Rujukan / Gejala Utama</p>
                <p class="text-gray-800 mt-2 whitespace-pre-wrap bg-gray-50 p-4 rounded border border-gray-200">
                  {{ emergency.reason }}
                </p>
              </div>

              <div v-if="emergency.notes" class="mb-4">
                <p class="text-gray-600 font-semibold">Catatan Klinis Tambahan</p>
                <p class="text-gray-800 mt-2 whitespace-pre-wrap bg-gray-50 p-4 rounded border border-gray-200">
                  {{ emergency.notes }}
                </p>
              </div>
            </div>

            <!-- Consultation History -->
            <div class="mb-8">
              <h3 class="font-bold text-gray-800 mb-3 border-b-2 border-gray-300 pb-2">RIWAYAT KONSULTASI</h3>
              <div class="bg-gray-50 p-4 rounded border border-gray-200 text-sm">
                <p><span class="text-gray-600">Tanggal Konsultasi:</span> <span class="font-semibold">{{ formatDate(emergency.consultation_date) }}</span></p>
                <p class="mt-2"><span class="text-gray-600">Dokter:</span> <span class="font-semibold">{{ emergency.doctor_name || 'N/A' }}</span></p>
                <p class="mt-2"><span class="text-gray-600">Spesialisasi:</span> <span class="font-semibold">{{ emergency.doctor_specialization || 'N/A' }}</span></p>
              </div>
            </div>

            <!-- Instructions -->
            <div class="mb-8">
              <h3 class="font-bold text-gray-800 mb-3 border-b-2 border-gray-300 pb-2">INSTRUKSI RUJUKAN</h3>
              <p class="text-gray-700 mb-3">Pasien dirujuk untuk mendapatkan penanganan lebih lanjut dan monitoring medis yang intensif.</p>
              <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>Pasien harus didampingi keluarga atau orang terdekat</li>
                <li>Bawa dokumen identitas diri pasien</li>
                <li>Informasikan kondisi kegawatan pasien ke petugas penerima</li>
                <li>Lakukan pemeriksaan lengkap dan sesuai protokol</li>
              </ul>
            </div>

            <!-- Signature -->
            <div class="border-t-2 border-gray-300 pt-6 grid grid-cols-3 gap-8">
              <!-- Left: Reference -->
              <div>
                <p class="text-gray-600 text-sm mb-2">Nomor Rujukan</p>
                <p class="font-mono text-sm font-bold">{{ generateReferenceNumber() }}</p>
              </div>

              <!-- Center: Date -->
              <div class="text-center">
                <p class="text-gray-600 text-sm mb-2">Tanggal Rujukan</p>
                <p class="font-semibold">{{ formatDate(emergency.escalated_at || emergency.created_at) }}</p>
              </div>

              <!-- Right: Authorized By -->
              <div>
                <p class="text-gray-600 text-sm mb-6">Dikeluarkan oleh:</p>
                <p class="font-semibold text-sm">Sistem Telemedicine</p>
                <p class="text-xs text-gray-600 mt-1">Platform Kesehatan Digital</p>
              </div>
            </div>

            <!-- Disclaimer -->
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
              <p class="font-semibold mb-2">⚠️ PENTING:</p>
              <p>Surat rujukan ini adalah dokumen medis resmi dan harus disimpan dengan baik. Informasi pasien di dalamnya bersifat rahasia dan dilindungi oleh undang-undang perlindungan data pribadi. Akses hanya diizinkan untuk pihak medis yang bertanggung jawab.</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t border-gray-200 flex gap-3">
            <button
              @click="downloadPDF"
              class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
            >
              <Download class="w-5 h-5" />
              Download PDF
            </button>
            <button
              @click="printLetter"
              class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2"
            >
              <Printer class="w-5 h-5" />
              Cetak
            </button>
            <button
              @click="$emit('close')"
              class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-semibold"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { X, Download, Printer } from 'lucide-vue-next'

const props = defineProps({
  emergency: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['close'])

const getLevelLabel = (level) => {
  const labels = {
    critical: '⚠️ KRITIS',
    severe: '🔴 SERIUS',
    moderate: '🟡 SEDANG',
  }
  return labels[level] || level
}

const getLevelColor = (level) => {
  const colors = {
    critical: 'text-red-700',
    severe: 'text-red-600',
    moderate: 'text-orange-600',
  }
  return colors[level] || ''
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const generateReferenceNumber = () => {
  return `RJ-${props.emergency.id}-${new Date().getFullYear()}`
}

const downloadPDF = () => {
  // In a real app, this would use a library like html2pdf
  const element = document.getElementById('referral-letter')
  window.print()
}

const printLetter = () => {
  window.print()
}
</script>

<style scoped>
@media print {
  body {
    margin: 0;
    padding: 0;
  }
  
  #referral-letter {
    margin: 0;
    box-shadow: none;
    border: none;
  }
}
</style>
