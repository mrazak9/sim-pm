<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Bagikan Dokumen
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

      <!-- Document Info -->
      <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
        <div class="flex items-center space-x-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
              <path d="M14 2v6h6" />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">{{ document?.title }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ document?.file_name }}</p>
          </div>
        </div>
      </div>

      <!-- Share Form -->
      <form @submit.prevent="handleShare" class="mb-6">
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Bagikan ke User <span class="text-red-500">*</span>
          </label>
          <select
            v-model="shareForm.user_id"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Pilih User</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Izin Akses <span class="text-red-500">*</span>
          </label>
          <div class="space-y-2">
            <label class="flex items-center">
              <input
                v-model="shareForm.permission"
                type="radio"
                value="view"
                class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-900 dark:text-white">
                <strong>View</strong> - Hanya dapat melihat dokumen
              </span>
            </label>
            <label class="flex items-center">
              <input
                v-model="shareForm.permission"
                type="radio"
                value="download"
                class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-900 dark:text-white">
                <strong>Download</strong> - Dapat melihat dan mengunduh
              </span>
            </label>
            <label class="flex items-center">
              <input
                v-model="shareForm.permission"
                type="radio"
                value="edit"
                class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-900 dark:text-white">
                <strong>Edit</strong> - Dapat melihat, mengunduh, dan mengedit
              </span>
            </label>
          </div>
        </div>

        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Berlaku Sampai
          </label>
          <input
            v-model="shareForm.expires_at"
            type="datetime-local"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Kosongkan jika tidak ada batas waktu
          </p>
        </div>

        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Catatan
          </label>
          <textarea
            v-model="shareForm.notes"
            rows="2"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Catatan tambahan (opsional)"
          ></textarea>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
          {{ errorMessage }}
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            :disabled="loading"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ loading ? 'Membagikan...' : 'Bagikan' }}
          </button>
        </div>
      </form>

      <!-- Existing Shares -->
      <div v-if="document?.shares && document.shares.length > 0">
        <h4 class="mb-3 font-medium text-gray-900 dark:text-white">
          Dibagikan Kepada
        </h4>
        <div class="space-y-2">
          <div
            v-for="share in document.shares"
            :key="share.id"
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-700"
          >
            <div class="flex items-center space-x-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">
                  {{ share.recipient?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ share.recipient?.name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ getPermissionLabel(share.permission) }}
                  <span v-if="share.expires_at">
                    · Berlaku sampai {{ formatDateTime(share.expires_at) }}
                  </span>
                </p>
              </div>
            </div>
            <button
              @click="confirmRevokeShare(share)"
              class="text-red-600 hover:text-red-700 dark:text-red-400"
              title="Cabut akses"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  document: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'shared']);

const users = ref([]);
const loading = ref(false);
const errorMessage = ref('');

const shareForm = ref({
  user_id: '',
  permission: 'view',
  expires_at: '',
  notes: '',
});

const handleShare = async () => {
  if (!shareForm.value.user_id) {
    errorMessage.value = 'Silakan pilih user terlebih dahulu';
    return;
  }

  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await axios.post(`/api/documents/${props.document.id}/share`, shareForm.value);

    if (response.data.success) {
      // Reset form
      shareForm.value = {
        user_id: '',
        permission: 'view',
        expires_at: '',
        notes: '',
      };

      emit('shared', response.data.data);
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal membagikan dokumen';
    console.error('Share error:', error);
  } finally {
    loading.value = false;
  }
};

const confirmRevokeShare = async (share) => {
  if (confirm(`Cabut akses ${share.recipient?.name} dari dokumen ini?`)) {
    try {
      const response = await axios.delete(`/api/document-shares/${share.id}`);
      if (response.data.success) {
        alert('Akses berhasil dicabut');
        // Refresh document data
        emit('shared');
      }
    } catch (error) {
      alert('Gagal mencabut akses: ' + (error.response?.data?.message || error.message));
    }
  }
};

const getPermissionLabel = (permission) => {
  const labels = {
    view: 'Lihat',
    download: 'Download',
    edit: 'Edit',
  };
  return labels[permission] || permission;
};

const formatDateTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/users');
    if (response.data.success) {
      users.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch users:', error);
  }
};

onMounted(() => {
  fetchUsers();
});
</script>
