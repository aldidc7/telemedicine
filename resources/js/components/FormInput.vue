<template>
  <div class="space-y-2">
    <!-- Label -->
    <label v-if="label" :for="id" class="block text-sm font-semibold text-gray-800">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Input Container -->
    <div class="relative">
      <!-- Input Field -->
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        :class="[
          'w-full px-4 py-3 rounded-xl border-2 font-medium transition-all',
          'focus:outline-none focus:ring-2 focus:ring-offset-0',
          error
            ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-300'
            : success
            ? 'border-green-500 bg-green-50 text-green-900 focus:ring-green-300'
            : 'border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-300',
          disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-text'
        ]"
      />

      <!-- Status Icons -->
      <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
        <svg
          v-if="success && !error"
          class="w-5 h-5 text-green-500 animate-slideDown"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else-if="error"
          class="w-5 h-5 text-red-500 animate-slideDown"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
    </div>

    <!-- Help Text & Error Message -->
    <transition name="slideUp">
      <p
        v-if="error"
        class="text-sm font-medium text-red-600 flex items-center gap-1.5 animate-slideDown"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
        {{ error }}
      </p>
      <p v-else-if="hint && !success" class="text-xs text-gray-500">
        {{ hint }}
      </p>
      <p v-else-if="success && successMessage" class="text-sm font-medium text-green-600 flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        {{ successMessage }}
      </p>
    </transition>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  success: {
    type: Boolean,
    default: false
  },
  successMessage: {
    type: String,
    default: 'Terlihat bagus!'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substr(2, 9)}`
  }
})

defineEmits(['update:modelValue', 'blur', 'focus'])
</script>

<style scoped>
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slideUp-enter-active {
  animation: slideUp 0.2s ease-out;
}

.slideUp-leave-active {
  animation: slideUp 0.2s ease-out reverse;
}
</style>
