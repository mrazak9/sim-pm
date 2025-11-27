<template>
  <div class="butir-data-table-form">
    <!-- Loading State -->
    <div v-if="initialLoading" class="flex justify-center items-center py-12">
      <div class="text-center">
        <svg class="animate-spin h-10 w-10 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600">Memuat data...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-6 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
          <h4 class="text-sm font-medium text-red-800 mb-1">Gagal memuat data</h4>
          <p class="text-sm text-red-700">{{ error }}</p>
          <button
            type="button"
            @click="loadData"
            class="mt-3 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
          >
            Coba Lagi
          </button>
        </div>
      </div>
    </div>

    <!-- No Mapping State -->
    <div v-else-if="!formConfig" class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="flex-1">
          <h4 class="text-sm font-medium text-yellow-800 mb-1">Mapping belum di-setup</h4>
          <p class="text-sm text-yellow-700">Butir ini belum memiliki column mapping. Silakan setup terlebih dahulu.</p>
        </div>
      </div>
    </div>

    <!-- Table Form -->
    <div v-else>
      <TableFormRenderer
        :config="formConfig"
        :modelValue="formData"
        @update:modelValue="handleDataChange"
        :readonly="readonly"
        @validate="handleValidation"
      />

      <!-- Save Actions -->
      <div v-if="!readonly && hasChanges" class="mt-6 flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center gap-2 text-blue-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-medium">Terdapat perubahan yang belum disimpan</span>
        </div>
        <div class="flex gap-2">
          <button
            type="button"
            @click="handleDiscard"
            :disabled="saving"
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 text-sm font-medium"
          >
            Batalkan
          </button>
          <button
            type="button"
            @click="handleSave"
            :disabled="saving || !isValid"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 text-sm font-medium flex items-center gap-2"
          >
            <svg v-if="saving" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ saving ? 'Menyimpan...' : 'Simpan' }}</span>
          </button>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="showSuccessMessage" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center gap-2 text-green-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm font-medium">Data berhasil disimpan</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useButirData } from '@/composables/useButirData'
import TableFormRenderer from './forms/TableFormRenderer.vue'

const props = defineProps({
  butirId: {
    type: Number,
    required: true
  },
  pengisianButirId: {
    type: Number,
    required: true
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['saved', 'error'])

const {
  data,
  mappings,
  loading,
  error,
  fetchMappings,
  fetchData,
  syncData,
  getFormConfigFromMappings
} = useButirData()

const initialLoading = ref(true)
const saving = ref(false)
const localData = ref([])
const originalData = ref([])
const isValid = ref(true)
const showSuccessMessage = ref(false)

// Form config generated from mappings
const formConfig = computed(() => getFormConfigFromMappings.value)

// Transform localData (array) to format expected by TableFormRenderer (object with rows)
const formData = computed(() => {
  console.log('🟠 ButirDataTableForm - formData computed triggered')
  console.log('  localData.value length:', localData.value.length)
  const data = { rows: localData.value }
  console.log('  Returning formData:', data)
  return data
})

// Check if data has changes
const hasChanges = computed(() => {
  return JSON.stringify(localData.value) !== JSON.stringify(originalData.value)
})

/**
 * Load mappings and data
 */
const loadData = async () => {
  initialLoading.value = true
  error.value = null

  try {
    // Fetch mappings first
    await fetchMappings(props.butirId)

    // Then fetch data
    const fetchedData = await fetchData(props.pengisianButirId)

    // Set local data
    localData.value = fetchedData || []
    originalData.value = JSON.parse(JSON.stringify(fetchedData || []))

  } catch (err) {
    console.error('Failed to load data:', err)
  } finally {
    initialLoading.value = false
  }
}

/**
 * Handle data change from TableFormRenderer
 * TableFormRenderer emits { rows: [] } format
 */
const handleDataChange = (newData) => {
  console.log('🔵 ButirDataTableForm - handleDataChange called')
  console.log('  newData:', newData)
  console.log('  newData.rows length:', newData?.rows?.length)
  console.log('  localData BEFORE:', localData.value.length, 'rows')

  localData.value = newData?.rows || []

  console.log('  localData AFTER:', localData.value.length, 'rows')
  console.log('  formData computed:', formData.value)
}

/**
 * Handle validation from TableFormRenderer
 */
const handleValidation = (validationResult) => {
  isValid.value = validationResult.isValid
}

/**
 * Save data to API using sync (delete all + recreate)
 */
const handleSave = async () => {
  if (!isValid.value) {
    return
  }

  saving.value = true
  showSuccessMessage.value = false

  try {
    // Sync data (delete all existing + create new)
    await syncData(props.pengisianButirId, localData.value)

    // Update original data
    originalData.value = JSON.parse(JSON.stringify(localData.value))

    // Show success message
    showSuccessMessage.value = true
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 3000)

    // Emit saved event
    emit('saved', localData.value)

  } catch (err) {
    console.error('Failed to save data:', err)
    emit('error', err.response?.data?.message || 'Gagal menyimpan data')
  } finally {
    saving.value = false
  }
}

/**
 * Discard changes
 */
const handleDiscard = () => {
  localData.value = JSON.parse(JSON.stringify(originalData.value))
}

// Load data on mount
onMounted(() => {
  loadData()
})

// Reload when butirId or pengisianButirId changes
watch([() => props.butirId, () => props.pengisianButirId], () => {
  loadData()
})
</script>
