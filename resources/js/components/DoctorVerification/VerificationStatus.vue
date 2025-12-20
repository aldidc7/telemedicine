<template>
  <div class="max-w-2xl mx-auto">
    <!-- Status Card -->
    <div v-if="verification" class="bg-white rounded-lg shadow-md p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Doctor Verification Status</h2>
        <span :class="statusBadge">{{ verification.status }}</span>
      </div>

      <!-- Status Timeline -->
      <div class="mt-6 space-y-4">
        <div class="flex items-center">
          <div :class="getStatusIcon('submitted')" class="mr-3"></div>
          <div>
            <p class="font-medium">Submitted</p>
            <p class="text-sm text-gray-500">{{ formatDate(verification.created_at) }}</p>
          </div>
        </div>

        <div class="flex items-center">
          <div :class="getStatusIcon('reviewed')" class="mr-3"></div>
          <div>
            <p class="font-medium">Under Review</p>
            <p class="text-sm text-gray-500">
              {{ verification.status === 'in_review' ? 'Currently reviewing...' : 'Completed' }}
            </p>
          </div>
        </div>

        <div class="flex items-center">
          <div :class="getStatusIcon('verified')" class="mr-3"></div>
          <div>
            <p class="font-medium">Verified</p>
            <p class="text-sm text-gray-500">
              {{ verification.approved_at ? formatDate(verification.approved_at) : 'Pending...' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Rejection Reason -->
      <div v-if="verification.status === 'rejected'" class="mt-6 p-4 bg-red-50 border border-red-200 rounded">
        <p class="font-medium text-red-800 mb-2">Rejection Reason:</p>
        <p class="text-red-700">{{ verification.rejection_reason }}</p>
        <button
          @click="resetVerification"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          :disabled="isResetting"
        >
          {{ isResetting ? 'Resetting...' : 'Resubmit Documents' }}
        </button>
      </div>
    </div>

    <!-- No Verification Message -->
    <div v-else class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
      <p class="text-blue-800">
        You haven't submitted a verification request yet. Start the process below.
      </p>
    </div>

    <!-- Document Upload Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Upload Documents</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="docType in requiredDocuments"
          :key="docType"
          class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 cursor-pointer"
          @drop="onFileDrop($event, docType)"
          @dragover.prevent
          @dragenter.prevent
        >
          <input
            type="file"
            :ref="`file-${docType}`"
            @change="onFileSelected($event, docType)"
            class="hidden"
            accept=".pdf,.jpg,.jpeg,.png"
          />

          <div
            @click="$refs[`file-${docType}`][0].click()"
            class="text-center"
          >
            <svg
              class="mx-auto h-8 w-8 text-gray-400 mb-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
              />
            </svg>
            <p class="font-medium text-gray-700 capitalize">{{ docType }}</p>
            <p class="text-xs text-gray-500 mt-1">PDF, JPG, or PNG (Max 5MB)</p>
          </div>

          <!-- Uploaded Document -->
          <div
            v-if="uploadedDocs[docType]"
            class="mt-2 p-2 bg-green-50 rounded text-green-700 text-sm"
          >
            ✓ {{ uploadedDocs[docType].name }}
          </div>
        </div>
      </div>

      <button
        @click="uploadDocuments"
        class="mt-6 w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        :disabled="!hasAllDocuments || isUploading"
      >
        {{ isUploading ? 'Uploading...' : 'Upload Documents' }}
      </button>
    </div>

    <!-- Progress -->
    <div v-if="showProgress" class="bg-white rounded-lg shadow-md p-6">
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-blue-600 h-2 rounded-full transition-all"
          :style="{ width: uploadProgress + '%' }"
        ></div>
      </div>
      <p class="text-center text-sm text-gray-600 mt-2">{{ uploadProgress }}%</p>
    </div>

    <!-- Messages -->
    <div v-if="successMessage" class="fixed bottom-4 right-4 bg-green-500 text-white p-4 rounded shadow-lg">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="fixed bottom-4 right-4 bg-red-500 text-white p-4 rounded shadow-lg">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const verification = ref(null)
const uploadedDocs = ref({})
const isUploading = ref(false)
const isResetting = ref(false)
const showProgress = ref(false)
const uploadProgress = ref(0)
const successMessage = ref('')
const errorMessage = ref('')

const requiredDocuments = ['ktp', 'skp', 'sertifikat']

const statusBadge = computed(() => {
  const badges = {
    pending: 'inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium',
    in_review: 'inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium',
    verified: 'inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium',
    rejected: 'inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium',
  }
  return badges[verification.value?.status] || ''
})

const hasAllDocuments = computed(() => {
  return requiredDocuments.every(doc => uploadedDocs.value[doc])
})

onMounted(() => {
  fetchVerificationStatus()
})

const fetchVerificationStatus = async () => {
  try {
    const response = await axios.get('/api/v1/doctor-verification/status')
    verification.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch verification status:', error)
  }
}

const getStatusIcon = (step) => {
  const isCompleted =
    (step === 'submitted' && verification.value) ||
    (step === 'reviewed' && verification.value?.status !== 'pending') ||
    (step === 'verified' && verification.value?.status === 'verified')

  return isCompleted
    ? 'w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white'
    : 'w-8 h-8 bg-gray-300 rounded-full'
}

const onFileSelected = (event, docType) => {
  const file = event.target.files[0]
  if (file) {
    uploadedDocs.value[docType] = file
  }
}

const onFileDrop = (event, docType) => {
  event.preventDefault()
  const file = event.dataTransfer.files[0]
  if (file) {
    uploadedDocs.value[docType] = file
  }
}

const uploadDocuments = async () => {
  isUploading.value = true
  showProgress.value = true

  try {
    for (const docType of requiredDocuments) {
      const file = uploadedDocs.value[docType]
      if (!file) continue

      const formData = new FormData()
      formData.append('document_type', docType)
      formData.append('document', file)

      const response = await axios.post(
        `/api/v1/doctor-verification/${verification.value.id}/documents`,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
          onUploadProgress: (progressEvent) => {
            uploadProgress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100)
          },
        }
      )

      uploadProgress.value += 33
    }

    successMessage.value = 'Documents uploaded successfully!'
    await new Promise(r => setTimeout(r, 2000))
    await fetchVerificationStatus()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to upload documents'
  } finally {
    isUploading.value = false
    showProgress.value = false
    uploadProgress.value = 0
  }
}

const resetVerification = async () => {
  isResetting.value = true
  try {
    await axios.post(`/api/v1/doctor-verification/${verification.value.id}/reset`)
    successMessage.value = 'Verification reset. You can now resubmit documents.'
    await new Promise(r => setTimeout(r, 2000))
    uploadedDocs.value = {}
    await fetchVerificationStatus()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to reset verification'
  } finally {
    isResetting.value = false
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>
