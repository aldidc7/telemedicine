<template>
  <!-- Consent Dialog - Full Screen Modal -->
  <div v-if="isOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="bg-linear-to-r from-indigo-600 to-purple-600 p-6 text-white">
        <h2 class="text-2xl font-bold">Informed Consent - Telemedicine</h2>
        <p class="text-indigo-100 text-sm mt-1">Harap baca dan setujui sebelum melanjutkan</p>
      </div>

      <!-- Content Tabs -->
      <div class="flex border-b border-gray-200">
        <button
          v-for="(consent, index) in consentTypes"
          :key="consent.type"
          @click="activeTab = index"
          :class="[
            'flex-1 px-4 py-3 text-sm font-semibold transition border-b-2',
            activeTab === index
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-gray-600 hover:text-gray-900'
          ]"
        >
          {{ consent.name }}
          <span
            v-if="acceptedConsents.includes(consent.type)"
            class="ml-2 text-green-600"
          >
            ✓
          </span>
        </button>
      </div>

      <!-- Consent Text -->
      <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <!-- Loading -->
        <div v-if="isLoadingText" class="flex items-center justify-center h-40">
          <div class="text-center">
            <div class="inline-block w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-2"></div>
            <p class="text-gray-600 text-sm">Loading...</p>
          </div>
        </div>

        <!-- Error -->
        <div v-else-if="textError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-700 font-semibold">{{ textError }}</p>
        </div>

        <!-- Consent Text -->
        <div v-else class="prose prose-sm max-w-none">
          <div v-html="consentText" class="text-gray-700 whitespace-pre-wrap text-sm leading-relaxed"></div>
        </div>
      </div>

      <!-- Footer - Checkbox & Buttons -->
      <div class="border-t border-gray-200 p-6 bg-white">
        <!-- Agreement Checkbox -->
        <div class="mb-4">
          <label class="flex items-start gap-3 cursor-pointer">
            <input
              type="checkbox"
              v-model="agreeConsent"
              :disabled="isAccepting"
              class="mt-1 w-5 h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500 cursor-pointer"
            />
            <span class="text-sm text-gray-700">
              Saya telah membaca dan setuju dengan
              <strong>{{ consentTypes[activeTab].name }}</strong>
            </span>
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="acceptError" class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4">
          <p class="text-red-700 text-sm font-medium">{{ acceptError }}</p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button
            @click="goToPreviousConsent"
            :disabled="activeTab === 0 || isAccepting"
            class="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            ← Sebelumnya
          </button>

          <button
            @click="acceptCurrentConsent"
            :disabled="!agreeConsent || isAccepting"
            class="flex-1 px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isAccepting ? 'Menyimpan...' : 'Setuju & Lanjut' }}
          </button>
        </div>

        <!-- Progress -->
        <div class="mt-4">
          <div class="flex justify-between text-xs text-gray-600 mb-2">
            <span>Progress</span>
            <span>{{ acceptedConsents.length }} / {{ consentTypes.length }}</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: (acceptedConsents.length / consentTypes.length) * 100 + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { authApi } from '@/api/auth'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close', 'consent-complete'])

const activeTab = ref(0)
const agreeConsent = ref(false)
const consentText = ref('')
const acceptedConsents = ref([])
const isLoadingText = ref(false)
const isAccepting = ref(false)
const textError = ref(null)
const acceptError = ref(null)

const consentTypes = [
  {
    type: 'telemedicine',
    name: 'Informed Consent',
    description: 'Persetujuan telemedicine dan risiko'
  },
  {
    type: 'privacy_policy',
    name: 'Privacy Policy',
    description: 'Kebijakan privasi data'
  },
  {
    type: 'data_processing',
    name: 'Data Processing',
    description: 'Penanganan data medis'
  }
]

// Watch active tab to load consent text
watch(() => activeTab.value, () => {
  agreeConsent.value = false
  loadConsentText()
})

// Load consent text
const loadConsentText = async () => {
  isLoadingText.value = true
  textError.value = null

  try {
    const response = await authApi.getConsentText(consentTypes[activeTab.value].type)
    consentText.value = response.data.data.text
  } catch (err) {
    textError.value = 'Gagal memuat consent text'
    console.error('Load consent text error:', err)
  } finally {
    isLoadingText.value = false
  }
}

// Accept current consent
const acceptCurrentConsent = async () => {
  if (!agreeConsent.value) {
    acceptError.value = 'Anda harus setuju untuk melanjutkan'
    return
  }

  isAccepting.value = true
  acceptError.value = null

  try {
    const consentType = consentTypes[activeTab.value].type
    await authApi.acceptConsent({
      consent_type: consentType,
      accepted: true
    })

    // Add to accepted list
    if (!acceptedConsents.value.includes(consentType)) {
      acceptedConsents.value.push(consentType)
    }

    // If all consents accepted
    if (acceptedConsents.value.length === consentTypes.length) {
      emit('consent-complete')
    } else {
      // Go to next consent
      setTimeout(() => {
        activeTab.value++
      }, 500)
    }
  } catch (err) {
    acceptError.value = err.response?.data?.message || 'Gagal menyimpan consent'
    console.error('Accept consent error:', err)
  } finally {
    isAccepting.value = false
  }
}

// Go to previous consent
const goToPreviousConsent = () => {
  if (activeTab.value > 0) {
    activeTab.value--
  }
}

// Load accepted consents on open
onMounted(async () => {
  if (props.isOpen) {
    try {
      const response = await authApi.getConsentStatus()
      acceptedConsents.value = response.data.data.accepted_consents
      loadConsentText()
    } catch (err) {
      console.error('Load consent status error:', err)
    }
  }
})

// Watch isOpen to load data
watch(() => props.isOpen, async (newVal) => {
  if (newVal) {
    try {
      const response = await authApi.getConsentStatus()
      acceptedConsents.value = response.data.data.accepted_consents
      activeTab.value = 0
      loadConsentText()
    } catch (err) {
      console.error('Load consent status error:', err)
    }
  }
})
</script>


