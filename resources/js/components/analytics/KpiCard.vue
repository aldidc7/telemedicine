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
        {{ icon }}
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
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
</script>
</script>
