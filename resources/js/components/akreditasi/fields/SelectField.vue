<template>
  <div class="select-field">
    <select
      :value="modelValue"
      @change="handleChange"
      :disabled="readonly"
      :class="inputClass"
      class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 transition"
    >
      <option value="">-- Pilih {{ config.label }} --</option>
      <option
        v-for="(label, value) in config.options"
        :key="value"
        :value="value"
      >
        {{ label }}
      </option>
    </select>
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
    type: [String, Number, null],
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

const handleChange = (event) => {
  emit('update:modelValue', event.target.value || null)
}
</script>
