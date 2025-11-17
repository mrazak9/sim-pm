<template>
  <MainLayout>
    <div>
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ isEditMode ? 'Edit' : 'Tambah' }} Template Butir
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              Buat template form untuk butir: <strong>{{ butirInfo?.kode }} - {{ butirInfo?.nama }}</strong>
            </p>
          </div>
          <router-link
            to="/akreditasi/butir-templates"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
          >
            ← Kembali
          </router-link>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="rounded-lg bg-white p-8 text-center shadow dark:bg-gray-800">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-blue-600 border-r-transparent"></div>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading...</p>
      </div>

      <!-- Builder Form -->
      <div v-else class="space-y-6">
        <!-- Template Type Selection -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Tipe Template</h2>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div
              v-for="type in templateTypes"
              :key="type.value"
              @click="selectTemplateType(type.value)"
              :class="[
                'cursor-pointer rounded-lg border-2 p-4 transition-all',
                formConfig.type === type.value
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                  : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'
              ]"
            >
              <div class="text-center">
                <div class="text-3xl">{{ type.icon }}</div>
                <div class="mt-2 font-semibold text-gray-900 dark:text-white">{{ type.label }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ type.description }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Basic Information -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h2>

          <div class="space-y-4">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Label Template <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formConfig.label"
                type="text"
                placeholder="Contoh: Data Penelitian Dosen"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Teks Bantuan
              </label>
              <textarea
                v-model="formConfig.help_text"
                rows="2"
                placeholder="Contoh: Isikan seluruh penelitian yang dilakukan oleh dosen dalam periode akreditasi"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Template Configuration -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Konfigurasi Form</h2>

          <!-- Table Type -->
          <div v-if="formConfig.type === 'table'">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Kolom Tabel</h3>
              <button
                @click="addColumn"
                class="rounded-lg bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700"
              >
                + Tambah Kolom
              </button>
            </div>

            <div v-if="formConfig.columns.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
              <p class="text-gray-500">Belum ada kolom. Klik "Tambah Kolom" untuk mulai.</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(column, index) in formConfig.columns"
                :key="index"
                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
              >
                <div class="mb-3 flex items-center justify-between">
                  <h4 class="font-medium text-gray-900 dark:text-white">Kolom {{ index + 1 }}</h4>
                  <button
                    @click="removeColumn(index)"
                    class="text-red-600 hover:text-red-800 dark:text-red-400"
                  >
                    Hapus
                  </button>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Nama Field</label>
                    <input
                      v-model="column.name"
                      type="text"
                      placeholder="contoh: judul_penelitian"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Label Kolom</label>
                    <input
                      v-model="column.label"
                      type="text"
                      placeholder="Judul Penelitian"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tipe Input</label>
                    <select
                      v-model="column.type"
                      @change="handleColumnTypeChange(column)"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="text">Text</option>
                      <option value="textarea">Textarea</option>
                      <option value="number">Number</option>
                      <option value="currency">Currency (Rupiah)</option>
                      <option value="date">Date</option>
                      <option value="select">Select/Dropdown</option>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Lebar Kolom</label>
                    <input
                      v-model="column.width"
                      type="text"
                      placeholder="30%"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>

                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Placeholder</label>
                    <input
                      v-model="column.placeholder"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>

                  <div class="flex items-center">
                    <input
                      v-model="column.required"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Wajib Diisi</label>
                  </div>

                  <!-- Select Options -->
                  <div v-if="column.type === 'select'" class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                      Opsi (Format: key=value, pisah dengan enter)
                    </label>
                    <textarea
                      v-model="column.optionsText"
                      @blur="parseSelectOptions(column)"
                      rows="3"
                      placeholder="internal=Internal PT&#10;dikti=Kemenristekdikti&#10;swasta=Swasta/Industri"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm font-mono dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Table Options -->
            <div class="mt-6 space-y-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
              <h4 class="font-medium text-gray-900 dark:text-white">Opsi Tabel</h4>

              <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Min Baris</label>
                  <input
                    v-model.number="formConfig.validations.min_rows"
                    type="number"
                    min="0"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Max Baris</label>
                  <input
                    v-model.number="formConfig.validations.max_rows"
                    type="number"
                    min="1"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                </div>
                <div class="flex items-center">
                  <input
                    v-model="formConfig.options.show_summary"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tampilkan Summary</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Narrative Type -->
          <div v-if="formConfig.type === 'narrative'">
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Min Length (karakter)</label>
                  <input
                    v-model.number="formConfig.fields[0].min_length"
                    type="number"
                    min="0"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                </div>
                <div class="flex-1">
                  <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Max Length (karakter)</label>
                  <input
                    v-model.number="formConfig.fields[0].max_length"
                    type="number"
                    min="1"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                </div>
              </div>
              <div class="flex items-center">
                <input
                  v-model="formConfig.fields[0].required"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Wajib Diisi</label>
              </div>
            </div>
          </div>

          <!-- Checklist Type -->
          <div v-if="formConfig.type === 'checklist'">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Item Checklist</h3>
              <button
                @click="addChecklistItem"
                class="rounded-lg bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700"
              >
                + Tambah Item
              </button>
            </div>

            <div v-if="formConfig.items.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
              <p class="text-gray-500">Belum ada item. Klik "Tambah Item" untuk mulai.</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="(item, index) in formConfig.items"
                :key="item.id"
                class="rounded-lg border border-gray-200 p-3 dark:border-gray-700"
              >
                <div class="mb-2 flex items-center justify-between">
                  <h4 class="font-medium text-gray-900 dark:text-white">Item {{ index + 1 }}</h4>
                  <button
                    @click="removeChecklistItem(index)"
                    class="text-red-600 hover:text-red-800 dark:text-red-400"
                  >
                    Hapus
                  </button>
                </div>

                <div class="space-y-2">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Label</label>
                    <input
                      v-model="item.label"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                    <input
                      v-model="item.description"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                  <div class="flex items-center">
                    <input
                      v-model="item.required"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Wajib</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Metric Type -->
          <div v-if="formConfig.type === 'metric'">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white">Metrik/Indikator</h3>
              <button
                @click="addMetric"
                class="rounded-lg bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700"
              >
                + Tambah Metrik
              </button>
            </div>

            <div v-if="formConfig.metrics.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
              <p class="text-gray-500">Belum ada metrik. Klik "Tambah Metrik" untuk mulai.</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="(metric, index) in formConfig.metrics"
                :key="index"
                class="rounded-lg border border-gray-200 p-3 dark:border-gray-700"
              >
                <div class="mb-2 flex items-center justify-between">
                  <h4 class="font-medium text-gray-900 dark:text-white">Metrik {{ index + 1 }}</h4>
                  <button
                    @click="removeMetric(index)"
                    class="text-red-600 hover:text-red-800 dark:text-red-400"
                  >
                    Hapus
                  </button>
                </div>

                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Nama Field</label>
                    <input
                      v-model="metric.name"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Label</label>
                    <input
                      v-model="metric.label"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                    <select
                      v-model="metric.type"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="number">Number</option>
                      <option value="percentage">Percentage</option>
                      <option value="currency">Currency</option>
                    </select>
                  </div>
                  <div class="flex items-center">
                    <input
                      v-model="metric.required"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Wajib Diisi</label>
                  </div>
                  <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Help Text</label>
                    <input
                      v-model="metric.help_text"
                      type="text"
                      class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mixed Type -->
          <div v-if="formConfig.type === 'mixed'">
            <div class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
              <p class="text-gray-500">Mixed template builder akan ditambahkan di versi berikutnya.</p>
              <p class="mt-2 text-sm text-gray-400">Untuk sementara gunakan tipe lain atau edit manual via migration.</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
          <router-link
            to="/akreditasi/butir-templates"
            class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
          >
            Batal
          </router-link>
          <button
            @click="saveTemplate"
            :disabled="saving"
            class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : 'Simpan Template' }}
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useButirTemplateApi } from '@/composables/useButirTemplateApi'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()
const router = useRouter()
const { loading, saving, getTemplateById, saveTemplate: saveTemplateApi } = useButirTemplateApi()

