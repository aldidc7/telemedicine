<template>
  <!-- Doctor Card with Verification Badge -->
  <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition border border-gray-100 overflow-hidden">
    <!-- Image & Verification Badge -->
    <div class="relative h-48 bg-linear-to-br from-indigo-100 to-purple-100">
      <img
        v-if="doctor.profile_photo"
        :src="doctor.profile_photo"
        :alt="doctor.name"
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
          />
        </svg>
      </div>

      <!-- Verification Badge - TOP RIGHT -->
      <div class="absolute top-3 right-3">
        <div
          v-if="doctor.is_verified"
          class="flex items-center gap-1 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
          title="Dokter telah diverifikasi oleh admin"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
          Terverifikasi
        </div>
        <div
          v-else
          class="flex items-center gap-1 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
          title="Dokter belum diverifikasi admin - Tidak bisa konsultasi"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
          Pending
        </div>
      </div>

      <!-- Availability Badge - TOP LEFT -->
      <div v-if="doctor.is_available" class="absolute top-3 left-3">
        <div class="flex items-center gap-1 bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
          <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
          Tersedia
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Name & Specialization -->
      <h3 class="text-lg font-bold text-gray-900">{{ doctor.user?.name }}</h3>
      <p class="text-indigo-600 font-semibold text-sm">{{ doctor.specialization || 'Spesialisasi tidak ditentukan' }}</p>

      <!-- License -->
      <p class="text-gray-500 text-xs mt-1">SIP: {{ doctor.license_number || '-' }}</p>

      <!-- Rating (if available) -->
      <div v-if="doctor.rating" class="flex items-center gap-2 mt-3">
        <div class="flex items-center">
          <span v-for="i in 5" :key="i" class="text-yellow-400">
            <svg
              class="w-4 h-4 fill-current"
              :class="{ 'text-gray-300': i > Math.round(doctor.rating) }"
              viewBox="0 0 20 20"
            >
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
            </svg>
          </span>
        </div>
        <span class="text-sm text-gray-600">{{ doctor.rating.toFixed(1) }}</span>
        <span class="text-xs text-gray-500">({{ doctor.review_count || 0 }} reviews)</span>
      </div>

      <!-- Address -->
      <p class="text-gray-600 text-xs mt-2 line-clamp-2">{{ doctor.address || 'Alamat tidak tersedia' }}</p>

      <!-- Action Button -->
      <button
        v-if="doctor.is_verified && doctor.is_available"
        @click="$emit('consult', doctor)"
        class="w-full mt-4 bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
      >
        Konsultasi Sekarang
      </button>
      <button
        v-else-if="!doctor.is_verified"
        disabled
        class="w-full mt-4 bg-gray-300 text-gray-600 py-2 rounded-lg font-semibold cursor-not-allowed"
        title="Dokter belum terverifikasi"
      >
        Belum Terverifikasi
      </button>
      <button
        v-else
        disabled
        class="w-full mt-4 bg-gray-300 text-gray-600 py-2 rounded-lg font-semibold cursor-not-allowed"
        title="Dokter sedang tidak tersedia"
      >
        Tidak Tersedia
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  doctor: {
    type: Object,
    required: true,
    validator: (obj) => {
      return (
        'id' in obj &&
        'is_verified' in obj &&
        'is_available' in obj &&
        'specialization' in obj &&
        'user' in obj
      )
    }
  }
})

defineEmits(['consult'])
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
