<template>
  <div class="narrative-form-renderer space-y-6">
    <!-- Form Header -->
    <div v-if="config.label">
      <h3 class="text-lg font-semibold text-gray-900">{{ config.label }}</h3>
    </div>

    <!-- Narrative Fields -->
    <div v-for="field in config.fields" :key="field.name" class="space-y-2">
      <label class="block text-sm font-medium text-gray-700">
        {{ field.label }}
        <span v-if="field.required" class="text-red-500 ml-1">*</span>
      </label>

      <!-- Help Text -->
      <p v-if="field.help_text" class="text-xs text-gray-500">
        {{ field.help_text }}
      </p>

      <!-- Rich Text Editor Placeholder -->
      <div class="border border-gray-300 rounded-lg p-4 min-h-[200px] bg-white">
        <textarea
          :value="formData[field.name]"
          @input="updateField(field.name, $event.target.value)"
          :placeholder="`Tuliskan ${field.label.toLowerCase()}...`"
          :readonly="readonly"
          class="w-full h-full min-h-[180px] focus:outline-none resize-none"
        />
      </div>

      <!-- Character Count -->
      <div class="flex justify-between text-xs text-gray-500">
        <span v-if="field.min_length">Minimal: {{ field.min_length }} karakter</span>
        <span v-if="field.max_length">
          {{ (formData[field.name] || '').length }} / {{ field.max_length }} karakter
        </span>
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

const updateField = (fieldName, value) => {
  formData.value[fieldName] = value
}
</script>
