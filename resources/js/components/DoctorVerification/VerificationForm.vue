<template>
  <div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Start Doctor Verification</h1>
      <p class="text-gray-600">Complete your verification to unlock full consultation features</p>
    </div>

    <!-- Verification Form -->
    <form @submit.prevent="submitVerification" class="bg-white rounded-lg shadow-md p-6 space-y-6">
      <!-- Medical License -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Medical License Number *</label>
        <input
          v-model="form.medical_license"
          type="text"
          placeholder="e.g., 12345/DKK/2020"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="isSubmitting"
          required
        />
        <p class="mt-1 text-xs text-gray-500">Format: your official medical license number</p>
      </div>

      <!-- Specialization -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Specialization *</label>
        <select
          v-model="form.specialization"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="isSubmitting"
          required
        >
          <option value="">Select your specialization</option>
          <option value="General Practice">General Practice</option>
          <option value="Cardiology">Cardiology</option>
          <option value="Dermatology">Dermatology</option>
          <option value="Pediatrics">Pediatrics</option>
          <option value="Orthopedics">Orthopedics</option>
          <option value="Psychiatry">Psychiatry</option>
          <option value="Neurology">Neurology</option>
          <option value="Surgery">Surgery</option>
          <option value="Obstetrics & Gynecology">Obstetrics & Gynecology</option>
          <option value="Oncology">Oncology</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <!-- Institution -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Institution/Hospital</label>
        <input
          v-model="form.institution"
          type="text"
          placeholder="e.g., Hospital ABC or University Medical Center"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="isSubmitting"
        />
      </div>

      <!-- Years of Experience -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Years of Experience *</label>
        <input
          v-model.number="form.years_of_experience"
          type="number"
          min="0"
          max="70"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :disabled="isSubmitting"
          required
        />
      </div>

      <!-- Requirements -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-medium text-blue-900 mb-3">Required Documents:</h3>
        <ul class="space-y-2 text-sm text-blue-800">
          <li class="flex items-start">
            <span class="mr-2">📄</span>
            <span>KTP (Identity Card)</span>
          </li>
          <li class="flex items-start">
            <span class="mr-2">📜</span>
            <span>SKP (Certification of Medical Competence)</span>
          </li>
          <li class="flex items-start">
            <span class="mr-2">🏅</span>
            <span>Medical Certificate/License</span>
          </li>
        </ul>
        <p class="mt-3 text-xs text-blue-700">You'll upload these documents in the next step.</p>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium transition"
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Submitting...' : 'Continue to Document Upload' }}
      </button>

      <!-- Terms -->
      <p class="text-xs text-gray-500 text-center">
        By submitting, you confirm that all information provided is accurate and complete.
      </p>
    </form>

    <!-- Messages -->
    <div v-if="successMessage" class="fixed top-4 right-4 bg-green-500 text-white p-4 rounded shadow-lg">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="fixed top-4 right-4 bg-red-500 text-white p-4 rounded shadow-lg">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  medical_license: '',
  specialization: '',
  institution: '',
  years_of_experience: null,
})

const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const submitVerification = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const response = await axios.post('/api/v1/doctor-verification/submit', form.value)

    successMessage.value = 'Verification submitted! Proceeding to document upload...'

    // Redirect to document upload after 1 second
    setTimeout(() => {
      router.push(`/doctor/verification/${response.data.data.id}/documents`)
    }, 1000)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to submit verification'
    console.error('Submission error:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>
