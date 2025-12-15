<template>
  <div v-if="success" class="rounded-lg p-4 mb-4 flex items-start gap-3" :class="successClass">
    <!-- Success Icon -->
    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
      <path
        fill-rule="evenodd"
        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
        clip-rule="evenodd"
      />
    </svg>

    <!-- Success Content -->
    <div class="flex-1">
      <h3 class="font-semibold mb-1">{{ title || 'Success' }}</h3>
      <p class="text-sm">{{ success }}</p>
    </div>

    <!-- Close Button -->
    <button
      @click="$emit('close')"
      class="text-gray-400 hover:text-gray-600 shrink-0"
    >
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path
          fill-rule="evenodd"
          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
          clip-rule="evenodd"
        />
      </svg>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  success: String,
  title: String,
})

const successClass = 'bg-green-50 text-green-800 border border-green-200'

defineEmits(['close'])
</script>
