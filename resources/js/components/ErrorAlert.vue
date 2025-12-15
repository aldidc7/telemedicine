<template>
  <div v-if="error" class="rounded-lg p-4 mb-4 flex items-start gap-3" :class="errorClass">
    <!-- Error Icon -->
    <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
      <path
        fill-rule="evenodd"
        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
        clip-rule="evenodd"
      />
    </svg>
    
    <!-- Error Content -->
    <div class="flex-1">
      <h3 class="font-semibold mb-1">{{ title || 'Error' }}</h3>
      <p class="text-sm">{{ error }}</p>
      <div v-if="errors && Object.keys(errors).length > 0" class="mt-2 text-sm">
        <ul class="list-disc list-inside space-y-1">
          <li v-for="(msgs, field) in errors" :key="field">
            <strong>{{ field }}:</strong> {{ msgs.join(', ') }}
          </li>
        </ul>
      </div>
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
import { computed } from 'vue'

const props = defineProps({
  error: String,
  errors: Object,
  title: String,
  type: {
    type: String,
    default: 'error', // error, warning, info
  },
})

const typeColors = {
  error: 'bg-red-50 text-red-800 border border-red-200',
  warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
  info: 'bg-blue-50 text-blue-800 border border-blue-200',
}

const errorClass = computed(() => typeColors[props.type] || typeColors.error)

defineEmits(['close'])
</script>
