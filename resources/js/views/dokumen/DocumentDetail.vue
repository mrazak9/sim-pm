<template>
  <MainLayout>
    <div v-if="loading" class="flex items-center justify-center py-12">
      <svg class="h-12 w-12 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <div v-else-if="document" class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button
            @click="$router.back()"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ document.title }}</h1>
            <p v-if="document.document_number" class="text-sm text-gray-500 dark:text-gray-400">
              No: {{ document.document_number }}
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <a
            :href="document.download_url"
            target="_blank"
            class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download
          </a>
          <button
            v-if="canEdit"
            @click="showEditModal = true"
            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </button>
        </div>
      </div>

      <!-- Document Preview -->
      <div v-if="canPreview" class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Preview Dokumen</h2>

        <!-- PDF Preview -->
        <div v-if="document.file_type === 'pdf'" class="w-full" style="height: 600px;">
          <iframe
            :src="document.view_url"
            class="h-full w-full rounded border border-gray-300 dark:border-gray-600"
            frameborder="0"
          ></iframe>
        </div>

        <!-- Image Preview -->
        <div v-else-if="isImageType" class="flex justify-center">
          <img
            :src="document.view_url"
            :alt="document.title"
            class="max-h-96 rounded border border-gray-300 dark:border-gray-600"
          />
        </div>

        <!-- Other file types - show download option -->
        <div v-else class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Preview tidak tersedia untuk tipe file ini
          </p>
          <p class="mt-1 text-xs text-gray-400">
            Silakan download untuk melihat file
          </p>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left Column: Document Info & Metadata -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Document Information Card -->
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Dokumen</h2>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</p>
                  <span
                    class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-medium"
                    :style="{
                      backgroundColor: document.category?.color + '20',
                      color: document.category?.color
                    }"
                  >
                    {{ document.category?.name }}
                  </span>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                  <span :class="getStatusClass(document.status)" class="mt-1">
                    {{ getStatusLabel(document.status) }}
                  </span>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Visibilitas</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ getVisibilityLabel(document.visibility) }}
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Versi</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">v{{ document.current_version }}</p>
                </div>
              </div>

              <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                  {{ document.description || '-' }}
                </p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.file_name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ukuran File</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.formatted_size }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipe File</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.file_type.toUpperCase() }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">MIME Type</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.mime_type }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Diupload Oleh</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.uploader?.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Upload</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(document.created_at) }}</p>
                </div>
                <div v-if="document.approved_by">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Disetujui Oleh</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.approver?.name }}</p>
                </div>
                <div v-if="document.approved_at">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Disetujui</p>
                  <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(document.approved_at) }}</p>
                </div>
              </div>

              <div v-if="document.retention_until">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Retensi Sampai</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(document.retention_until) }}</p>
              </div>
            </div>
          </div>

          <!-- Version History Card -->
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Versi</h2>
              <button
                v-if="canEdit"
                @click="showUploadVersionModal = true"
                class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
              >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Upload Versi Baru
              </button>
            </div>

            <div class="space-y-3">
              <div
                v-for="version in document.versions"
                :key="version.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-600"
                :class="version.version_number === document.current_version ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-700'"
              >
                <div class="flex items-center space-x-3">
                  <div
                    class="flex h-10 w-10 items-center justify-center rounded-full"
                    :class="version.version_number === document.current_version ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-700 dark:bg-gray-600 dark:text-gray-300'"
                  >
                    <span class="text-sm font-bold">v{{ version.version_number }}</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ version.file_name }}
                      <span v-if="version.version_number === document.current_version" class="ml-2 text-xs text-blue-600 dark:text-blue-400">
                        (Current)
                      </span>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ version.change_notes || 'No notes' }} • {{ version.uploader?.name }} • {{ formatDate(version.created_at) }}
                    </p>
                  </div>
                </div>
                <div v-if="version.version_number !== document.current_version && canEdit" class="flex items-center space-x-2">
                  <button
                    @click="restoreVersion(version.version_number)"
                    class="rounded-lg border border-blue-600 px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-900/20"
                  >
                    Restore
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Actions & Sharing -->
        <div class="space-y-6">
          <!-- Status Workflow Card -->
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Status Workflow</h3>

            <div class="space-y-2">
              <button
                v-for="status in availableStatusTransitions"
                :key="status"
                @click="updateStatus(status)"
                :disabled="updatingStatus"
                class="w-full rounded-lg border-2 border-dashed border-gray-300 px-4 py-3 text-left text-sm font-medium text-gray-700 transition-colors hover:border-blue-500 hover:bg-blue-50 disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-900/20"
              >
                <span class="flex items-center justify-between">
                  <span>Ubah ke {{ getStatusLabel(status) }}</span>
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </span>
              </button>
            </div>
          </div>

          <!-- Sharing Card -->
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Berbagi Dokumen</h3>
              <button
                v-if="canEdit"
                @click="showShareModal = true"
                class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700"
              >
                + Bagikan
              </button>
            </div>

            <div v-if="document.shares && document.shares.length > 0" class="space-y-2">
              <div
                v-for="share in document.shares"
                :key="share.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-600"
              >
                <div class="flex items-center space-x-2">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <span class="text-xs font-medium text-purple-600 dark:text-purple-400">
                      {{ share.recipient?.name?.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ share.recipient?.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ getPermissionLabel(share.permission) }}</p>
                  </div>
                </div>
                <button
                  v-if="canEdit"
                  @click="revokeShare(share.id)"
                  class="text-red-600 hover:text-red-700 dark:text-red-400"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-500 dark:text-gray-400">Dokumen belum dibagikan</p>
          </div>

          <!-- Danger Zone -->
          <div v-if="canEdit" class="rounded-lg border-2 border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-900/20">
            <h3 class="mb-2 text-lg font-semibold text-red-900 dark:text-red-400">Danger Zone</h3>
            <p class="mb-4 text-sm text-red-700 dark:text-red-300">Tindakan berikut tidak dapat dibatalkan</p>
            <button
              @click="confirmDelete"
              class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
            >
              Hapus Dokumen
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <DocumentEditModal
      v-if="showEditModal"
      :document="document"
      @close="showEditModal = false"
      @updated="handleDocumentUpdated"
    />

    <DocumentUploadVersionModal
      v-if="showUploadVersionModal"
      :document="document"
      @close="showUploadVersionModal = false"
      @uploaded="handleVersionUploaded"
    />

    <DocumentShareModal
      v-if="showShareModal"
      :document="document"
      @close="showShareModal = false"
      @shared="handleDocumentShared"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import DocumentEditModal from '@/components/dokumen/DocumentEditModal.vue';
