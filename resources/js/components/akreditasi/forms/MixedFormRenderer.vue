<template>
  <div class="mixed-form-renderer space-y-6">
    <div v-for="(section, index) in config.sections" :key="index" class="border border-gray-200 rounded-lg p-6">
      <h4 class="text-md font-semibold text-gray-900 mb-4">{{ section.title }}</h4>

      <!-- Render section based on type -->
      <component
        :is="getSectionComponent(section.type)"
        :config="section"
        :modelValue="formData[section.title] || {}"
        @update:modelValue="(data) => updateSection(section.title, data)"
        :readonly="readonly"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import TableFormRenderer from './TableFormRenderer.vue'
import NarrativeFormRenderer from './NarrativeFormRenderer.vue'
import ChecklistFormRenderer from './ChecklistFormRenderer.vue'

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

const getSectionComponent = (type) => {
  const components = {
    table: TableFormRenderer,
    narrative: NarrativeFormRenderer,
    checklist: ChecklistFormRenderer
  }
  return components[type] || 'div'
}

const updateSection = (sectionTitle, data) => {
  formData.value[sectionTitle] = data
}
</script>
