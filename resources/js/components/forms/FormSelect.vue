<!-- 📁 resources/js/components/forms/FormSelect.vue -->
<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-600">*</span>
    </label>
    <select
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.value)"
      :class="[
        'w-full px-4 py-2 border rounded-lg transition',
        'focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        error ? 'border-red-300 bg-red-50' : 'border-gray-300'
      ]"
    >
      <option value="">{{ placeholder || '-- Pilih --' }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  placeholder: String,
  modelValue: [String, Number],
  options: {
    type: Array,
    required: true
  },
  required: Boolean,
  disabled: Boolean,
  error: String
})

defineEmits(['update:modelValue'])
</script>