import DocumentUploadVersionModal from '@/components/dokumen/DocumentUploadVersionModal.vue';
import DocumentShareModal from '@/components/dokumen/DocumentShareModal.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const document = ref(null);
const loading = ref(true);
const updatingStatus = ref(false);
const showEditModal = ref(false);
const showUploadVersionModal = ref(false);
const showShareModal = ref(false);

const canEdit = computed(() => {
  return document.value?.user_permission === 'edit';
});

const canPreview = computed(() => {
  if (!document.value) return false;
  const previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
  return previewableTypes.includes(document.value.file_type?.toLowerCase());
});

const isImageType = computed(() => {
  if (!document.value) return false;
  const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
  return imageTypes.includes(document.value.file_type?.toLowerCase());
});

const availableStatusTransitions = computed(() => {
  if (!document.value) return [];

  const transitions = {
    draft: ['review'],
    review: ['approved', 'draft'],
    approved: ['archived'],
    archived: [],
  };

  return transitions[document.value.status] || [];
});

const fetchDocument = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/documents/${route.params.id}`);
    if (response.data.success) {
      document.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch document:', error);
    alert('Gagal memuat dokumen');
    router.push('/dokumen');
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (newStatus) => {
  if (!confirm(`Ubah status dokumen ke ${getStatusLabel(newStatus)}?`)) return;

  updatingStatus.value = true;
  try {
    const response = await axios.post(`/api/documents/${document.value.id}/update-status`, {
      status: newStatus
    });

    if (response.data.success) {
      document.value = response.data.data;
      alert('Status dokumen berhasil diperbarui');
    }
  } catch (error) {
    alert('Gagal memperbarui status: ' + (error.response?.data?.message || error.message));
  } finally {
    updatingStatus.value = false;
  }
};

const restoreVersion = async (versionNumber) => {
  if (!confirm(`Restore dokumen ke versi ${versionNumber}? Ini akan membuat versi baru.`)) return;

  try {
    const response = await axios.post(`/api/documents/${document.value.id}/restore-version/${versionNumber}`);

    if (response.data.success) {
      document.value = response.data.data;
      alert(`Berhasil restore ke versi ${versionNumber}`);
    }
  } catch (error) {
    alert('Gagal restore versi: ' + (error.response?.data?.message || error.message));
  }
};

const revokeShare = async (shareId) => {
  if (!confirm('Cabut akses dokumen ini?')) return;

  try {
    const response = await axios.delete(`/api/document-shares/${shareId}`);
    if (response.data.success) {
      await fetchDocument(); // Refresh document data
      alert('Akses berhasil dicabut');
    }
  } catch (error) {
    alert('Gagal mencabut akses: ' + (error.response?.data?.message || error.message));
  }
};

const confirmDelete = async () => {
  if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.')) return;

  try {
    const response = await axios.delete(`/api/documents/${document.value.id}`);
    if (response.data.success) {
      alert('Dokumen berhasil dihapus');
      router.push('/dokumen');
    }
  } catch (error) {
    alert('Gagal menghapus dokumen: ' + (error.response?.data?.message || error.message));
  }
};

const handleDocumentUpdated = () => {
  showEditModal.value = false;
  fetchDocument();
};

const handleVersionUploaded = () => {
  showUploadVersionModal.value = false;
  fetchDocument();
};

const handleDocumentShared = () => {
  showShareModal.value = false;
  fetchDocument();
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    archived: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-sm font-medium ${classes[status] || ''}`;
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

const getVisibilityLabel = (visibility) => {
  const labels = {
    public: 'Publik',
    private: 'Privat',
    restricted: 'Terbatas',
  };
  return labels[visibility] || visibility;
};

const getPermissionLabel = (permission) => {
  const labels = {
    view: 'Lihat',
    download: 'Download',
    edit: 'Edit',
  };
  return labels[permission] || permission;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

onMounted(() => {
  fetchDocument();
});
</script>
