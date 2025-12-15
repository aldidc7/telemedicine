<template>
  <div class="bg-white rounded-2xl shadow-lg p-8">
    <!-- Header with Average Rating -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Rating Dokter</h3>
        <div class="flex items-center gap-3">
          <div class="flex gap-1">
            <svg
              v-for="i in 5"
              :key="i"
              :class="[
                'w-5 h-5',
                i <= Math.round(averageRating)
                  ? 'text-yellow-400 fill-yellow-400'
                  : 'text-gray-300'
              ]"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <span class="text-2xl font-bold text-gray-900">{{ averageRating.toFixed(1) }}</span>
          <span class="text-gray-600">({{ ratings.length }} rating)</span>
        </div>
      </div>
    </div>

    <!-- No Ratings Yet -->
    <div
      v-if="ratings.length === 0"
      class="text-center py-8 text-gray-500"
    >
      <p>Belum ada rating untuk dokter ini</p>
    </div>

    <!-- Ratings List -->
    <div v-else class="space-y-6">
      <div
        v-for="rating in ratings"
        :key="rating.id"
        class="pb-6 border-b border-gray-200 last:border-b-0"
      >
        <!-- Rating Header -->
        <div class="flex items-start justify-between mb-3">
          <div>
            <p class="font-semibold text-gray-900">{{ rating.user?.name || 'Pasien' }}</p>
            <div class="flex items-center gap-2 mt-1">
              <div class="flex gap-0.5">
                <svg
                  v-for="i in 5"
                  :key="i"
                  :class="[
                    'w-4 h-4',
                    i <= rating.rating
                      ? 'text-yellow-400 fill-yellow-400'
                      : 'text-gray-300'
                  ]"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <span class="text-xs text-gray-500">{{ formatDate(rating.created_at) }}</span>
            </div>
          </div>
          <span class="text-sm font-bold text-indigo-600">{{ rating.rating }}/5</span>
        </div>

        <!-- Review Text -->
        <p
          v-if="rating.review"
          class="text-gray-700 text-sm leading-relaxed"
        >
          {{ rating.review }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ratingAPI } from '@/api/rating'

const props = defineProps({
  dokter_id: {
    type: [String, Number],
    required: true
  }
})

const ratings = ref([])
const isLoading = ref(false)

const averageRating = computed(() => {
  if (ratings.value.length === 0) return 0
  const sum = ratings.value.reduce((acc, r) => acc + r.rating, 0)
  return sum / ratings.value.length
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(async () => {
  isLoading.value = true
  try {
    const response = await ratingAPI.getDokterRatings(props.dokter_id)
    ratings.value = response.data || []
  } catch (error) {
    console.error('Failed to load ratings:', error)
  } finally {
    isLoading.value = false
  }
})
</script>
