<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Kategori Dokumen
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Kelola kategori dokumen untuk sistem manajemen dokumen
            </p>
          </div>
          <button
            @click="addNewRow()"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3 w-8">#</th>
              <th scope="col" class="px-6 py-3">Nama</th>
              <th scope="col" class="px-6 py-3">Kode</th>
              <th scope="col" class="px-6 py-3">Deskripsi</th>
              <th scope="col" class="px-6 py-3 w-24">Warna</th>
              <th scope="col" class="px-6 py-3 w-20">Urutan</th>
              <th scope="col" class="px-6 py-3 w-24">Status</th>
              <th scope="col" class="px-6 py-3 w-40">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="categories.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Belum ada kategori. Klik "Tambah Kategori" untuk membuat kategori baru.
              </td>
            </tr>
            <template v-else>
              <tr
                v-for="(category, index) in sortedCategories"
                :key="category.id || category.tempId"
                class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                :class="{ 'bg-yellow-50 dark:bg-yellow-900/10': category.isEditing }"
              >
                <td class="px-6 py-4 text-gray-900 dark:text-white">
                  {{ index + 1 }}
                </td>
                <td class="px-6 py-4">
                  <input
                    v-if="category.isEditing"
                    v-model="category.name"
                    type="text"
                    required
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Nama kategori"
                  />
                  <span v-else class="font-medium text-gray-900 dark:text-white">
                    {{ category.name }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <input
                    v-if="category.isEditing"
                    v-model="category.code"
                    type="text"
                    required
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="KODE"
                  />
                  <span v-else class="font-mono text-xs text-gray-700 dark:text-gray-300">
                    {{ category.code }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <input
                    v-if="category.isEditing"
                    v-model="category.description"
                    type="text"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Deskripsi"
                  />
                  <span v-else class="text-gray-600 dark:text-gray-400">
                    {{ category.description || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <input
                      v-if="category.isEditing"
                      v-model="category.color"
                      type="color"
                      class="h-8 w-16 cursor-pointer rounded border border-gray-300 dark:border-gray-600"
                    />
                    <div
                      v-else
                      class="flex items-center gap-2"
                    >
                      <div
                        class="h-6 w-6 rounded border border-gray-300 dark:border-gray-600"
                        :style="{ backgroundColor: category.color }"
                      ></div>
                      <span class="text-xs text-gray-600 dark:text-gray-400">
                        {{ category.color }}
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <input
                    v-if="category.isEditing"
                    v-model.number="category.order"
                    type="number"
                    min="0"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  />
                  <span v-else class="text-gray-900 dark:text-white">
                    {{ category.order }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <label v-if="category.isEditing" class="flex items-center">
                    <input
                      v-model="category.is_active"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                    />
                    <span class="ml-2 text-sm text-gray-900 dark:text-white">Aktif</span>
                  </label>
                  <span
                    v-else
                    :class="[
                      'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                      category.is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                    ]"
                  >
                    {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <template v-if="category.isEditing">
                      <button
                        @click="saveCategory(category)"
                        :disabled="saving"
                        class="rounded bg-green-600 px-3 py-1 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50"
                        title="Simpan"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                      <button
                        @click="cancelEdit(category)"
                        :disabled="saving"
                        class="rounded bg-gray-600 px-3 py-1 text-xs font-medium text-white hover:bg-gray-700 disabled:opacity-50"
                        title="Batal"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </template>
                    <template v-else>
                      <button
                        @click="editCategory(category)"
                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        title="Edit"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        @click="confirmDelete(category)"
                        class="text-red-600 hover:text-red-700 dark:text-red-400"
                        title="Hapus"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

const { success, error } = useToast();

const categories = ref([]);
const loading = ref(false);
const saving = ref(false);
let tempIdCounter = 0;

const sortedCategories = computed(() => {
  return [...categories.value].sort((a, b) => a.order - b.order);
});

const fetchCategories = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/document-categories', {
      params: { active_only: false }
    });
    if (response.data.success) {
      categories.value = response.data.data.map(cat => ({
        ...cat,
        isEditing: false,
        originalData: null
      }));
    }
  } catch (err) {
    console.error('Failed to fetch categories:', err);
    error('Gagal memuat kategori');
  } finally {
    loading.value = false;
  }
};

const addNewRow = () => {
  const newCategory = {
    tempId: `temp_${tempIdCounter++}`,
    name: '',
    code: '',
    description: '',
    color: '#3B82F6',
    order: categories.value.length > 0 ? Math.max(...categories.value.map(c => c.order)) + 1 : 1,
    is_active: true,
    isEditing: true,
    isNew: true,
    originalData: null
  };
  categories.value.unshift(newCategory);
};

const editCategory = (category) => {
  // Save original data for cancel
  category.originalData = {
    name: category.name,
    code: category.code,
    description: category.description,
    color: category.color,
    order: category.order,
    is_active: category.is_active
  };
  category.isEditing = true;
};

const cancelEdit = (category) => {
  if (category.isNew) {
    // Remove new unsaved row
    const index = categories.value.findIndex(c =>
      c.tempId ? c.tempId === category.tempId : c.id === category.id
    );
    if (index > -1) {
      categories.value.splice(index, 1);
    }
  } else {
    // Restore original data
    if (category.originalData) {
      Object.assign(category, category.originalData);
      category.originalData = null;
    }
    category.isEditing = false;
  }
};

const saveCategory = async (category) => {
  // Validation
  if (!category.name || !category.code) {
    error('Nama dan Kode harus diisi');
    return;
  }

  saving.value = true;
  try {
    const data = {
      name: category.name,
      code: category.code.toUpperCase(),
      description: category.description,
      color: category.color,
      order: category.order,
      is_active: category.is_active
    };

    let response;
    if (category.isNew) {
      // Create new
      response = await axios.post('/api/document-categories', data);
    } else {
      // Update existing
      response = await axios.put(`/api/document-categories/${category.id}`, data);
    }

    if (response.data.success) {
      success(category.isNew ? 'Kategori berhasil ditambahkan' : 'Kategori berhasil diupdate');
      await fetchCategories();
    }
  } catch (err) {
    console.error('Save error:', err);
    error('Gagal menyimpan kategori: ' + (err.response?.data?.message || err.message));
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (category) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus kategori "${category.name}"?`)) {
    return;
  }

  try {
    const response = await axios.delete(`/api/document-categories/${category.id}`);
    if (response.data.success) {
      success('Kategori berhasil dihapus');
      await fetchCategories();
    }
  } catch (err) {
    console.error('Delete error:', err);
    error('Gagal menghapus kategori: ' + (err.response?.data?.message || err.message));
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
