<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Dokumen
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
          ></textarea>
        </div>

        <!-- Visibility -->
        <div class="mb-4">
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

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
          {{ errorMessage }}
        </div>

        <!-- Action Buttons -->
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
            {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </form>
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

const emit = defineEmits(['close', 'updated']);

const categories = ref([]);
const loading = ref(false);
const errorMessage = ref('');

const formData = ref({
  title: props.document.title,
  document_number: props.document.document_number,
  category_id: props.document.category_id,
  description: props.document.description,
  visibility: props.document.visibility,
  retention_until: props.document.retention_until,
});

const handleSubmit = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await axios.put(`/api/documents/${props.document.id}`, formData.value);

    if (response.data.success) {
      emit('updated', response.data.data);
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memperbarui dokumen';
    console.error('Update error:', error);
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

onMounted(() => {
  fetchCategories();
});
</script>