const butirId = route.params.id
const isEditMode = ref(false)
const butirInfo = ref(null)

const templateTypes = [
  { value: 'table', label: 'Tabel', icon: '📊', description: 'Data berbentuk tabel dengan kolom' },
  { value: 'narrative', label: 'Narasi', icon: '📝', description: 'Teks panjang/uraian' },
  { value: 'checklist', label: 'Checklist', icon: '✓', description: 'Daftar item yang bisa dicentang' },
  { value: 'metric', label: 'Metrik', icon: '📈', description: 'Nilai/angka indikator' },
  { value: 'mixed', label: 'Campuran', icon: '🔀', description: 'Kombinasi berbagai tipe' },
]

const formConfig = ref({
  type: 'table',
  label: '',
  help_text: '',
  columns: [],
  fields: [],
  items: [],
  metrics: [],
  validations: {
    min_rows: 1,
    max_rows: 100
  },
  options: {
    allow_add: true,
    allow_delete: true,
    show_summary: false
  }
})

// Template Type Selection
const selectTemplateType = (type) => {
  if (formConfig.value.type === type) return

  formConfig.value.type = type

  // Initialize default structure based on type
  if (type === 'table') {
    formConfig.value.columns = []
    formConfig.value.validations = { min_rows: 1, max_rows: 100 }
    formConfig.value.options = { allow_add: true, allow_delete: true, show_summary: false }
  } else if (type === 'narrative') {
    formConfig.value.fields = [{
      name: 'uraian',
      label: 'Uraian',
      type: 'richtext',
      required: true,
      min_length: 100,
      max_length: 5000
    }]
  } else if (type === 'checklist') {
    formConfig.value.items = []
  } else if (type === 'metric') {
    formConfig.value.metrics = []
  }
}

