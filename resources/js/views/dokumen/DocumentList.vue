<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Manajemen Dokumen
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

        <!-- Tab Navigation -->
        <div class="mt-4 flex border-b border-gray-200 dark:border-gray-600">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="activeTab = tab.value"
            :class="[
              'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
              activeTab === tab.value
                ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-500'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            {{ tab.label }}
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
              v-model="filters.category_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchDocuments()"
            >
              <option value="">Semua Kategori</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchDocuments()"
            >
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="review">Review</option>
              <option value="approved">Disetujui</option>
              <option value="archived">Arsip</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.visibility"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchDocuments()"
            >
              <option value="">Semua Visibilitas</option>
              <option value="public">Publik</option>
              <option value="private">Privat</option>
              <option value="restricted">Terbatas</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Judul Dokumen</th>
              <th scope="col" class="px-6 py-3">Kategori</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Ukuran</th>
              <th scope="col" class="px-6 py-3">Versi</th>
              <th scope="col" class="px-6 py-3">Diupload</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="documents.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada dokumen
              </td>
            </tr>
            <tr v-else v-for="doc in documents" :key="doc.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <div class="flex items-start space-x-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg" :style="{ backgroundColor: doc.category?.color + '20' }">
                    <svg class="h-6 w-6" :style="{ color: doc.category?.color }" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
                      <path d="M14 2v6h6" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ doc.title }}</p>
                    <p v-if="doc.document_number" class="text-xs text-gray-500 dark:text-gray-400">
                      No: {{ doc.document_number }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ doc.file_name }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                  :style="{
                    backgroundColor: doc.category?.color + '20',
                    color: doc.category?.color
                  }"
                >
                  {{ doc.category?.name }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(doc.status)">
                  {{ getStatusLabel(doc.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ doc.formatted_size }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                v{{ doc.current_version }}
              </td>
              <td class="px-6 py-4">
                <p class="text-gray-900 dark:text-white">{{ doc.uploader?.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(doc.created_at) }}
                </p>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    @click="viewDocument(doc)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Detail"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button
                    @click="downloadDocument(doc)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Download"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </button>
                  <button
                    v-if="canEdit(doc)"
                    @click="shareDocument(doc)"
                    class="text-purple-600 hover:text-purple-700 dark:text-purple-400"
                    title="Bagikan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-5.368m0 5.368a3 3 0 105.368 0m-4.681 0a3.013 3.013 0 01.633-.343A3.013 3.013 0 0112 13.5c.584 0 1.144-.13 1.633-.343m-4.266 0C8.52 14.81 7.5 16.51 7.5 18.5h9c0-1.99-1.02-3.69-2.867-5.343" />
                    </svg>
                  </button>
                  <button
                    v-if="canEdit(doc)"
                    @click="confirmDelete(doc)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400"
                    title="Hapus"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="text-sm text-gray-700 dark:text-gray-400">
          Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ pagination.from }}</span> sampai
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.to }}</span> dari
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> dokumen
        </div>
        <div class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'rounded-lg border px-3 py-2 text-sm font-medium',
              page === pagination.current_page
                ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-700'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Modal Placeholder -->
    <DocumentUploadModal
      v-if="showUploadModal"
      @close="showUploadModal = false"
      @uploaded="handleDocumentUploaded"
    />

    <!-- Share Modal Placeholder -->
    <DocumentShareModal
      v-if="showShareModal"
      :document="selectedDocument"
      @close="showShareModal = false"
      @shared="handleDocumentShared"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import DocumentUploadModal from '@/components/dokumen/DocumentUploadModal.vue';
import DocumentShareModal from '@/components/dokumen/DocumentShareModal.vue';
import axios from 'axios';

const router = useRouter();

const documents = ref([]);
const categories = ref([]);
const loading = ref(false);
const showUploadModal = ref(false);
const showShareModal = ref(false);
const selectedDocument = ref(null);

const activeTab = ref('all');
const tabs = [
  { value: 'all', label: 'Semua Dokumen' },
  { value: 'my', label: 'Dokumen Saya' },
  { value: 'shared', label: 'Dibagikan Dengan Saya' },
];

const filters = ref({
  search: '',
  category_id: '',
  status: '',
  visibility: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  let start = Math.max(1, current - 2);
  let end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchDocuments();
  }, 500);
};

const fetchDocuments = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    let endpoint = '/api/documents';
    if (activeTab.value === 'my') {
      endpoint = '/api/documents/my-documents';
    } else if (activeTab.value === 'shared') {
      endpoint = '/api/documents/shared-with-me';
    }

    const response = await axios.get(endpoint, { params });

    if (response.data.success) {
      documents.value = response.data.data;
      pagination.value = response.data.meta;
    }
  } catch (error) {
    console.error('Failed to fetch documents:', error);
  } finally {
    loading.value = false;
  }
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

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchDocuments(page);
  }
};

const viewDocument = (doc) => {
  router.push(`/dokumen/${doc.id}`);
};

const shareDocument = (doc) => {
  selectedDocument.value = doc;
  showShareModal.value = true;
};

const canEdit = (doc) => {
  // Simple check - in real app, check user permissions
  return true; // Will be controlled by backend
};

const confirmDelete = async (doc) => {
  if (confirm(`Apakah Anda yakin ingin menghapus dokumen "${doc.title}"?`)) {
    try {
      const response = await axios.delete(`/api/documents/${doc.id}`);
      if (response.data.success) {
        alert('Dokumen berhasil dihapus');
        fetchDocuments(pagination.value.current_page);
      }
    } catch (error) {
      alert('Gagal menghapus dokumen: ' + (error.response?.data?.message || error.message));
    }
  }
};

const downloadDocument = async (doc) => {
  try {
    const response = await axios.get(`/api/documents/${doc.id}/download`, {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = window.document.createElement('a');
    link.href = url;
    link.setAttribute('download', doc.file_name);
    window.document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Download error:', error);
    alert('Gagal mengunduh dokumen: ' + (error.response?.data?.message || error.message));
  }
};

const handleDocumentUploaded = () => {
  showUploadModal.value = false;
  fetchDocuments(1);
};

const handleDocumentShared = () => {
  showShareModal.value = false;
  alert('Dokumen berhasil dibagikan');
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    archived: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[status] || ''}`;
};

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    review: 'Review',
    approved: 'Disetujui',
    archived: 'Arsip',
  };
  return labels[status] || status;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

watch(activeTab, () => {
  fetchDocuments(1);
});

onMounted(() => {
  fetchCategories();
  fetchDocuments();
});
</script>
