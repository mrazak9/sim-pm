<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Upload Dokumen Baru
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <!-- File Upload Area -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            File Dokumen <span class="text-red-500">*</span>
          </label>
          <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            :class="[
              'rounded-lg border-2 border-dashed p-6 text-center transition-colors',
              isDragging
                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                : 'border-gray-300 dark:border-gray-600'
            ]"
          >
            <input
              ref="fileInput"
              type="file"
              @change="handleFileSelect"
              class="hidden"
            />

            <div v-if="!selectedFile">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Drag & drop file atau
                <button
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                >
                  klik untuk browse
                </button>
              </p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Maksimal ukuran file: 50MB
              </p>
            </div>

            <div v-else class="flex items-center justify-between rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
              <div class="flex items-center space-x-3">
                <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
                  <path d="M14 2v6h6" />
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ selectedFile.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatFileSize(selectedFile.size) }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                @click="selectedFile = null"
                class="text-red-600 hover:text-red-700 dark:text-red-400"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Title -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Judul Dokumen <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.title"
            type="text"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Masukkan judul dokumen"
          />
        </div>

        <!-- Document Number -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Nomor Dokumen
          </label>
          <input
            v-model="formData.document_number"
            type="text"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Contoh: DOC/2024/001"
          />
        </div>

        <!-- Category -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Kategori <span class="text-red-500">*</span>
          </label>
          <select
            v-model="formData.category_id"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Pilih Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Deskripsi
          </label>
          <textarea
            v-model="formData.description"
            rows="3"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Deskripsi dokumen..."
          ></textarea>
        </div>

        <!-- Status and Visibility Row -->
        <div class="mb-4 grid grid-cols-2 gap-4">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Status
            </label>
            <select
              v-model="formData.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="draft">Draft</option>
              <option value="review">Review</option>
              <option value="approved">Disetujui</option>
            </select>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Visibilitas
            </label>
            <select
              v-model="formData.visibility"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="private">Privat</option>
              <option value="public">Publik</option>
              <option value="restricted">Terbatas</option>
            </select>
          </div>
        </div>

        <!-- Retention Until -->
        <div class="mb-6">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Retensi Sampai
          </label>
          <input
            v-model="formData.retention_until"
            type="date"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          />
        </div>

        <!-- Upload Progress -->
        <div v-if="uploading" class="mb-4">
          <div class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300">
            <span>Mengupload...</span>
            <span>{{ uploadProgress }}%</span>
          </div>
          <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div
              class="h-full bg-blue-600 transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
          {{ errorMessage }}
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            :disabled="uploading"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="!selectedFile || uploading"
            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ uploading ? 'Mengupload...' : 'Upload Dokumen' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'uploaded']);

const selectedFile = ref(null);
const isDragging = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref('');
const categories = ref([]);

const formData = ref({
  title: '',
  document_number: '',
  category_id: '',
  description: '',
  status: 'draft',
  visibility: 'private',
  retention_until: '',
});

const fileInput = ref(null);

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 50 * 1024 * 1024) {
      errorMessage.value = 'Ukuran file maksimal 50MB';
      return;
    }
    selectedFile.value = file;
    errorMessage.value = '';

    // Auto-fill title if empty
    if (!formData.value.title) {
      formData.value.title = file.name.replace(/\.[^/.]+$/, '');
    }
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file) {
    if (file.size > 50 * 1024 * 1024) {
      errorMessage.value = 'Ukuran file maksimal 50MB';
      return;
    }
    selectedFile.value = file;
    errorMessage.value = '';

    if (!formData.value.title) {
      formData.value.title = file.name.replace(/\.[^/.]+$/, '');
    }
  }
};

const handleSubmit = async () => {
  if (!selectedFile.value) {
    errorMessage.value = 'Silakan pilih file terlebih dahulu';
    return;
  }

  uploading.value = true;
  errorMessage.value = '';
  uploadProgress.value = 0;

  try {
    const formDataToSend = new FormData();
    formDataToSend.append('file', selectedFile.value);
    formDataToSend.append('title', formData.value.title);
    formDataToSend.append('category_id', formData.value.category_id);

    if (formData.value.document_number) {
      formDataToSend.append('document_number', formData.value.document_number);
    }
    if (formData.value.description) {
      formDataToSend.append('description', formData.value.description);
    }
    if (formData.value.status) {
      formDataToSend.append('status', formData.value.status);
    }
    if (formData.value.visibility) {
      formDataToSend.append('visibility', formData.value.visibility);
    }
    if (formData.value.retention_until) {
      formDataToSend.append('retention_until', formData.value.retention_until);
    }

    const response = await axios.post('/api/documents', formDataToSend, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
      },
    });

    if (response.data.success) {
      emit('uploaded', response.data.data);
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengupload dokumen';
    console.error('Upload error:', error);
  } finally {
    uploading.value = false;
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/document-categories', {
      params: { active_only: true }
    });
    if (response.data.success) {
      categories.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
