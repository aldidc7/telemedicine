<!-- 📁 resources/js/components/forms/FormInput.vue -->
<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-600">*</span>
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="[
        'w-full px-4 py-2 border rounded-lg transition',
        'focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        error ? 'border-red-300 bg-red-50' : 'border-gray-300'
      ]"
    />
    <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
    <p v-if="hint" class="text-gray-500 text-xs mt-1">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'text'
  },
  label: String,
  placeholder: String,
  modelValue: [String, Number],
  required: Boolean,
  disabled: Boolean,
  error: String,
  hint: String
})

defineEmits(['update:modelValue'])
</script>