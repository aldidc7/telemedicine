<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm font-medium">{{ label }}</p>
        <p class="text-4xl font-bold text-gray-900 mt-2">{{ value }}</p>
      </div>
      <div :class="[
        'w-16 h-16 rounded-2xl flex items-center justify-center text-3xl',
        {
          'bg-blue-100': color === 'blue',
          'bg-green-100': color === 'green',
          'bg-purple-100': color === 'purple',
          'bg-emerald-100': color === 'emerald',
          'bg-orange-100': color === 'orange',
          'bg-red-100': color === 'red',
        }
      ]">
        <i v-if="isIconClass" :class="['fas', ...iconClass, getColorClass()]"></i>
        <span v-else>{{ icon }}</span>
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

const isIconClass = computed(() => {
  return props.icon.startsWith('fa-')
})

const iconClass = computed(() => {
  if (!isIconClass.value) return []
  return props.icon.split(' ').filter(cls => cls.startsWith('fa-'))
})

const getColorClass = () => {
  const colorMap = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    purple: 'text-purple-600',
    emerald: 'text-emerald-600',
    orange: 'text-orange-600',
    red: 'text-red-600',
  }
  return colorMap[props.color] || colorMap.blue
}
</script>
