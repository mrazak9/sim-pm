<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Dokumen Akreditasi
          </h3>
          <button
            @click="showUploadModal = true"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Upload Dokumen
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari dokumen..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.periode_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchDokumens"
            >
              <option value="">Semua Periode</option>
              <option v-for="periode in periodeList" :key="periode.id" :value="periode.id">
                {{ periode.nama }}
              </option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.butir_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchDokumens"
            >
              <option value="">Semua Butir</option>
              <option v-for="butir in butirList" :key="butir.id" :value="butir.id">
                {{ butir.kode }} - {{ butir.nama }}
              </option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.jenis_dokumen"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchDokumens"
            >
              <option value="">Semua Jenis</option>
              <option value="kebijakan">Kebijakan</option>
              <option value="pedoman">Pedoman</option>
              <option value="manual">Manual</option>
              <option value="sop">SOP</option>
              <option value="formulir">Formulir</option>
              <option value="instruksi_kerja">Instruksi Kerja</option>
              <option value="laporan">Laporan</option>
              <option value="sertifikat">Sertifikat</option>
              <option value="sk">SK</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
      </div>

      <!-- Documents Grid -->
      <div v-else class="p-6">
        <div v-if="dokumens.length === 0" class="py-12 text-center text-gray-500 dark:text-gray-400">
          Tidak ada dokumen
        </div>
        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="dokumen in dokumens"
            :key="dokumen.id"
            class="rounded-lg border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
          >
            <!-- File Icon & Name -->
            <div class="mb-3 flex items-start gap-3">
              <div class="flex-shrink-0">
                <svg class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="truncate font-medium text-gray-900 dark:text-white" :title="dokumen.nama_file">
                  {{ dokumen.nama_file }}
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatFileSize(dokumen.file_size) }}
                </p>
              </div>
            </div>

            <!-- Deskripsi -->
            <p v-if="dokumen.deskripsi" class="mb-3 text-sm text-gray-600 dark:text-gray-400">
              {{ truncate(dokumen.deskripsi, 60) }}
            </p>

            <!-- Meta Info -->
            <div class="mb-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
              <div v-if="dokumen.jenis_dokumen" class="flex items-center gap-1">
                <span class="font-medium">Jenis:</span>
                <span class="capitalize">{{ dokumen.jenis_dokumen }}</span>
              </div>
              <div v-if="dokumen.periode_akreditasi" class="flex items-center gap-1">
                <span class="font-medium">Periode:</span>
                <span>{{ dokumen.periode_akreditasi.nama }}</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="font-medium">Upload:</span>
                <span>{{ formatDate(dokumen.created_at) }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 border-t border-gray-200 pt-3 dark:border-gray-700">
              <button
                @click="downloadDokumen(dokumen)"
                class="flex-1 rounded bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
              >
                Download
              </button>
              <button
                @click="viewDokumen(dokumen)"
                class="flex-1 rounded bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
              >
                Detail
              </button>
              <button
                @click="confirmDelete(dokumen)"
                class="rounded bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                title="Hapus"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <div
      v-if="showUploadModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
      @click.self="showUploadModal = false"
    >
      <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Dokumen</h3>
          <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleUpload" class="space-y-4">
          <!-- File Input -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              File <span class="text-red-500">*</span>
            </label>
            <input
              ref="fileInputRef"
              type="file"
              required
              @change="handleFileChange"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Maksimal 10MB. Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
            </p>
          </div>

          <!-- Nama Dokumen -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Nama Dokumen
            </label>
            <input
              v-model="uploadForm.nama_dokumen"
              type="text"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Akan terisi otomatis dari nama file"
            />
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="uploadForm.deskripsi"
              rows="3"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Deskripsi dokumen..."
            ></textarea>
          </div>

          <!-- Periode & Jenis -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Periode Akreditasi
              </label>
              <select
                v-model="uploadForm.periode_akreditasi_id"
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
                Jenis Dokumen
              </label>
              <select
                v-model="uploadForm.jenis_dokumen"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Jenis</option>
                <option value="kebijakan">Kebijakan</option>
                <option value="pedoman">Pedoman</option>
                <option value="manual">Manual</option>
                <option value="sop">SOP</option>
                <option value="formulir">Formulir</option>
                <option value="instruksi_kerja">Instruksi Kerja</option>
                <option value="laporan">Laporan</option>
                <option value="sertifikat">Sertifikat</option>
                <option value="sk">SK</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
          </div>

          <!-- Butir Akreditasi (Multiple Select) -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Kaitkan dengan Butir Akreditasi
            </label>
            <select
              v-model="uploadForm.butir_ids"
              multiple
              size="5"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option v-for="butir in butirList" :key="butir.id" :value="butir.id">
                {{ butir.kode }} - {{ butir.nama }}
              </option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Tahan Ctrl/Cmd untuk memilih lebih dari satu
            </p>
          </div>

          <!-- Upload Progress -->
          <div v-if="uploadProgress > 0 && uploadProgress < 100" class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-700 dark:text-gray-300">Uploading...</span>
              <span class="text-gray-700 dark:text-gray-300">{{ uploadProgress }}%</span>
            </div>
            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
              <div
                class="h-2 rounded-full bg-blue-600 transition-all"
                :style="{ width: uploadProgress + '%' }"
              ></div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="uploadError" class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
            <p class="text-sm text-red-700 dark:text-red-400">{{ uploadError }}</p>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="showUploadModal = false"
              class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="uploading || !selectedFile"
              class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {{ uploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="showDeleteModal = false"
    >
      <div class="rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800" style="max-width: 400px">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
          Apakah Anda yakin ingin menghapus dokumen "{{ dokumenToDelete?.nama_file }}"?
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="deleteDokumen"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'

const {
  loading,
  getDokumenList,
  uploadDokumen,
  deleteDokumen: deleteDokumenApi,
  downloadDokumen: downloadDokumenApi,
  getPeriodeList,
  getButirList,
} = useAkreditasiApi()

const dokumens = ref([])
const periodeList = ref([])
const butirList = ref([])
const showUploadModal = ref(false)
const showDeleteModal = ref(false)
const dokumenToDelete = ref(null)
const selectedFile = ref(null)
const fileInputRef = ref(null)
const uploading = ref(false)
const uploadProgress = ref(0)
const uploadError = ref(null)

const filters = ref({
  search: '',
  periode_id: '',
  butir_id: '',
  jenis_dokumen: '',
})

const uploadForm = ref({
  nama_dokumen: '',
  deskripsi: '',
  periode_akreditasi_id: '',
  jenis_dokumen: '',
  butir_ids: [],
})

const fetchDokumens = async () => {
  try {
    const params = { ...filters.value }
    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key]
    })

    const response = await getDokumenList(params)
    dokumens.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Failed to fetch dokumens:', error)
  }
}

