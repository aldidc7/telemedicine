<template>
  <div class="error-container" v-if="error">
    <div class="error-message">
      <div class="error-header">
        <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span>{{ errorTitle }}</span>
        <button @click="dismiss" class="close-btn">×</button>
      </div>
      <div class="error-details" v-if="errorDetails">
        <div class="detail-item" v-for="(detail, key) in errorDetails" :key="key">
          <strong>{{ key }}:</strong> {{ detail }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  error: {
    type: [String, Object],
    default: null,
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['dismiss'])

const errorTitle = computed(() => {
  if (typeof props.error === 'string') return props.error
  return props.error?.message || 'An error occurred'
})

const errorDetails = computed(() => {
  if (typeof props.error !== 'object') return null
  return props.error?.details || null
})

const dismiss = () => {
  emit('dismiss')
}
</script>

<style scoped>
.error-container {
  margin-bottom: 1rem;
}

.error-message {
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 0.375rem;
  padding: 1rem;
}

.error-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #991b1b;
  font-weight: 500;
}

.error-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
}

.close-btn {
  margin-left: auto;
  background: none;
  border: none;
  color: #991b1b;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  opacity: 0.8;
}

.error-details {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #fecaca;
  font-size: 0.875rem;
}

.detail-item {
  padding: 0.25rem 0;
  color: #7f1d1d;
}
</style>
