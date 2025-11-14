<template>
  <MainLayout>
    <div>
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit Butir Akreditasi' : 'Tambah Butir Akreditasi' }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Isi form berikut untuk {{ isEdit ? 'mengupdate' : 'membuat' }} butir akreditasi
        </p>
      </div>

      <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Tipe Butir -->
          <div class="rounded-lg border-2 border-purple-200 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-900/20">
            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
              Tipe Butir <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <label
                :class="[
                  'flex cursor-pointer items-center rounded-lg border-2 p-4 transition-all',
                  butirType === 'template'
                    ? 'border-purple-600 bg-purple-100 dark:border-purple-500 dark:bg-purple-900/40'
                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500'
                ]"
              >
                <input
                  type="radio"
                  v-model="butirType"
                  value="template"
                  class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500"
                />
                <div class="ml-3">
                  <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Template</span>
                  </div>
                  <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    Master butir yang bisa di-copy ke berbagai periode
                  </p>
                </div>
              </label>

              <label
                :class="[
                  'flex cursor-pointer items-center rounded-lg border-2 p-4 transition-all',
                  butirType === 'periode'
                    ? 'border-blue-600 bg-blue-100 dark:border-blue-500 dark:bg-blue-900/40'
                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500'
                ]"
              >
                <input
                  type="radio"
                  v-model="butirType"
                  value="periode"
                  class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <div class="ml-3">
                  <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Per-Periode</span>
                  </div>
                  <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    Butir untuk periode akreditasi spesifik
                  </p>
                </div>
              </label>
            </div>
          </div>

          <!-- Periode Selection (only for per-periode type) -->
          <div v-if="butirType === 'periode'" class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Periode Akreditasi <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.periode_akreditasi_id"
              required
              class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="handlePeriodeChange"
            >
              <option :value="null">Pilih Periode</option>
              <option v-for="periode in periodeList" :key="periode.id" :value="periode.id">
                {{ periode.nama }} ({{ periode.instrumen }})
              </option>
            </select>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
              Pilih periode untuk butir ini
            </p>
          </div>

          <!-- Kode & Nama -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Kode <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.kode"
                type="text"
                required
                :class="[
                  'block w-full rounded-lg border p-2.5 text-sm focus:ring-2',
                  hasFieldError('kode')
                    ? 'border-red-500 bg-red-50 text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-red-900/20 dark:text-red-400'
                    : 'border-gray-300 bg-gray-50 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400'
                ]"
                placeholder="Contoh: 1.1"
              />
              <p v-if="hasFieldError('kode')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ getFieldError('kode') }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Nama Butir <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.nama"
                type="text"
                required
                :class="[
                  'block w-full rounded-lg border p-2.5 text-sm focus:ring-2',
                  hasFieldError('nama')
                    ? 'border-red-500 bg-red-50 text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-red-900/20 dark:text-red-400'
                    : 'border-gray-300 bg-gray-50 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400'
                ]"
                placeholder="Nama butir akreditasi"
              />
              <p v-if="hasFieldError('nama')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ getFieldError('nama') }}
              </p>
            </div>
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="form.deskripsi"
              rows="4"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              placeholder="Deskripsi butir akreditasi..."
            ></textarea>
          </div>

          <!-- Instrumen, Kategori, Bobot -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Instrumen <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.instrumen"
                required
                :disabled="butirType === 'periode'"
                @change="handleInstrumenChange"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Instrumen</option>
                <option v-for="ins in instrumenList" :key="ins" :value="ins">{{ ins }}</option>
              </select>
              <p v-if="butirType === 'periode'" class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                Instrumen diambil dari periode yang dipilih
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Kategori
              </label>
              <select
                v-model="form.kategori"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Kategori</option>
                <option v-for="kat in kategoriList" :key="kat" :value="kat">{{ kat }}</option>
              </select>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Bobot
              </label>
              <input
                v-model.number="form.bobot"
                type="number"
                step="0.01"
                min="0"
                max="100"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Parent & Urutan -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Parent Butir
              </label>
              <select
                v-model="form.parent_id"
                :disabled="loadingParents"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option :value="null">Tidak ada (Root Level)</option>
                <option
                  v-for="parent in availableParents"
                  :key="parent.id"
                  :value="parent.id"
                  :disabled="isCircularReference(parent.id)"
                >
                  {{ parent.kode }} - {{ parent.nama }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pilih parent jika butir ini merupakan sub-butir dari butir lain
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Urutan
              </label>
              <input
                v-model.number="form.urutan"
                type="number"
                min="0"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="0"
              />
            </div>
          </div>

          <!-- Is Mandatory Checkbox -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.is_mandatory"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
              />
              <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                Butir Wajib
              </span>
            </label>
            <p class="ml-6 mt-1 text-xs text-gray-500 dark:text-gray-400">
              Centang jika butir ini wajib diisi
            </p>
          </div>

          <!-- Metadata (JSON) -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Metadata (JSON)
            </label>
            <textarea
              v-model="metadataString"
              rows="4"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 font-mono text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              placeholder='{"key": "value"}'
            ></textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Format JSON untuk data tambahan (opsional)
            </p>
            <p v-if="metadataError" class="mt-1 text-xs text-red-600 dark:text-red-400">
              {{ metadataError }}
            </p>
          </div>

          <!-- Error Message -->
          <div v-if="localError" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                />
              </svg>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                  Terjadi kesalahan saat menyimpan data
                </h3>
                <div v-if="typeof localError === 'string'" class="mt-2 text-sm text-red-700 dark:text-red-400">
                  {{ localError }}
                </div>
                <div v-else-if="localError.errors" class="mt-2 text-sm text-red-700 dark:text-red-400">
                  <ul class="list-disc space-y-1 pl-5">
                    <li v-for="(messages, field) in localError.errors" :key="field">
                      <span class="font-medium">{{ formatFieldName(field) }}:</span>
                      {{ Array.isArray(messages) ? messages[0] : messages }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Success Message -->
          <div v-if="successMessage" class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
              </svg>
              <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-400">
                {{ successMessage }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="loading || metadataError"
              class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
              <span v-if="loading">Menyimpan...</span>
              <span v-else>{{ isEdit ? 'Update' : 'Simpan' }}</span>
            </button>
            <router-link
              to="/akreditasi/butir"
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
            >
              Batal
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'
import MainLayout from '@/layouts/MainLayout.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const { loading, getButirList, getInstrumenList, getKategoriList, getPeriodeList } = useAkreditasiApi()

const isEdit = computed(() => !!route.params.id)
const localError = ref(null)
const successMessage = ref(null)
const loadingParents = ref(false)
const metadataError = ref(null)

const butirType = ref('template') // 'template' or 'periode'
const periodeList = ref([])

const form = ref({
  kode: '',
  nama: '',
  deskripsi: '',
  instrumen: '',
  kategori: '',
  bobot: null,
  parent_id: null,
  urutan: 0,
  is_mandatory: false,
  metadata: {},
  periode_akreditasi_id: null,
})

const metadataString = ref('{}')
const instrumenList = ref([])
const kategoriList = ref([])
const allButirs = ref([])
const currentButirId = ref(null)

// Watch metadata string for validation
watch(metadataString, (newValue) => {
  if (!newValue || newValue.trim() === '') {
    metadataString.value = '{}'
    metadataError.value = null
    return
  }

  try {
    JSON.parse(newValue)
    metadataError.value = null
  } catch (e) {
    metadataError.value = 'Format JSON tidak valid'
  }
})

// Available parents (excluding self and descendants)
const availableParents = computed(() => {
  return allButirs.value.filter(butir => {
    // Exclude self in edit mode
    if (isEdit.value && butir.id === currentButirId.value) {
      return false
    }

    // Filter by type
    if (butirType.value === 'template') {
      // For template: only show other templates with same instrumen
      return !butir.periode_akreditasi_id && butir.instrumen === form.value.instrumen
    } else {
      // For per-periode: only show butirs from same periode
      return butir.periode_akreditasi_id === form.value.periode_akreditasi_id
    }
  })
})

// Watch butirType changes
watch(butirType, (newType) => {
  // Clear periode when switching to template
  if (newType === 'template') {
    form.value.periode_akreditasi_id = null
  }
  // Clear parent when switching types
  form.value.parent_id = null
  // Refresh parent list
  fetchParents()
})

// Check if selecting this parent would create a circular reference
const isCircularReference = (parentId) => {
  if (!isEdit.value) return false
  if (!currentButirId.value) return false
  if (parentId === currentButirId.value) return true

  // Check if the parent is actually a descendant of the current butir
  const isDescendant = (butirId, potentialAncestorId) => {
    const butir = allButirs.value.find(b => b.id === butirId)
    if (!butir || !butir.parent_id) return false
    if (butir.parent_id === potentialAncestorId) return true
    return isDescendant(butir.parent_id, potentialAncestorId)
  }

  return isDescendant(parentId, currentButirId.value)
}

// Clear messages when form changes
watch(
  form,
  () => {
    if (localError.value || successMessage.value) {
      localError.value = null
      successMessage.value = null
    }
  },
  { deep: true }
)

const fetchInstrumen = async () => {
  try {
    const response = await getInstrumenList()
    instrumenList.value = response.data || []

    // Set default if creating new and query param exists
    if (!isEdit.value && route.query.instrumen) {
      form.value.instrumen = route.query.instrumen
    }
  } catch (error) {
    console.error('Failed to fetch instrumen:', error)
  }
}

const fetchKategori = async (instrumen = null) => {
  try {
    const ins = instrumen || form.value.instrumen || '4.0'
    const response = await getKategoriList(ins)
    kategoriList.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch kategori:', error)
  }
}

const fetchPeriodes = async () => {
  try {
    const response = await getPeriodeList({ per_page: 100 })
    periodeList.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch periodes:', error)
  }
}

const fetchParents = async () => {
  loadingParents.value = true
  try {
    const params = { per_page: 'all' }

    // Filter based on type
    if (butirType.value === 'template') {
      params.template_only = true
      if (form.value.instrumen) {
        params.instrumen = form.value.instrumen
      }
    } else {
      // For per-periode, only get butirs from the selected periode
      if (form.value.periode_akreditasi_id) {
        params.periode_akreditasi_id = form.value.periode_akreditasi_id
      }
    }

    const response = await getButirList(params)
    allButirs.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch parents:', error)
  } finally {
    loadingParents.value = false
  }
}

const handleInstrumenChange = () => {
  fetchKategori(form.value.instrumen)
  fetchParents()
}

const handlePeriodeChange = () => {
  // Get instrumen from selected periode
  const selectedPeriode = periodeList.value.find(p => p.id === form.value.periode_akreditasi_id)
  if (selectedPeriode) {
    form.value.instrumen = selectedPeriode.instrumen
    fetchKategori(selectedPeriode.instrumen)
  }
  // Clear parent and refresh list
  form.value.parent_id = null
  fetchParents()
}

const formatFieldName = (field) => {
  const fieldNames = {
    kode: 'Kode',
    nama: 'Nama Butir',
    deskripsi: 'Deskripsi',
    instrumen: 'Instrumen',
    kategori: 'Kategori',
    bobot: 'Bobot',
    parent_id: 'Parent Butir',
    urutan: 'Urutan',
    is_mandatory: 'Wajib',
    metadata: 'Metadata',
  }
  return fieldNames[field] || field
}

const hasFieldError = (field) => {
  return localError.value?.errors && localError.value.errors[field]
}

const getFieldError = (field) => {
  if (!hasFieldError(field)) return null
  const error = localError.value.errors[field]
  return Array.isArray(error) ? error[0] : error
}

const handleSubmit = async () => {
  // Clear previous messages
  localError.value = null
  successMessage.value = null

  // Validate metadata
  if (metadataError.value) {
    localError.value = 'Format metadata JSON tidak valid'
    return
  }

  try {
    // Parse metadata
    let metadata = {}
    try {
      metadata = JSON.parse(metadataString.value || '{}')
    } catch (e) {
      localError.value = 'Format metadata JSON tidak valid'
      return
    }

    // Prepare data
    const data = { ...form.value, metadata }

    // Set periode_akreditasi_id based on type
    if (butirType.value === 'template') {
      data.periode_akreditasi_id = null
    } else {
      // Validate periode is selected for per-periode type
      if (!data.periode_akreditasi_id) {
        localError.value = 'Periode akreditasi harus dipilih untuk butir per-periode'
        return
      }
    }

    // Clean up null/empty values
    if (data.deskripsi === '') data.deskripsi = null
    if (data.kategori === '') data.kategori = null
    if (data.bobot === '' || data.bobot === null) data.bobot = null
    if (data.parent_id === '' || data.parent_id === null) data.parent_id = null

    let response
    if (isEdit.value) {
      response = await axios.put(`/api/butir-akreditasi/${route.params.id}`, data)
      successMessage.value = 'Butir akreditasi berhasil diupdate!'
    } else {
      response = await axios.post('/api/butir-akreditasi', data)
      successMessage.value = 'Butir akreditasi berhasil dibuat!'
    }

    // Show success message briefly then redirect
    setTimeout(() => {
      router.push('/akreditasi/butir')
    }, 1500)
  } catch (err) {
    console.error('Error saving butir:', err)

    // Handle different error types
    if (err.response?.data) {
      const errorData = err.response.data

      // Laravel validation errors
      if (errorData.errors) {
        localError.value = {
          message: errorData.message || 'Validasi gagal',
          errors: errorData.errors,
        }
      }
      // Simple error message
      else if (errorData.message) {
        localError.value = errorData.message
      }
      // Unknown error format
      else {
        localError.value = 'Terjadi kesalahan saat menyimpan data'
      }
    }
    // Network or other errors
    else if (err.message) {
      localError.value = err.message
    }
    // Fallback error
    else {
      localError.value = 'Terjadi kesalahan yang tidak diketahui'
    }

    // Scroll to top to show error
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

onMounted(async () => {
  await fetchInstrumen()
  await fetchKategori()
  await fetchPeriodes()

  if (isEdit.value) {
    // Edit mode: load existing data
    localError.value = null
    try {
      const response = await axios.get(`/api/butir-akreditasi/${route.params.id}`)
      const data = response.data.data

      currentButirId.value = data.id

      // Determine type based on periode_akreditasi_id
      butirType.value = data.periode_akreditasi_id ? 'periode' : 'template'

      Object.assign(form.value, {
        kode: data.kode,
        nama: data.nama,
        deskripsi: data.deskripsi,
        instrumen: data.instrumen,
        kategori: data.kategori,
        bobot: data.bobot,
        parent_id: data.parent_id,
        urutan: data.urutan,
        is_mandatory: data.is_mandatory,
        periode_akreditasi_id: data.periode_akreditasi_id,
      })

      metadataString.value = JSON.stringify(data.metadata || {}, null, 2)

      // Fetch kategori for selected instrumen
      if (data.instrumen) {
        await fetchKategori(data.instrumen)
      }

      // Fetch parents after determining type
      await fetchParents()
    } catch (err) {
      console.error('Error loading butir:', err)
      localError.value = 'Gagal memuat data butir akreditasi'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } else {
    // Create mode
    // Set parent_id from query param if creating new sub-butir
    if (route.query.parent_id) {
      form.value.parent_id = parseInt(route.query.parent_id)
    }

    // Set periode from query param if provided
    if (route.query.periode_id) {
      butirType.value = 'periode'
      form.value.periode_akreditasi_id = parseInt(route.query.periode_id)
      // Auto-select instrumen from periode
      const selectedPeriode = periodeList.value.find(p => p.id === form.value.periode_akreditasi_id)
      if (selectedPeriode) {
        form.value.instrumen = selectedPeriode.instrumen
        await fetchKategori(selectedPeriode.instrumen)
      }
    }

    await fetchParents()
  }
})
</script>
