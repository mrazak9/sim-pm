<template>
  <div class="dynamic-form-renderer">
    <!-- Show form type badge -->
    <div v-if="formConfig" class="mb-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-gray-700">Tipe Form:</span>
        <span :class="formTypeBadgeClass" class="px-3 py-1 rounded-full text-xs font-semibold">
          {{ formTypeLabel }}
        </span>
      </div>

      <!-- Help text -->
      <button
        v-if="formConfig.help_text"
        @click="showHelp = !showHelp"
        class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Bantuan</span>
      </button>
    </div>

    <!-- Help text panel -->
    <div v-if="showHelp && formConfig?.help_text" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <div class="flex items-start gap-2">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-sm text-blue-900">
          {{ formConfig.help_text }}
        </div>
      </div>
    </div>

    <!-- Render appropriate form based on type -->
    <div v-if="formConfig">
      <!-- Table Form -->
      <TableFormRenderer
        v-if="formConfig.type === 'table'"
        :config="formConfig"
        :modelValue="formData"
        @update:modelValue="handleFormDataChange"
        @validate="handleValidation"
      />

      <!-- Narrative Form -->
      <NarrativeFormRenderer
        v-else-if="formConfig.type === 'narrative'"
        :config="formConfig"
        :modelValue="formData"
        @update:modelValue="handleFormDataChange"
        @validate="handleValidation"
      />

      <!-- Checklist Form -->
      <ChecklistFormRenderer
        v-else-if="formConfig.type === 'checklist'"
        :config="formConfig"
        :modelValue="formData"
        @update:modelValue="handleFormDataChange"
        @validate="handleValidation"
      />

      <!-- Metric Form -->
      <MetricFormRenderer
        v-else-if="formConfig.type === 'metric'"
        :config="formConfig"
        :modelValue="formData"
        @update:modelValue="handleFormDataChange"
        @validate="handleValidation"
      />

      <!-- Mixed Form -->
      <MixedFormRenderer
        v-else-if="formConfig.type === 'mixed'"
        :config="formConfig"
        :modelValue="formData"
        @update:modelValue="handleFormDataChange"
        @validate="handleValidation"
      />

      <!-- Unsupported form type -->
      <div v-else class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-center gap-2 text-yellow-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <span class="font-medium">Tipe form tidak didukung: {{ formConfig.type }}</span>
        </div>
      </div>
    </div>

    <!-- No form config -->
    <div v-else class="p-6 bg-gray-50 border border-gray-200 rounded-lg">
      <div class="text-center text-gray-600">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-sm">Tidak ada konfigurasi form untuk butir ini.</p>
        <p class="text-xs text-gray-500 mt-1">Gunakan input teks biasa untuk mengisi butir ini.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import TableFormRenderer from './forms/TableFormRenderer.vue'
import NarrativeFormRenderer from './forms/NarrativeFormRenderer.vue'
import ChecklistFormRenderer from './forms/ChecklistFormRenderer.vue'
import MetricFormRenderer from './forms/MetricFormRenderer.vue'
import MixedFormRenderer from './forms/MixedFormRenderer.vue'

const props = defineProps({
  formConfig: {
    type: Object,
    default: null
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

const emit = defineEmits(['update:modelValue', 'validate', 'completion'])

const showHelp = ref(false)
const formData = ref({ ...props.modelValue })

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  formData.value = { ...newValue }
}, { deep: true })

// Form type display helpers
const formTypeLabel = computed(() => {
  if (!props.formConfig) return 'Tidak ada'

  const labels = {
    table: 'Tabel Data',
    narrative: 'Narasi',
    checklist: 'Checklist',
    metric: 'Metrik',
    mixed: 'Campuran'
  }

  return labels[props.formConfig.type] || props.formConfig.type
})

const formTypeBadgeClass = computed(() => {
  if (!props.formConfig) return 'bg-gray-100 text-gray-800'

  const classes = {
    table: 'bg-blue-100 text-blue-800',
    narrative: 'bg-purple-100 text-purple-800',
    checklist: 'bg-green-100 text-green-800',
    metric: 'bg-orange-100 text-orange-800',
    mixed: 'bg-indigo-100 text-indigo-800'
  }

  return classes[props.formConfig.type] || 'bg-gray-100 text-gray-800'
})

// Event handlers
const handleFormDataChange = (newData) => {
  formData.value = newData
  emit('update:modelValue', newData)
}

const handleValidation = (validationResult) => {
  emit('validate', validationResult)
}
</script>

<style scoped>
.dynamic-form-renderer {
  /* Container styles */
}
</style>
