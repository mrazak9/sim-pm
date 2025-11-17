<template>
  <div class="currency-field">
    <div class="relative">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">Rp</span>
      <input
        :value="formattedValue"
        @input="handleInput"
        @blur="formatOnBlur"
        :placeholder="config.placeholder || '0'"
        :readonly="readonly"
        :class="inputClass"
        type="text"
        inputmode="numeric"
        class="w-full pl-10 pr-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 transition"
      />
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

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

const formattedValue = ref('')
const isFormatted = ref(true)

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (isFormatted.value) {
    formattedValue.value = formatCurrency(newValue)
  }
}, { immediate: true })

const inputClass = computed(() => {
  if (props.error) {
    return 'border-red-300 focus:border-red-500 focus:ring-red-500'
  }
  if (props.readonly) {
    return 'border-gray-200 bg-gray-50 cursor-not-allowed'
  }
  return 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
})

const formatCurrency = (value) => {
  if (value === null || value === undefined || value === '') return ''

  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

const handleInput = (event) => {
  isFormatted.value = false
  const value = event.target.value.replace(/\D/g, '') // Remove non-digits
  formattedValue.value = value
  emit('update:modelValue', value === '' ? null : parseFloat(value))
}

const formatOnBlur = () => {
  isFormatted.value = true
  formattedValue.value = formatCurrency(props.modelValue)
}
</script>