// Table Columns
const addColumn = () => {
  formConfig.value.columns.push({
    name: '',
    label: '',
    type: 'text',
    required: false,
    width: '20%',
    placeholder: ''
  })
}

const removeColumn = (index) => {
  formConfig.value.columns.splice(index, 1)
}

const handleColumnTypeChange = (column) => {
  if (column.type === 'select' && !column.options) {
    column.options = {}
    column.optionsText = ''
  }
}

const parseSelectOptions = (column) => {
  if (!column.optionsText) return

  const options = {}
  const lines = column.optionsText.split('\n')

  lines.forEach(line => {
    const [key, value] = line.split('=').map(s => s.trim())
    if (key && value) {
      options[key] = value
    }
  })

  column.options = options
}

// Checklist Items
const addChecklistItem = () => {
  const newId = formConfig.value.items.length > 0
    ? Math.max(...formConfig.value.items.map(i => i.id)) + 1
    : 1

  formConfig.value.items.push({
    id: newId,
    label: '',
    description: '',
    required: false
  })
}

const removeChecklistItem = (index) => {
  formConfig.value.items.splice(index, 1)
}

// Metrics
const addMetric = () => {
  formConfig.value.metrics.push({
    name: '',
    label: '',
    type: 'number',
    required: false,
    help_text: ''
  })
}

const removeMetric = (index) => {
  formConfig.value.metrics.splice(index, 1)
}

// Save Template
const saveTemplate = async () => {
  try {
    // Validate
    if (!formConfig.value.label) {
      alert('Label template wajib diisi!')
      return
    }

    if (formConfig.value.type === 'table' && formConfig.value.columns.length === 0) {
      alert('Tambahkan minimal 1 kolom untuk tipe tabel!')
      return
    }

    if (formConfig.value.type === 'checklist' && formConfig.value.items.length === 0) {
      alert('Tambahkan minimal 1 item untuk tipe checklist!')
      return
    }

    if (formConfig.value.type === 'metric' && formConfig.value.metrics.length === 0) {
      alert('Tambahkan minimal 1 metrik!')
      return
    }

    // Clean up optionsText before saving
    if (formConfig.value.type === 'table') {
      formConfig.value.columns.forEach(col => {
        delete col.optionsText
      })
    }

    await saveTemplateApi(butirId, { form_config: formConfig.value })
    alert('Template berhasil disimpan!')
    router.push('/akreditasi/butir-templates')
  } catch (error) {
    alert('Gagal menyimpan template: ' + (error.response?.data?.message || error.message))
  }
}

// Load existing template
const loadTemplate = async () => {
  try {
    const response = await getTemplateById(butirId)
    butirInfo.value = response.butir

    if (response.template) {
      isEditMode.value = true
      formConfig.value = response.template

      // Convert select options to text format for editing
      if (formConfig.value.type === 'table' && formConfig.value.columns) {
        formConfig.value.columns.forEach(col => {
          if (col.type === 'select' && col.options) {
            col.optionsText = Object.entries(col.options)
              .map(([key, value]) => `${key}=${value}`)
              .join('\n')
          }
        })
      }
    } else {
      // New template - use butir name as default label
      formConfig.value.label = `Data ${butirInfo.value.nama}`
      formConfig.value.help_text = `Isikan data ${butirInfo.value.nama} sesuai dengan panduan BAN-PT`
    }
  } catch (error) {
    alert('Gagal memuat template: ' + (error.response?.data?.message || error.message))
    router.push('/akreditasi/butir-templates')
  }
}

onMounted(() => {
  loadTemplate()
})
</script>
