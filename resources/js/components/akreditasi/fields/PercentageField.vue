<template>
  <div class="percentage-field">
    <div class="relative">
      <input
        :value="modelValue"
        @input="handleInput"
        :min="0"
        :max="100"
        :placeholder="config.placeholder || '0'"
        :readonly="readonly"
        :class="inputClass"
        type="number"
        step="0.01"
        class="w-full pl-3 pr-8 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 transition"
      />
      <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">%</span>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  config: {
    type: Object,
    required: true
  },
  modelValue: {
    type: [Number, null],
    default: null
  },
  readonly: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const inputClass = computed(() => {
  if (props.error) {
    return 'border-red-300 focus:border-red-500 focus:ring-red-500'
  }
  if (props.readonly) {
    return 'border-gray-200 bg-gray-50 cursor-not-allowed'
  }
  return 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
})

const handleInput = (event) => {
  const value = event.target.value
  let numValue = value === '' ? null : parseFloat(value)

  // Clamp between 0 and 100
  if (numValue !== null) {
    numValue = Math.max(0, Math.min(100, numValue))
  }

  emit('update:modelValue', numValue)
}
</script>
