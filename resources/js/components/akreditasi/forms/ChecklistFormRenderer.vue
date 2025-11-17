<template>
  <div class="checklist-form-renderer">
    <div class="space-y-3">
      <div
        v-for="(item, index) in config.items"
        :key="index"
        class="flex items-start gap-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
      >
        <input
          type="checkbox"
          :checked="formData.items?.[index]?.checked"
          @change="toggleItem(index, $event.target.checked)"
          :disabled="readonly"
          class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        />
        <div class="flex-1">
          <label class="text-sm font-medium text-gray-900">{{ item.label }}</label>
          <p v-if="item.description" class="text-xs text-gray-600 mt-1">{{ item.description }}</p>
        </div>
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
    default: () => ({ items: [] })
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

const toggleItem = (index, checked) => {
  if (!formData.value.items) {
    formData.value.items = []
  }
  if (!formData.value.items[index]) {
    formData.value.items[index] = {}
  }
  formData.value.items[index].checked = checked
}
</script>
