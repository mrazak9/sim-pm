<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
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
        <!-- Name -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Nama Kategori <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.name"
            type="text"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Contoh: Dokumen Mutu"
          />
        </div>

        <!-- Code -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Kode Kategori <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.code"
            type="text"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Contoh: DM"
          />
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Deskripsi
          </label>
          <textarea
            v-model="formData.description"
            rows="2"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            placeholder="Deskripsi kategori..."
          ></textarea>
        </div>

        <!-- Parent Category -->
        <div class="mb-4">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Parent Kategori
          </label>
          <select
            v-model="formData.parent_id"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option :value="null">Tanpa Parent (Root Category)</option>
            <option
              v-for="cat in availableParents"
              :key="cat.id"
              :value="cat.id"
              :disabled="cat.id === category?.id"
            >
              {{ cat.name }} ({{ cat.code }})
            </option>
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Pilih parent kategori untuk membuat hierarki
          </p>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
          <!-- Icon -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Icon
            </label>
            <input
              v-model="formData.icon"
              type="text"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Contoh: folder, document"
            />
          </div>

          <!-- Color -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Warna <span class="text-red-500">*</span>
            </label>
            <div class="flex space-x-2">
              <input
                v-model="formData.color"
                type="color"
                class="h-10 w-16 rounded-lg border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700"
              />
              <input
                v-model="formData.color"
                type="text"
                pattern="^#[0-9A-Fa-f]{6}$"
                class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="#3B82F6"
              />
            </div>
          </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
          <!-- Order -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Urutan
            </label>
            <input
              v-model.number="formData.order"
              type="number"
              min="0"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Active Status -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Status
            </label>
            <label class="flex items-center space-x-2">
              <input
                v-model="formData.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-900 dark:text-white">Aktif</span>
            </label>
          </div>
        </div>

        <!-- Color Preview -->
        <div class="mb-6 rounded-lg border border-gray-200 p-4 dark:border-gray-600">
          <p class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Preview:</p>
          <div class="flex items-center space-x-3">
            <div
              class="h-10 w-10 rounded-lg"
              :style="{ backgroundColor: formData.color }"
            ></div>
            <span
              class="inline-flex rounded-full px-4 py-2 text-sm font-medium"
              :style="{
                backgroundColor: formData.color + '20',
                color: formData.color
              }"
            >
              {{ formData.name || 'Nama Kategori' }}
            </span>
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
            {{ loading ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Tambah Kategori' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  category: {
    type: Object,
    default: null,
  },
  parentId: {
    type: Number,
    default: null,
  },
  categories: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(false);
const errorMessage = ref('');

const isEdit = computed(() => props.category !== null);

const formData = ref({
  name: props.category?.name || '',
  code: props.category?.code || '',
  description: props.category?.description || '',
  parent_id: props.parentId || props.category?.parent_id || null,
  icon: props.category?.icon || '',
  color: props.category?.color || '#3B82F6',
  order: props.category?.order || 0,
  is_active: props.category?.is_active !== undefined ? props.category.is_active : true,
});

const availableParents = computed(() => {
  // If editing, exclude self and descendants to prevent circular reference
  if (isEdit.value) {
    return props.categories.filter(cat => cat.id !== props.category.id);
  }
  return props.categories;
});

const handleSubmit = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const url = isEdit.value
      ? `/api/document-categories/${props.category.id}`
      : '/api/document-categories';

    const method = isEdit.value ? 'put' : 'post';

    const response = await axios[method](url, formData.value);

    if (response.data.success) {
      emit('saved', response.data.data);
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || `Gagal ${isEdit.value ? 'memperbarui' : 'menambah'} kategori`;
    console.error('Save error:', error);
  } finally {
    loading.value = false;
  }
};
</script>
