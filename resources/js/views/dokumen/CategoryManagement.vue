<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Kategori Dokumen
          </h3>
          <button
            @click="openCreateModal()"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
          </button>
        </div>
      </div>

      <!-- Category Tree View -->
      <div class="p-6">
        <div v-if="loading" class="flex items-center justify-center py-12">
          <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <div v-else-if="categories.length === 0" class="py-12 text-center text-gray-500 dark:text-gray-400">
          Belum ada kategori. Klik "Tambah Kategori" untuk membuat kategori baru.
        </div>

        <div v-else class="space-y-2">
          <CategoryTreeItem
            v-for="category in rootCategories"
            :key="category.id"
            :category="category"
            :level="0"
            @edit="openEditModal"
            @delete="confirmDelete"
            @add-child="openCreateModal"
          />
        </div>
      </div>
    </div>

    <!-- Category Form Modal -->
    <CategoryFormModal
      v-if="showFormModal"
      :category="selectedCategory"
      :parentId="selectedParentId"
      :categories="categories"
      @close="closeFormModal"
      @saved="handleCategorySaved"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import CategoryTreeItem from '@/components/dokumen/CategoryTreeItem.vue';
import CategoryFormModal from '@/components/dokumen/CategoryFormModal.vue';
import axios from 'axios';

const categories = ref([]);
const loading = ref(false);
const showFormModal = ref(false);
const selectedCategory = ref(null);
const selectedParentId = ref(null);

const rootCategories = computed(() => {
  return categories.value.filter(cat => !cat.parent_id);
});

const fetchCategories = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/document-categories', {
      params: { active_only: false }
    });
    if (response.data.success) {
      categories.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = (parentId = null) => {
  selectedCategory.value = null;
  selectedParentId.value = parentId;
  showFormModal.value = true;
};

const openEditModal = (category) => {
  selectedCategory.value = category;
  selectedParentId.value = null;
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  selectedCategory.value = null;
  selectedParentId.value = null;
};

const handleCategorySaved = () => {
  closeFormModal();
  fetchCategories();
};

const confirmDelete = async (category) => {
  if (!confirm(`Hapus kategori "${category.name}"?`)) return;

  try {
    const response = await axios.delete(`/api/document-categories/${category.id}`);
    if (response.data.success) {
      alert('Kategori berhasil dihapus');
      fetchCategories();
    }
  } catch (error) {
    alert('Gagal menghapus kategori: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
