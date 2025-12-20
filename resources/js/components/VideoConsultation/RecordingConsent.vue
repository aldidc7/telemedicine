<template>
  <div v-if="show" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4">
      <!-- Header -->
      <div class="bg-linear-to-r from-orange-600 to-orange-700 px-6 py-4">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          <span>⚠️</span>
          Recording Consent
        </h2>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-4">
        <!-- Warning Box -->
        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
          <p class="text-sm text-gray-800">
            <strong>This consultation may be recorded</strong> for quality assurance and training purposes.
            Your consent is required before proceeding.
          </p>
        </div>

        <!-- Consent Details -->
        <div class="bg-gray-50 p-4 rounded-lg space-y-3">
          <div class="flex items-start gap-3">
            <input
              type="checkbox"
              id="consent-recording"
              v-model="consentToRecording"
              class="mt-1 w-4 h-4 text-orange-600 rounded focus:ring-orange-500"
            />
            <label for="consent-recording" class="text-sm text-gray-700 flex-1">
              <strong>I agree to this consultation being recorded</strong>
              <p class="text-xs text-gray-600 mt-1">
                The recording will be stored securely and automatically deleted after 30 days.
              </p>
            </label>
          </div>

          <div class="flex items-start gap-3">
            <input
              type="checkbox"
              id="consent-privacy"
              v-model="consentToPrivacy"
              class="mt-1 w-4 h-4 text-orange-600 rounded focus:ring-orange-500"
            />
            <label for="consent-privacy" class="text-sm text-gray-700 flex-1">
              <strong>I understand the privacy policy</strong>
              <a href="/privacy-policy" target="_blank" class="text-orange-600 hover:text-orange-700 underline">
                View policy
              </a>
            </label>
          </div>

          <div class="flex items-start gap-3">
            <input
              type="checkbox"
              id="consent-understand"
              v-model="understandLimitations"
              class="mt-1 w-4 h-4 text-orange-600 rounded focus:ring-orange-500"
            />
            <label for="consent-understand" class="text-sm text-gray-700 flex-1">
              <strong>I understand telemedicine limitations</strong>
              <p class="text-xs text-gray-600 mt-1">
                Diagnosis via telemedicine may not be 100% accurate. I will seek emergency services if needed.
              </p>
            </label>
          </div>
        </div>

        <!-- Recording Information -->
        <div class="border-t pt-4">
          <p class="text-xs text-gray-600 space-y-2">
            <div class="flex gap-2">
              <span>📹</span>
              <span><strong>Recording Use:</strong> Quality assurance, training, and compliance purposes only</span>
            </div>
            <div class="flex gap-2">
              <span>🔒</span>
              <span><strong>Security:</strong> End-to-end encrypted storage</span>
            </div>
            <div class="flex gap-2">
              <span>⏰</span>
              <span><strong>Retention:</strong> Automatically deleted after 30 days</span>
            </div>
            <div class="flex gap-2">
              <span>🚫</span>
              <span><strong>Access:</strong> Only authorized personnel can access</span>
            </div>
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
          {{ errorMessage }}
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-gray-50 px-6 py-4 border-t flex gap-3">
        <button
          @click="declineConsent"
          :disabled="isProcessing"
          class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ consentToRecording ? 'No Recording' : 'Continue Without' }}
        </button>
        <button
          @click="giveConsent"
          :disabled="!allConsentsGiven || isProcessing"
          class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <span v-if="isProcessing">⏳</span>
          <span>{{ isProcessing ? 'Processing...' : 'I Agree & Continue' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  consultationId: number
  show: boolean
}

const props = withDefaults(defineProps<Props>(), {
  show: true,
})

const emit = defineEmits<{
  'consent-given': [consent: boolean]
  'consent-declined': []
  'error': [message: string]
}>()

// State
const consentToRecording = ref(false)
const consentToPrivacy = ref(false)
const understandLimitations = ref(false)
const isProcessing = ref(false)
const errorMessage = ref<string | null>(null)

// Computed
const allConsentsGiven = computed(
  () => consentToRecording.value && consentToPrivacy.value && understandLimitations.value
)

// Methods
const giveConsent = async () => {
  if (!allConsentsGiven.value) return

  try {
    isProcessing.value = true
    errorMessage.value = null

    const response = await fetch(
      `/api/video-consultations/${props.consultationId}/consent`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        },
        body: JSON.stringify({
          consented_to_recording: consentToRecording.value,
          consent_reason: 'User accepted before video consultation',
          ip_address: await getClientIP(),
          user_agent: navigator.userAgent,
        }),
      }
    )

    if (!response.ok) {
      const error = await response.json()
      throw new Error(error.message || 'Failed to save consent')
    }

    emit('consent-given', consentToRecording.value)
  } catch (error) {
    const message = (error as Error).message || 'An error occurred'
    errorMessage.value = message
    emit('error', message)
  } finally {
    isProcessing.value = false
  }
}

const declineConsent = async () => {
  try {
    isProcessing.value = true
    errorMessage.value = null

    // Save declined consent
    await fetch(
      `/api/video-consultations/${props.consultationId}/consent`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        },
        body: JSON.stringify({
          consented_to_recording: false,
          consent_reason: 'User declined recording',
          ip_address: await getClientIP(),
          user_agent: navigator.userAgent,
        }),
      }
    )

    emit('consent-declined')
  } catch (error) {
    const message = (error as Error).message || 'An error occurred'
    errorMessage.value = message
    emit('error', message)
  } finally {
    isProcessing.value = false
  }
}

// Helper: Get client IP (fallback method)
const getClientIP = async (): Promise<string> => {
  try {
    const response = await fetch('https://api.ipify.org?format=json')
    const data = await response.json()
    return data.ip
  } catch {
    return 'unknown'
  }
}
</script>

<style scoped>
/* Custom checkbox styling */
input[type='checkbox']:checked {
  background-color: rgb(234 88 12);
  border-color: rgb(234 88 12);
}
</style>