const fetchPeriodes = async () => {
  try {
    const response = await getPeriodeList()
    periodeList.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch periodes:', error)
  }
}

const fetchButirs = async () => {
  try {
    const response = await getButirList({ per_page: 'all' })
    butirList.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch butirs:', error)
  }
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchDokumens()
  }, 300)
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    if (!uploadForm.value.nama_dokumen) {
      uploadForm.value.nama_dokumen = file.name
    }
  }
}

const handleUpload = async () => {
  if (!selectedFile.value) {
    uploadError.value = 'Pilih file terlebih dahulu'
    return
  }

  uploading.value = true
  uploadProgress.value = 0
  uploadError.value = null

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('nama_dokumen', uploadForm.value.nama_dokumen || selectedFile.value.name)

    if (uploadForm.value.deskripsi) formData.append('deskripsi', uploadForm.value.deskripsi)
    if (uploadForm.value.periode_akreditasi_id) formData.append('periode_akreditasi_id', uploadForm.value.periode_akreditasi_id)
    if (uploadForm.value.jenis_dokumen) formData.append('jenis_dokumen', uploadForm.value.jenis_dokumen)

    // Attach butir IDs if selected
    if (uploadForm.value.butir_ids.length > 0) {
      uploadForm.value.butir_ids.forEach(id => {
        formData.append('butir_ids[]', id)
      })
    }

    await uploadDokumen(formData)

    // Reset form
    showUploadModal.value = false
    selectedFile.value = null
    uploadForm.value = {
      nama_dokumen: '',
      deskripsi: '',
      periode_akreditasi_id: '',
      jenis_dokumen: '',
      butir_ids: [],
    }
    if (fileInputRef.value) fileInputRef.value.value = ''

    await fetchDokumens()
  } catch (error) {
    console.error('Upload failed:', error)
    uploadError.value = error.response?.data?.message || 'Upload gagal'
  } finally {
    uploading.value = false
    uploadProgress.value = 0
  }
}

const downloadDokumen = async (dokumen) => {
  try {
    const response = await downloadDokumenApi(dokumen.id)
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', dokumen.nama_file)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download failed:', error)
    alert('Download gagal')
  }
}

const viewDokumen = (dokumen) => {
  // TODO: Implement view dokumen detail
  console.log('View dokumen:', dokumen.id)
}

const confirmDelete = (dokumen) => {
  dokumenToDelete.value = dokumen
  showDeleteModal.value = true
}

const deleteDokumen = async () => {
  try {
    await deleteDokumenApi(dokumenToDelete.value.id)
    showDeleteModal.value = false
    await fetchDokumens()
  } catch (error) {
    console.error('Delete failed:', error)
    alert('Hapus dokumen gagal')
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

onMounted(async () => {
  await fetchPeriodes()
  await fetchButirs()
  await fetchDokumens()
})
</script>
