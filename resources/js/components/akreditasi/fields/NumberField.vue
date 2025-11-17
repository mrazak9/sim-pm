<template>
  <div class="number-field">
    <input
      :value="modelValue"
      @input="handleInput"
      :min="config.min"
      :max="config.max"
      :placeholder="config.placeholder || '0'"
      :readonly="readonly"
      :class="inputClass"
      type="number"
      step="1"
      class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 transition"
    />
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
  emit('update:modelValue', value === '' ? null : parseFloat(value))
}
</script>
