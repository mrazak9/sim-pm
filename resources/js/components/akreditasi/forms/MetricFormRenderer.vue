<template>
  <div class="metric-form-renderer">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="metric in config.metrics" :key="metric.name" class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
          {{ metric.label }}
          <span v-if="metric.required" class="text-red-500 ml-1">*</span>
        </label>

        <input
          :value="formData[metric.name]"
          @input="updateMetric(metric.name, $event.target.value)"
          :type="getInputType(metric.type)"
          :readonly="readonly"
          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <p v-if="metric.help_text" class="text-xs text-gray-500">{{ metric.help_text }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  config: {
    type: Object,
    required: true
  },
  modelValue: {
    type: Object,
    default: () => ({})
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'validate'])

const formData = ref({ ...props.modelValue })

watch(() => props.modelValue, (newValue) => {
  formData.value = { ...newValue }
}, { deep: true })

watch(formData, (newData) => {
  emit('update:modelValue', newData)
}, { deep: true })

const updateMetric = (name, value) => {
  formData.value[name] = value
}

const getInputType = (type) => {
  const typeMap = {
    number: 'number',
    currency: 'number',
    decimal: 'number',
    percentage: 'number',
    text: 'text',
    date: 'date'
  }
  return typeMap[type] || 'text'
}
</script>
