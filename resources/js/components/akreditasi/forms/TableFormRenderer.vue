<template>
  <div class="table-form-renderer">
    <!-- Form Header -->
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">{{ config.label }}</h3>
        <p v-if="rowCount > 0" class="text-sm text-gray-600 mt-1">
          Total: {{ rowCount }} baris
          <span v-if="config.validations?.min_rows" class="text-gray-500">
            (minimal {{ config.validations.min_rows }} baris)
          </span>
        </p>
      </div>

      <!-- Add Row Button -->
      <button
        v-if="!readonly && config.options?.allow_add !== false"
        @click="handleAddRow"
        :disabled="isMaxRowsReached"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center gap-2 text-sm font-medium transition"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Baris
      </button>
    </div>

    <!-- Validation Messages -->
    <div v-if="validationErrors.length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-start gap-2">
        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
          <h4 class="text-sm font-medium text-red-800 mb-1">Terdapat kesalahan pada form:</h4>
          <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
            <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in config.columns"
              :key="column.name"
              :style="{ width: column.width || 'auto' }"
              class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
            >
              {{ column.label }}
              <span v-if="column.required" class="text-red-500 ml-1">*</span>
            </th>
            <th
              v-if="!readonly && config.options?.allow_delete !== false"
              class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
              style="width: 80px"
            >
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Empty State -->
          <tr v-if="rows.length === 0">
            <td :colspan="columnCount" class="px-4 py-8 text-center text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-sm">Belum ada data. Klik "Tambah Baris" untuk menambahkan data.</p>
            </td>
          </tr>

          <!-- Data Rows -->
          <tr v-for="(row, rowIndex) in rows" :key="rowIndex" class="hover:bg-gray-50">
            <td
              v-for="column in config.columns"
              :key="column.name"
              class="px-4 py-3"
            >
              <!-- Render field based on type -->
              <component
                :is="getFieldComponent(column.type)"
                :config="column"
                :modelValue="row[column.name]"
                @update:modelValue="(value) => handleUpdateCell(rowIndex, column.name, value)"
                :readonly="readonly"
                :error="getCellError(rowIndex, column.name)"
              />
            </td>

            <!-- Delete Button -->
            <td
              v-if="!readonly && config.options?.allow_delete !== false"
              class="px-4 py-3 text-center"
            >
              <button
                @click="handleDeleteRow(rowIndex)"
                class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition"
                title="Hapus baris"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary -->
    <div v-if="config.options?.show_summary && rows.length > 0" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
      <h4 class="text-sm font-medium text-gray-700 mb-2">Ringkasan</h4>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="column in numericColumns" :key="column.name">
          <p class="text-xs text-gray-600">{{ column.label }}</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ formatSummaryValue(column, calculateColumnSum(column.name)) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import TextField from '../fields/TextField.vue'
import NumberField from '../fields/NumberField.vue'
import CurrencyField from '../fields/CurrencyField.vue'
import DecimalField from '../fields/DecimalField.vue'
import SelectField from '../fields/SelectField.vue'
import DateField from '../fields/DateField.vue'
import PercentageField from '../fields/PercentageField.vue'

const props = defineProps({
  config: {
    type: Object,
    required: true
  },
  modelValue: {
    type: Object,
    default: () => ({ rows: [] })
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'validate'])

const validationErrors = ref([])
const cellErrors = ref({})

// Computed - directly from props (no internal state)
const rows = computed(() => {
  const rowsData = props.modelValue?.rows || []
  console.log('🟡 TableFormRenderer - rows computed triggered, length:', rowsData.length)
  return rowsData
})
const rowCount = computed(() => rows.value.length)

const columnCount = computed(() => {
  let count = props.config.columns.length
  if (!props.readonly && props.config.options?.allow_delete !== false) {
    count++
  }
  return count
})

const isMaxRowsReached = computed(() => {
  const maxRows = props.config.validations?.max_rows
  return maxRows && rowCount.value >= maxRows
})

const numericColumns = computed(() => {
  return props.config.columns.filter(col =>
    ['number', 'currency', 'decimal', 'percentage'].includes(col.type)
  )
})

// Field component mapping
const getFieldComponent = (type) => {
  const components = {
    text: TextField,
    number: NumberField,
    currency: CurrencyField,
    decimal: DecimalField,
    select: SelectField,
    date: DateField,
    percentage: PercentageField
  }
  return components[type] || TextField
}

// Row operations - emit to parent instead of mutating local state
const handleAddRow = () => {
  console.log('🟢 TableFormRenderer - handleAddRow called')
  console.log('  Current rows.value length:', rows.value.length)
  console.log('  Current rows.value:', rows.value)

  const newRow = {}
  props.config.columns.forEach(column => {
    newRow[column.name] = column.default || null
  })

  console.log('  New row created:', newRow)

  // Create new array with added row
  const newRows = [...rows.value, newRow]
  console.log('  New rows array length:', newRows.length)
  console.log('  Emitting update:modelValue with:', { rows: newRows })

  emit('update:modelValue', { rows: newRows })
}

const handleDeleteRow = (index) => {
  if (confirm('Apakah Anda yakin ingin menghapus baris ini?')) {
    // Create new array without deleted row
    const newRows = rows.value.filter((_, i) => i !== index)
    emit('update:modelValue', { rows: newRows })
  }
}

const handleUpdateCell = (rowIndex, columnName, value) => {
  // Create new array with updated cell
  const newRows = rows.value.map((row, index) => {
    if (index === rowIndex) {
      return { ...row, [columnName]: value }
    }
    return row
  })
  emit('update:modelValue', { rows: newRows })
}

// Validation
const validateForm = () => {
  validationErrors.value = []
  cellErrors.value = {}

  const minRows = props.config.validations?.min_rows
  const maxRows = props.config.validations?.max_rows

  // Validate row count
  if (minRows && rowCount.value < minRows) {
    validationErrors.value.push(`Minimal ${minRows} baris data harus diisi`)
  }

  if (maxRows && rowCount.value > maxRows) {
    validationErrors.value.push(`Maksimal ${maxRows} baris data`)
  }

  // Validate each row
  rows.value.forEach((row, rowIndex) => {
    props.config.columns.forEach(column => {
      const value = row[column.name]

      // Required validation
      if (column.required && (value === null || value === undefined || value === '')) {
        const errorKey = `${rowIndex}.${column.name}`
        cellErrors.value[errorKey] = `${column.label} wajib diisi`
      }
    })
  })

  const isValid = validationErrors.value.length === 0 && Object.keys(cellErrors.value).length === 0
  emit('validate', { isValid, errors: validationErrors.value })

  return isValid
}

// Watch for changes and validate
watch(() => props.modelValue, () => {
  validateForm()
}, { deep: true })

const getCellError = (rowIndex, columnName) => {
  return cellErrors.value[`${rowIndex}.${columnName}`] || null
}

// Summary calculations
const calculateColumnSum = (columnName) => {
  return rows.value.reduce((sum, row) => {
    const value = parseFloat(row[columnName]) || 0
    return sum + value
  }, 0)
}

const formatSummaryValue = (column, value) => {
  if (column.type === 'currency') {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(value)
  }

  if (column.type === 'percentage') {
    return `${value.toFixed(2)}%`
  }

  if (column.type === 'decimal') {
    return value.toFixed(2)
  }

  return value
}

// Expose validation method
defineExpose({
  validate: validateForm
})
</script>

<style scoped>
.table-form-renderer {
  /* Custom styles */
}
</style>
