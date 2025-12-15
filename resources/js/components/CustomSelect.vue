<!-- 📁 resources/js/components/CustomSelect.vue -->
<template>
  <div class="relative">
    <!-- Trigger Button -->
    <button
      @click="isOpen = !isOpen"
      @blur="closeDropdown"
      type="button"
      class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white transition hover:border-indigo-400 shadow-sm font-medium text-gray-900 text-left flex items-center justify-between appearance-none focus:outline-none"
    >
      <span>{{ selectedLabel }}</span>
      <svg
        :class="['w-5 h-5 text-gray-400 transition', isOpen && 'rotate-180']"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"></path>
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
      >
        <!-- Options List -->
        <ul class="max-h-64 overflow-y-auto py-1">
          <li v-for="(option, index) in options" :key="index">
            <button
              @click="selectOption(option.value, option.label)"
              :class="[
                'w-full px-4 py-3 text-left text-sm transition flex items-center gap-3',
                modelValue === option.value
                  ? 'bg-indigo-50 text-indigo-700 font-semibold'
                  : 'text-gray-700 hover:bg-gray-50'
              ]"
              type="button"
            >
              <!-- Checkmark for selected -->
              <svg
                v-if="modelValue === option.value"
                class="w-5 h-5 text-indigo-600 shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              <span v-else class="w-5 h-5 shrink-0"></span>
              <span>{{ option.label }}</span>
            </button>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  options: {
    type: Array,
    required: true
    // Expected: [{ value: '', label: 'Semua' }, { value: 'umum', label: 'Dokter Umum' }]
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

const selectedLabel = computed(() => {
  const selected = props.options.find(opt => opt.value === props.modelValue)
  return selected ? selected.label : 'Pilih...'
})

const selectOption = (value, label) => {
  emit('update:modelValue', value)
  isOpen.value = false
}

const closeDropdown = () => {
  setTimeout(() => {
    isOpen.value = false
  }, 150)
}
</script>

