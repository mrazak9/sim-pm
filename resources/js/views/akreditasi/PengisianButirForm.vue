<template>
  <MainLayout>
    <div>
      <!-- Breadcrumb Navigation -->
      <nav class="mb-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
        <button
          @click="handleCancel"
          class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Pengisian Butir
        </button>
        <span class="mx-2">/</span>
        <span class="text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit' : 'Tambah' }}
        </span>
      </nav>

      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit Pengisian Butir' : 'Tambah Pengisian Butir' }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Isi konten untuk butir akreditasi
        </p>
      </div>

      <!-- Butir Info Card -->
      <div v-if="selectedButir" class="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
        <div class="flex items-start justify-between mb-2">
          <h3 class="font-semibold text-blue-900 dark:text-blue-300">Butir Akreditasi</h3>
          <!-- Loading Mapping State -->
          <span
            v-if="loadingMapping"
            class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 flex items-center gap-2"
          >
            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Mengecek mapping...
          </span>
          <!-- Template Badge -->
          <span
            v-else-if="hasColumnMapping"
            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
          >
            ✨ Form Tabel (Column Mapping)
          </span>
          <span
            v-else-if="hasDynamicForm"
            class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
          >
            📋 {{ selectedButir.metadata?.form_config?.type === 'table' ? 'Form Tabel (Legacy)' :
                 selectedButir.metadata?.form_config?.type === 'narrative' ? 'Form Narasi (Legacy)' :
                 'Form Template (Legacy)' }}
          </span>
          <span
            v-else
            class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400"
          >
            Rich Text Editor
          </span>
        </div>
        <div class="space-y-1 text-sm text-blue-800 dark:text-blue-400">
          <p><span class="font-medium">Kode:</span> {{ selectedButir.kode }}</p>
          <p><span class="font-medium">Nama:</span> {{ selectedButir.nama }}</p>
          <p v-if="selectedButir.deskripsi"><span class="font-medium">Deskripsi:</span> {{ selectedButir.deskripsi }}</p>
          <!-- Debug info -->
          <p v-if="hasColumnMapping" class="pt-2 border-t border-blue-200">
            <span class="font-medium text-green-600">✓ Column Mapping aktif</span>
            - Data tersimpan di c1-c30 dengan performa optimal
          </p>
          <p v-else-if="hasDynamicForm" class="pt-2 border-t border-yellow-200">
            <span class="font-medium text-yellow-600">⚠ Legacy Template:</span>
            {{ selectedButir.metadata?.form_config?.label }} (akan diupgrade)
          </p>
        </div>
      </div>

      <!-- Lock Status Indicator (only in edit mode) -->
      <LockStatusIndicator
        v-if="isEdit && route.params.id"
        :pengisian-butir-id="parseInt(route.params.id)"
        :show-active-status="true"
        :auto-refresh="true"
        :refresh-interval="60000"
        @lock-status-changed="handleLockStatusChanged"
        @contact-admin="handleContactAdmin"
      />

      <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Periode & Butir Selection (only for create without query params) -->
          <div v-if="!isEdit && !route.query.periode_id && !route.query.butir_id" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Periode Akreditasi <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.periode_akreditasi_id"
                required
                @change="handlePeriodeChange"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Periode</option>
                <option v-for="periode in periodeList" :key="periode.id" :value="periode.id">
                  {{ periode.nama }}
                </option>
              </select>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Butir Akreditasi <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.butir_akreditasi_id"
                required
                @change="handleButirChange"
                :disabled="!form.periode_akreditasi_id || loadingButirs"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Butir</option>
                <option v-for="butir in butirList" :key="butir.id" :value="butir.id">
                  {{ butir.kode }} - {{ butir.nama }}
                  {{ butir.metadata?.form_config ? ' [📋 Form Template]' : '' }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                Butir dengan label [📋 Form Template] memiliki form input khusus
              </p>
            </div>
          </div>

          <!-- Dynamic Form or Rich Text Editor -->
          <div>
            <!-- NEW: Column Mapping Form (c1-c30 system) -->
            <div v-if="hasColumnMapping && isEdit">
              <ButirDataTableForm
                :key="`butir-data-form-${form.butir_akreditasi_id}`"
                :butir-id="form.butir_akreditasi_id"
                :pengisian-butir-id="parseInt(route.params.id)"
                :readonly="isLocked"
                @saved="handleButirDataSaved"
                @error="handleButirDataError"
              />
            </div>

            <!-- DEPRECATED: Old Dynamic Form (form_config - will be removed) -->
            <div v-else-if="hasDynamicForm">
              <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm">
                <div class="flex items-center gap-2 text-yellow-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <span class="font-medium">Form ini menggunakan sistem lama (form_config). Akan diupgrade ke column mapping.</span>
                </div>
              </div>
              <DynamicFormRenderer
                :key="`dynamic-form-${form.butir_akreditasi_id}`"
                :form-config="selectedButir.metadata?.form_config"
                v-model="form.form_data"
                :readonly="isLocked"
                @validate="handleFormValidation"
                @completion="handleCompletionChange"
              />
            </div>

            <!-- Legacy Rich Text Editor (default for narrative/free-text butirs) -->
            <div v-else>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Konten <span class="text-red-500">*</span>
              </label>
              <RichTextEditor
                v-model="form.konten"
                :error="hasFieldError('konten') ? getFieldError('konten') : ''"
                :showCharCount="true"
              />
            </div>
          </div>

          <!-- Notes -->
          <div v-if="!hasColumnMapping || isEdit">
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Catatan
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              placeholder="Catatan tambahan untuk pengisian ini..."
            ></textarea>
          </div>

          <!-- Completion Status -->
          <div v-if="!hasColumnMapping || isEdit" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Persentase Penyelesaian (%)
              </label>
              <input
                v-model.number="form.completion_percentage"
                type="number"
                min="0"
                max="100"
                step="1"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="0"
              />
            </div>

            <div class="flex items-center">
              <label class="flex items-center">
                <input
                  v-model="form.is_complete"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                />
                <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                  Tandai sebagai selesai
                </span>
              </label>
            </div>
          </div>

          <!-- Status (for edit mode) -->
          <div v-if="isEdit">
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Status
            </label>
            <div class="flex items-center gap-2">
              <select
                v-model="form.status"
                disabled
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="draft">Draft</option>
                <option value="submitted">Diajukan</option>
                <option value="review">Dalam Review</option>
                <option value="revision">Perlu Revisi</option>
                <option value="approved">Disetujui</option>
              </select>
              <span :class="['rounded px-3 py-2 text-sm font-medium', getStatusClass(form.status)]">
                {{ getStatusLabel(form.status) }}
              </span>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Status akan berubah saat Anda mengajukan atau mereview pengisian
            </p>
          </div>

          <!-- Review Notes (if status is revision) -->
          <div v-if="isEdit && form.status === 'revision' && form.review_notes" class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <h4 class="mb-2 font-semibold text-yellow-900 dark:text-yellow-300">Catatan Review</h4>
            <p class="text-sm text-yellow-800 dark:text-yellow-400">{{ form.review_notes }}</p>
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

          <!-- Column Mapping Info (no main save button needed) -->
          <div v-if="hasColumnMapping && isEdit" class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="flex-1">
                <h4 class="text-sm font-medium text-green-800 mb-1">Data Column Mapping</h4>
                <p class="text-sm text-green-700">
                  Data untuk butir ini disimpan secara otomatis menggunakan sistem column mapping.
                  Gunakan tombol "Simpan" di dalam tabel data untuk menyimpan perubahan.
                </p>
              </div>
            </div>
          </div>

          <!-- Metadata Save Button (for column mapping mode) -->
          <div v-if="hasColumnMapping && isEdit" class="flex items-center gap-3">
            <button
              type="button"
              @click="handleSaveMetadata"
              :disabled="loading"
              class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
              <span v-if="loading">Menyimpan...</span>
              <span v-else>Simpan Catatan & Status</span>
            </button>
            <button
              v-if="form.status === 'draft'"
              type="button"
              @click="handleSubmitForReview"
              :disabled="loading"
              class="rounded-lg bg-green-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 disabled:opacity-50"
            >
              <span v-if="loading">Mengajukan...</span>
              <span v-else>Ajukan untuk Review</span>
            </button>
            <button
              type="button"
              @click="handleCancel"
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
            >
              Kembali
            </button>
          </div>

          <!-- Actions -->
          <div v-if="!hasColumnMapping" class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="loading"
              @click="console.log('🔵 Simpan Draft button clicked')"
              class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
              <span v-if="loading">Menyimpan...</span>
              <span v-else>Simpan Draft</span>
            </button>
            <button
              v-if="isEdit && form.status === 'draft'"
              type="button"
              @click="handleSubmitForReview"
              :disabled="loading"
              class="rounded-lg bg-green-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 disabled:opacity-50"
            >
              <span v-if="loading">Mengajukan...</span>
              <span v-else>Ajukan untuk Review</span>
            </button>
            <button
              type="button"
              @click="handleCancel"
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
            >
              Batal
            </button>
          </div>
        </form>
      </div>

      <!-- Edit Lock Indicator (only in edit mode) -->
      <div v-if="isEdit && route.params.id" class="mt-6">
        <EditLockIndicator
          :pengisian-butir-id="parseInt(route.params.id)"
          :auto-refresh="true"
        />
      </div>

      <!-- Version History Timeline (only in edit mode) -->
      <div v-if="isEdit && route.params.id" class="mt-6">
        <VersionHistoryTimeline
          :pengisian-butir-id="parseInt(route.params.id)"
          :can-restore="form.status === 'draft' && !isLocked"
          @version-restored="handleVersionRestored"
        />
      </div>

      <!-- Comment/Discussion Panel (only in edit mode) -->
      <div v-if="isEdit && route.params.id" class="mt-6">
        <ButirCommentPanel
          :pengisian-butir-id="parseInt(route.params.id)"
        />
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'
import { useButirData } from '@/composables/useButirData'
import MainLayout from '@/layouts/MainLayout.vue'
import RichTextEditor from '@/components/RichTextEditor.vue'
import DynamicFormRenderer from '@/components/akreditasi/DynamicFormRenderer.vue'
import ButirDataTableForm from '@/components/akreditasi/ButirDataTableForm.vue'
import LockStatusIndicator from '@/components/akreditasi/LockStatusIndicator.vue'
import VersionHistoryTimeline from '@/components/akreditasi/VersionHistoryTimeline.vue'
import EditLockIndicator from '@/components/akreditasi/EditLockIndicator.vue'
import ButirCommentPanel from '@/components/akreditasi/ButirCommentPanel.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const { loading, getPeriodeList, getButirList, savePengisian, updatePengisian, submitPengisian } = useAkreditasiApi()
const { fetchMappings } = useButirData()

const isEdit = computed(() => !!route.params.id)
const localError = ref(null)
const successMessage = ref(null)
const loadingButirs = ref(false)
const loadingMapping = ref(false)
const isLocked = ref(false)
const formIsValid = ref(true)
const hasColumnMapping = ref(false)

const form = ref({
  periode_akreditasi_id: '',
  butir_akreditasi_id: '',
  konten: '',
  form_data: null, // For dynamic forms
  notes: '',
  completion_percentage: 0,
  is_complete: false,
  status: 'draft',
  review_notes: '',
})

const periodeList = ref([])
const butirList = ref([])
const selectedButir = ref(null)

// Check if selected butir uses dynamic form
const hasDynamicForm = computed(() => {
  return selectedButir.value && selectedButir.value.metadata?.form_config
})

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

const fetchPeriodes = async () => {
  try {
    const response = await getPeriodeList()
    periodeList.value = response.data.data || []

    // Set from query param if creating new
    if (!isEdit.value && route.query.periode_id) {
      form.value.periode_akreditasi_id = parseInt(route.query.periode_id)
      await fetchButirs()
    }
  } catch (error) {
    console.error('Failed to fetch periodes:', error)
  }
}

const fetchButirs = async () => {
  if (!form.value.periode_akreditasi_id) return

  loadingButirs.value = true
  try {
    const response = await getButirList({ per_page: 'all' })
    butirList.value = response.data || []

    // Set from query param if creating new
    if (!isEdit.value && route.query.butir_id) {
      form.value.butir_akreditasi_id = parseInt(route.query.butir_id)
      handleButirChange()
    }
  } catch (error) {
    console.error('Failed to fetch butirs:', error)
  } finally {
    loadingButirs.value = false
  }
}

const handlePeriodeChange = () => {
  form.value.butir_akreditasi_id = ''
  selectedButir.value = null
  fetchButirs()
}

const handleButirChange = async () => {
  const butir = butirList.value.find(b => b.id === form.value.butir_akreditasi_id)
  selectedButir.value = butir || null

  // Check for column mapping (NEW system)
  if (butir?.id) {
    loadingMapping.value = true
    try {
      const mappings = await fetchMappings(butir.id)
      hasColumnMapping.value = mappings && mappings.length > 0

      console.log('=== Butir Changed ===')
      console.log('Selected Butir:', butir)
      console.log('Has Column Mapping:', hasColumnMapping.value)
      console.log('Mappings count:', mappings?.length || 0)
      console.log('Has form_config (old):', !!butir?.metadata?.form_config)
      console.log('hasDynamicForm computed:', hasDynamicForm.value)
      console.log('===================')
    } catch (err) {
      console.error('Failed to check column mapping:', err)
      hasColumnMapping.value = false
    } finally {
      loadingMapping.value = false
    }
  } else {
    hasColumnMapping.value = false
  }
}

const formatFieldName = (field) => {
  const fieldNames = {
    periode_akreditasi_id: 'Periode Akreditasi',
    butir_akreditasi_id: 'Butir Akreditasi',
    konten: 'Konten',
    notes: 'Catatan',
    completion_percentage: 'Persentase Penyelesaian',
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

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    submitted: 'Diajukan',
    review: 'Dalam Review',
    revision: 'Perlu Revisi',
    approved: 'Disetujui',
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    submitted: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    revision: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

// Dynamic form handlers
const handleFormValidation = (validationResult) => {
  console.log('=== Form Validation Event ===')
  console.log('validationResult:', validationResult)
  console.log('isValid:', validationResult.isValid)
  console.log('errors:', validationResult.errors)

  formIsValid.value = validationResult.isValid
  if (!validationResult.isValid) {
    console.log('Setting localError for validation failure')
    localError.value = {
      message: 'Validasi form gagal. Harap periksa field yang ditandai merah.',
      errors: { form_data: validationResult.errors }
    }
  } else {
    console.log('Validation passed, clearing localError')
    localError.value = null
  }
  console.log('===========================')
}

const handleCompletionChange = (completionPercentage) => {
  form.value.completion_percentage = completionPercentage
}

// NEW: Handlers for ButirDataTableForm
const handleButirDataSaved = (data) => {
  console.log('Butir data saved:', data)
  successMessage.value = 'Data berhasil disimpan!'

  // Update completion percentage
  form.value.completion_percentage = 100
  form.value.is_complete = true

  // Clear after 3 seconds
  setTimeout(() => {
    successMessage.value = null
  }, 3000)
}

const handleButirDataError = (error) => {
  console.error('Butir data error:', error)
  localError.value = error || 'Gagal menyimpan data butir'

  // Scroll to top to show error
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

/**
 * Save only metadata (notes, completion_percentage, is_complete, status)
 * Used for column mapping mode where data is saved separately
 */
const handleSaveMetadata = async () => {
  console.log('=== Handle Save Metadata - START ===')

  try {
    localError.value = null
    successMessage.value = null

    // Prepare metadata only
    const data = {
      notes: form.value.notes || null,
      completion_percentage: form.value.completion_percentage || 0,
      is_complete: form.value.is_complete || false,
      status: form.value.status,
    }

    console.log('Saving metadata:', data)

    if (isEdit.value) {
      const response = await updatePengisian(route.params.id, data)
      successMessage.value = 'Catatan dan status berhasil disimpan!'
      console.log('✅ Metadata saved!', response)
    }

    // Show success message briefly
    setTimeout(() => {
      successMessage.value = null
    }, 3000)

  } catch (err) {
    console.error('❌ Error saving metadata:', err)
    handleError(err)
  }
}

const handleSubmit = async () => {
  console.log('=== Handle Submit (Save Draft) - START ===')
  console.log('Timestamp:', new Date().toISOString())
  console.log('isEdit:', isEdit.value)
  console.log('hasColumnMapping:', hasColumnMapping.value)
  console.log('hasDynamicForm:', hasDynamicForm.value)
  console.log('formIsValid:', formIsValid.value)
  console.log('form.form_data:', form.value.form_data)
  console.log('form.butir_akreditasi_id:', form.value.butir_akreditasi_id)
  console.log('form.periode_akreditasi_id:', form.value.periode_akreditasi_id)

  // IMPORTANT: Block save for column mapping - data is saved via ButirDataTableForm
  if (hasColumnMapping.value) {
    console.log('🛑 BLOCKED: Column mapping detected. Use table save button instead.')
    console.log('=== Handle Submit - END (BLOCKED) ===')
    return
  }

  try {
    localError.value = null
    successMessage.value = null

    // Validate dynamic form if applicable
    if (hasDynamicForm.value && !formIsValid.value) {
      console.log('❌ Validation failed! Stopping save.')
      localError.value = 'Harap perbaiki error validasi form sebelum menyimpan'
      window.scrollTo({ top: 0, behavior: 'smooth' })
      return
    }

    console.log('✅ Validation passed, proceeding with save...')

    // Prepare data
    const data = { ...form.value }
    console.log('Data before processing:', JSON.stringify(data, null, 2))

    // For dynamic forms, clear konten since we use form_data
    if (hasDynamicForm.value) {
      console.log('Using dynamic form data')
      data.konten = ''
      data.konten_plain = ''
    } else {
      console.log('Using legacy rich text editor')
      // Generate plain text from HTML for legacy forms
      const temp = document.createElement('div')
      temp.innerHTML = data.konten
      data.konten_plain = temp.textContent || temp.innerText || ''
      // Clear form_data for legacy forms
      data.form_data = null
    }

    // Clean up
    if (data.notes === '') data.notes = null
    if (data.completion_percentage === '') data.completion_percentage = 0

    console.log('Data after processing:', JSON.stringify(data, null, 2))
    console.log('Calling API...')

    let response
    if (isEdit.value) {
      console.log('Updating existing pengisian:', route.params.id)
      response = await updatePengisian(route.params.id, data)
      successMessage.value = 'Pengisian berhasil diupdate!'
    } else {
      console.log('Creating new pengisian')
      response = await savePengisian(data)
      successMessage.value = 'Pengisian berhasil disimpan!'
    }

    console.log('✅ Save successful!', response)
    console.log('=== Handle Submit - END (SUCCESS) ===')

    // Show success message briefly then redirect
    setTimeout(() => {
      handleCancel()
    }, 1500)
  } catch (err) {
    console.error('❌ Error in handleSubmit:', err)
    console.error('Error details:', {
      message: err.message,
      response: err.response?.data,
      stack: err.stack
    })
    console.log('=== Handle Submit - END (ERROR) ===')
    handleError(err)
  }
}

const handleSubmitForReview = async () => {
  if (!confirm('Apakah Anda yakin ingin mengajukan pengisian ini untuk direview?')) {
    return
  }

  localError.value = null
  successMessage.value = null

  try {
    await submitPengisian(route.params.id)
    successMessage.value = 'Pengisian berhasil diajukan untuk review!'

    setTimeout(() => {
      const periodeId = route.query.periode_id || form.value.periode_akreditasi_id
      if (periodeId) {
        router.push(`/akreditasi/periode/${periodeId}/pengisian`)
      } else {
        router.push('/akreditasi/pengisian')
      }
    }, 1500)
  } catch (err) {
    console.error('Error submitting pengisian:', err)
    handleError(err)
  }
}

const handleError = (err) => {
  if (err.response?.data) {
    const errorData = err.response.data
    if (errorData.errors) {
      localError.value = {
        message: errorData.message || 'Validasi gagal',
        errors: errorData.errors,
      }
    } else if (errorData.message) {
      localError.value = errorData.message
    } else {
      localError.value = 'Terjadi kesalahan saat menyimpan data'
    }
  } else if (err.message) {
    localError.value = err.message
  } else {
    localError.value = 'Terjadi kesalahan yang tidak diketahui'
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Lock Status Handler
const handleLockStatusChanged = (lockStatusData) => {
  isLocked.value = lockStatusData.is_locked

  // If locked, disable form inputs
  if (lockStatusData.is_locked) {
    localError.value = `Pengisian terkunci: ${lockStatusData.reason}`
  }
}

// Contact Admin Handler
const handleContactAdmin = () => {
  // You can implement email or notification logic here
  alert('Silakan hubungi administrator untuk meminta perpanjangan deadline atau unlock pengisian.')
}

// Cancel Handler - Navigate back to appropriate page
const handleCancel = () => {
  // Priority 1: periode_id from query params (for create mode)
  // Priority 2: periode_id from form data (for edit mode)
  const periodeId = route.query.periode_id || form.value.periode_akreditasi_id

  if (periodeId) {
    router.push(`/akreditasi/periode/${periodeId}/pengisian`)
  } else {
    router.push('/akreditasi/pengisian')
  }
}

// Version Restored Handler
const handleVersionRestored = async (restoredData) => {
  successMessage.value = 'Versi berhasil dipulihkan!'

  // Reload the form data
  try {
    const response = await axios.get(`/api/pengisian-butir/${route.params.id}`)
    const data = response.data.data

    Object.assign(form.value, {
      konten: data.konten || '',
      form_data: data.form_data || null,
      notes: data.notes || '',
      completion_percentage: data.completion_percentage || 0,
      is_complete: data.is_complete || false,
      status: data.status || 'draft',
      review_notes: data.review_notes || '',
    })

    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (err) {
    console.error('Error reloading after restore:', err)
    localError.value = 'Gagal memuat data setelah restore'
  }
}

onMounted(async () => {
  await fetchPeriodes()

  if (isEdit.value) {
    localError.value = null
    try {
      const response = await axios.get(`/api/pengisian-butir/${route.params.id}`)
      const data = response.data.data

      console.log('=== Loading Edit Data ===')
      console.log('API Response Data:', data)
      console.log('form_data from API:', data.form_data)
      console.log('butir_akreditasi:', data.butir_akreditasi)

      Object.assign(form.value, {
        periode_akreditasi_id: data.periode_akreditasi_id,
        butir_akreditasi_id: data.butir_akreditasi_id,
        konten: data.konten || '',
        form_data: data.form_data || null,
        notes: data.notes || '',
        completion_percentage: data.completion_percentage || 0,
        is_complete: data.is_complete || false,
        status: data.status || 'draft',
        review_notes: data.review_notes || '',
      })

      console.log('form.value after assign:', form.value)
      console.log('form.form_data:', form.value.form_data)

      // Load butir info
      await fetchButirs()
      handleButirChange()

      console.log('=== After fetchButirs ===')
      console.log('selectedButir:', selectedButir.value)
      console.log('===========================')
    } catch (err) {
      console.error('Error loading pengisian:', err)
      localError.value = 'Gagal memuat data pengisian'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
})
</script>
