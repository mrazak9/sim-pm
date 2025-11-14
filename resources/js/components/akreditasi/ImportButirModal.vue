<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50 p-4"
      @click.self="handleClose"
    >
      <div class="relative w-full max-w-2xl rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
            Import Butir Akreditasi
          </h3>
          <button
            @click="handleClose"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white"
          >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Info Alert -->
          <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-start">
              <svg class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-400">Informasi</h4>
                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                  Import butir akan meng-copy semua butir sesuai instrumen periode ini.
                  <span v-if="butirCount > 0" class="font-semibold">
                    Periode ini sudah memiliki {{ butirCount }} butir. Hapus butir existing terlebih dahulu jika ingin import ulang.
                  </span>
                </p>
              </div>
            </div>
          </div>

          <!-- Import Options -->
          <div class="mb-6">
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Pilih Sumber Import
            </label>
            <div class="grid grid-cols-2 gap-4">
              <button
                @click="selectedOption = 'template'"
                :class="[
                  'flex flex-col items-center rounded-lg border-2 p-4 transition-all',
                  selectedOption === 'template'
                    ? 'border-blue-600 bg-blue-50 dark:border-blue-500 dark:bg-blue-900/20'
                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500'
                ]"
              >
                <svg class="mb-2 h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium text-gray-900 dark:text-white">Template</span>
                <span class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                  Copy dari template {{ periode?.instrumen }}
                </span>
              </button>

              <button
                @click="selectedOption = 'periode'"
                :class="[
                  'flex flex-col items-center rounded-lg border-2 p-4 transition-all',
                  selectedOption === 'periode'
                    ? 'border-blue-600 bg-blue-50 dark:border-blue-500 dark:bg-blue-900/20'
                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500'
                ]"
              >
                <svg class="mb-2 h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                </svg>
                <span class="font-medium text-gray-900 dark:text-white">Periode Lain</span>
                <span class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                  Copy dari periode existing
                </span>
              </button>
            </div>
          </div>

          <!-- Template Option Content -->
          <div v-if="selectedOption === 'template'" class="space-y-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Template Tersedia</p>
                  <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    Instrumen: {{ periode?.instrumen }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ templateCount }}
                  </p>
                  <p class="text-xs text-gray-600 dark:text-gray-400">Butir</p>
                </div>
              </div>
            </div>

            <div v-if="templateCount === 0" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
              <p class="text-sm text-yellow-800 dark:text-yellow-200">
                Belum ada template butir untuk instrumen {{ periode?.instrumen }}.
                Silakan buat template butir terlebih dahulu di menu Butir Akreditasi.
              </p>
            </div>
          </div>

          <!-- Periode Option Content -->
          <div v-if="selectedOption === 'periode'" class="space-y-4">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Pilih Periode Sumber
              </label>
              <select
                v-model="selectedSourcePeriode"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              >
                <option :value="null">-- Pilih Periode --</option>
                <option
                  v-for="p in availablePeriodes"
                  :key="p.id"
                  :value="p.id"
                >
                  {{ p.nama }} ({{ p.butir_count }} butir)
                </option>
              </select>
            </div>

            <div v-if="availablePeriodes.length === 0" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
              <p class="text-sm text-yellow-800 dark:text-yellow-200">
                Tidak ada periode lain yang memiliki butir yang bisa di-copy.
              </p>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-200">{{ errorMessage }}</p>
          </div>

          <!-- Success Message -->
          <div v-if="successMessage" class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <p class="text-sm text-green-800 dark:text-green-200">{{ successMessage }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end space-x-3 border-t border-gray-200 p-6 dark:border-gray-700">
          <button
            @click="handleClose"
            :disabled="importing"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
          >
            Batal
          </button>
          <button
            @click="handleImport"
            :disabled="importing || butirCount > 0 || !canImport"
            class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          >
            <span v-if="importing" class="flex items-center">
              <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Importing...
            </span>
            <span v-else>Import Butir</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  periode: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const {
  getPeriodeList,
  copyButirFromTemplate,
  copyButirFromPeriode,
  getButirCount,
  getTemplateCount
} = useAkreditasiApi()

const selectedOption = ref('template')
const selectedSourcePeriode = ref(null)
const templateCount = ref(0)
const butirCount = ref(0)
const availablePeriodes = ref([])
const importing = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const canImport = computed(() => {
  if (butirCount.value > 0) return false
  if (selectedOption.value === 'template') {
    return templateCount.value > 0
  }
  if (selectedOption.value === 'periode') {
    return selectedSourcePeriode.value !== null
  }
  return false
})

const fetchData = async () => {
  try {
    // Get butir count for current periode
    const butirResponse = await getButirCount(props.periode.id)
    butirCount.value = butirResponse.data.count

    // Get template count
    if (props.periode.instrumen) {
      const templateResponse = await getTemplateCount(props.periode.instrumen)
      templateCount.value = templateResponse.data.count
    }

    // Get available periodes with butir
    const periodeResponse = await getPeriodeList({ per_page: 100 })
    availablePeriodes.value = periodeResponse.data
      .filter(p => p.id !== props.periode.id && p.butir_count > 0)
      .map(p => ({
        id: p.id,
        nama: p.nama,
        instrumen: p.instrumen,
        butir_count: p.butir_count || 0
      }))
  } catch (error) {
    console.error('Failed to fetch data:', error)
  }
}

const handleImport = async () => {
  if (!canImport.value) return

  importing.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    let response

    if (selectedOption.value === 'template') {
      response = await copyButirFromTemplate(props.periode.id)
    } else {
      response = await copyButirFromPeriode(props.periode.id, selectedSourcePeriode.value)
    }

    if (response.success) {
      successMessage.value = `Berhasil import ${response.data.copied_count} butir!`
      setTimeout(() => {
        emit('success')
        handleClose()
      }, 1500)
    } else {
      errorMessage.value = response.message || 'Import gagal'
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message || 'Terjadi kesalahan saat import'
  } finally {
    importing.value = false
  }
}

const handleClose = () => {
  if (!importing.value) {
    selectedOption.value = 'template'
    selectedSourcePeriode.value = null
    errorMessage.value = ''
    successMessage.value = ''
    emit('close')
  }
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchData()
  }
})

onMounted(() => {
  if (props.isOpen) {
    fetchData()
  }
})
</script>
