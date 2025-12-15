<template>
  <div class="relative inline-block group">
    <!-- Tooltip Trigger -->
    <slot />

    <!-- Tooltip Content -->
    <div
      :class="[
        'absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap',
        'shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none group-hover:pointer-events-auto',
        'before:content-[\'\'] before:absolute before:bottom-0 before:left-1/2 before:-translate-x-1/2 before:translate-y-full',
        'before:border-6 before:border-t-gray-900 before:border-x-transparent before:border-b-transparent',
        position === 'top' ? 'bottom-full mb-2' : position === 'bottom' ? 'top-full mt-2' : position === 'left' ? 'right-full mr-2 translate-x-0 translate-y-0' : 'left-full ml-2 translate-x-0 translate-y-0'
      ]"
    >
      {{ text }}
    </div>

    <!-- Help Icon (Optional) -->
    <svg
      v-if="showIcon"
      class="w-4 h-4 text-gray-400 inline-block ml-1 cursor-help"
      fill="currentColor"
      viewBox="0 0 20 20"
    >
      <path
        fill-rule="evenodd"
        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
        clip-rule="evenodd"
      />
    </svg>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    required: true
  },
  position: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value)
  },
  showIcon: {
    type: Boolean,
    default: true
  }
})
</script>
