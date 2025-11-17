<template>
  <MainLayout>
    <div>
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
          <!-- Template Badge -->
          <span
            v-if="hasDynamicForm"
            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
          >
            📋 {{ selectedButir.metadata?.form_config?.type === 'table' ? 'Form Tabel' :
                 selectedButir.metadata?.form_config?.type === 'narrative' ? 'Form Narasi' :
                 'Form Template' }}
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
          <!-- Debug info (akan kita hapus nanti) -->
          <p v-if="hasDynamicForm" class="pt-2 border-t border-blue-200">
            <span class="font-medium text-green-600">✓ Template aktif:</span>
            {{ selectedButir.metadata?.form_config?.label }}
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
            <!-- Dynamic Form (if butir has form_config) -->
            <div v-if="hasDynamicForm">
              <DynamicFormRenderer
                :form-config="selectedButir.metadata?.form_config"
                v-model="form.form_data"
                :readonly="isLocked"
                @validate="handleFormValidation"
                @completion="handleCompletionChange"
              />
            </div>

            <!-- Legacy Rich Text Editor (default) -->
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
          <div>
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
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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

          <!-- Actions -->
          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="loading"
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
import MainLayout from '@/layouts/MainLayout.vue'
import RichTextEditor from '@/components/RichTextEditor.vue'
import DynamicFormRenderer from '@/components/akreditasi/DynamicFormRenderer.vue'
import LockStatusIndicator from '@/components/akreditasi/LockStatusIndicator.vue'
import VersionHistoryTimeline from '@/components/akreditasi/VersionHistoryTimeline.vue'
import EditLockIndicator from '@/components/akreditasi/EditLockIndicator.vue'
import ButirCommentPanel from '@/components/akreditasi/ButirCommentPanel.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const { loading, getPeriodeList, getButirList, savePengisian, updatePengisian, submitPengisian } = useAkreditasiApi()

const isEdit = computed(() => !!route.params.id)
const localError = ref(null)
const successMessage = ref(null)
const loadingButirs = ref(false)
const isLocked = ref(false)
const formIsValid = ref(true)

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

const handleButirChange = () => {
  const butir = butirList.value.find(b => b.id === form.value.butir_akreditasi_id)
  selectedButir.value = butir || null

  // Debug logging
  console.log('=== Butir Changed ===')
  console.log('Selected Butir:', butir)
  console.log('Has metadata:', !!butir?.metadata)
  console.log('Metadata:', butir?.metadata)
  console.log('Has form_config:', !!butir?.metadata?.form_config)
  console.log('Form config:', butir?.metadata?.form_config)
  console.log('hasDynamicForm computed:', hasDynamicForm.value)
  console.log('===================')
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
  formIsValid.value = validationResult.isValid
  if (!validationResult.isValid) {
    localError.value = {
      message: 'Form validation failed',
      errors: { form_data: validationResult.errors }
    }
  } else {
    localError.value = null
  }
}

const handleCompletionChange = (completionPercentage) => {
  form.value.completion_percentage = completionPercentage
}

const handleSubmit = async () => {
  localError.value = null
  successMessage.value = null

  // Validate dynamic form if applicable
  if (hasDynamicForm.value && !formIsValid.value) {
    localError.value = 'Please fix form validation errors before saving'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  try {
    // Prepare data
    const data = { ...form.value }

    // For dynamic forms, clear konten since we use form_data
    if (hasDynamicForm.value) {
      data.konten = ''
      data.konten_plain = ''
    } else {
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

    let response
    if (isEdit.value) {
      response = await updatePengisian(route.params.id, data)
      successMessage.value = 'Pengisian berhasil diupdate!'
    } else {
      response = await savePengisian(data)
      successMessage.value = 'Pengisian berhasil disimpan!'
    }

    // Show success message briefly then redirect
    setTimeout(() => {
      handleCancel()
    }, 1500)
  } catch (err) {
    console.error('Error saving pengisian:', err)
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
      router.push('/akreditasi/pengisian')
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
  // If came from periode detail (has periode_id in query), go back to list
  if (route.query.periode_id) {
    router.push(`/akreditasi/periode/${route.query.periode_id}/pengisian`)
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

      // Load butir info
      await fetchButirs()
      handleButirChange()
    } catch (err) {
      console.error('Error loading pengisian:', err)
      localError.value = 'Gagal memuat data pengisian'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
})
</script>
