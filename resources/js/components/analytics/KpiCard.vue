<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm font-medium">{{ label }}</p>
        <p class="text-4xl font-bold text-gray-900 mt-2">{{ value }}</p>
      </div>
      <div :class="[
        'w-16 h-16 rounded-2xl flex items-center justify-center',
        {
          'bg-blue-100': color === 'blue',
          'bg-green-100': color === 'green',
          'bg-purple-100': color === 'purple',
          'bg-emerald-100': color === 'emerald',
          'bg-orange-100': color === 'orange',
          'bg-red-100': color === 'red',
        }
      ]">
        <!-- Icon Container - Support both SVG and emoji for backwards compatibility -->
        <div v-if="isEmoji" class="text-3xl">{{ icon }}</div>
        <svg v-else class="w-8 h-8" :class="iconColorClass" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue'
  }
})

const isEmoji = computed(() => {
  // Check if icon is an emoji (single character or common emoji)
  return props.icon && props.icon.length <= 2
})

const iconColorClass = computed(() => {
  const colorMap = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    purple: 'text-purple-600',
    emerald: 'text-emerald-600',
    orange: 'text-orange-600',
    red: 'text-red-600',
  }
  return colorMap[props.color] || 'text-blue-600'
})
</script>
</script>
