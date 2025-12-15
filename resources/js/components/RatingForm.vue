<!-- 📁 resources/js/components/RatingForm.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg p-8">
    <h3 class="text-2xl font-bold text-gray-900 mb-6">Berikan Rating</h3>

    <!-- Star Rating -->
    <div class="mb-8">
      <label class="block text-sm font-semibold text-gray-700 mb-4">Rating (1-5 Bintang)</label>
      <div class="flex gap-3">
        <button
          v-for="star in 5"
          :key="star"
          @click="form.rating = star"
          class="transition transform hover:scale-110"
        >
          <svg
            :class="[
              'w-10 h-10 transition',
              star <= form.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'
            ]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
            />
          </svg>
        </button>
      </div>
      <p v-if="form.rating" class="text-sm text-gray-600 mt-2">
        {{ form.rating }} dari 5 bintang
      </p>
    </div>

    <!-- Review Text -->
    <div class="mb-8">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Ulasan (Opsional)</label>
      <textarea
        v-model="form.review"
        placeholder="Bagikan pengalaman Anda dengan dokter ini..."
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
        rows="4"
        maxlength="1000"
      ></textarea>
      <p class="text-xs text-gray-500 mt-1">{{ form.review.length }}/1000 karakter</p>
    </div>

    <!-- Buttons -->
    <div class="flex gap-3">
      <button
        @click="submitRating"
        :disabled="!form.rating || isLoading"
        class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition"
      >
        {{ isLoading ? 'Menyimpan...' : 'Simpan Rating' }}
      </button>
      <button
        @click="$emit('close')"
        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-50 transition"
      >
        Batal
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
      {{ error }}
    </div>

    <!-- Success Message -->
    <div v-if="success" class="mt-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
      Rating berhasil disimpan!
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ratingAPI } from '@/api/rating'

const emit = defineEmits(['close', 'success'])

const props = defineProps({
  konsultasi_id: {
    type: [String, Number],
    required: true
  }
})

const form = reactive({
  rating: 0,
  review: ''
})

const isLoading = ref(false)
const error = ref('')
const success = ref(false)

const submitRating = async () => {
  if (!form.rating) {
    error.value = 'Pilih rating terlebih dahulu'
    return
  }

  isLoading.value = true
  error.value = ''
  success.value = false

  try {
    await ratingAPI.create({
      konsultasi_id: props.konsultasi_id,
      rating: form.rating,
      review: form.review || null
    })

    success.value = true
    setTimeout(() => {
      emit('success')
      emit('close')
    }, 1500)
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menyimpan rating'
  } finally {
    isLoading.value = false
  }
}
</script>
