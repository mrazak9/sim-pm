<template>
  <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <!-- Header -->
    <div class="border-b border-stroke px-7 py-4 dark:border-strokedark">
      <div class="flex items-center justify-between">
        <h3 class="font-medium text-black dark:text-white">
          Daftar Indikator Kinerja Utama (IKU)
        </h3>
        <button
          @click="openCreateForm"
          class="inline-flex items-center justify-center rounded-md bg-primary px-5 py-2.5 text-center font-medium text-white hover:bg-opacity-90"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah IKU
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="border-b border-stroke px-7 py-4 dark:border-strokedark">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Cari IKU..."
            class="w-full rounded border border-stroke bg-transparent px-4 py-2 text-black outline-none transition focus:border-primary dark:border-strokedark dark:text-white"
            @input="debouncedSearch"
          />
        </div>
        <div>
          <select
            v-model="filters.is_active"
            class="w-full rounded border border-stroke bg-transparent px-4 py-2 text-black outline-none transition focus:border-primary dark:border-strokedark dark:text-white"
            @change="fetchIKUs"
          >
            <option value="">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
          </select>
        </div>
        <div>
          <select
            v-model="filters.kategori"
            class="w-full rounded border border-stroke bg-transparent px-4 py-2 text-black outline-none transition focus:border-primary dark:border-strokedark dark:text-white"
            @change="fetchIKUs"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
        <div>
          <select
            v-model="filters.target_type"
            class="w-full rounded border border-stroke bg-transparent px-4 py-2 text-black outline-none transition focus:border-primary dark:border-strokedark dark:text-white"
            @change="fetchIKUs"
          >
            <option value="">Semua Tipe Target</option>
            <option value="increase">Meningkat</option>
            <option value="decrease">Menurun</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-2 text-left dark:bg-meta-4">
            <th class="px-4 py-4 font-medium text-black dark:text-white">Kode IKU</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Nama IKU</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Kategori</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Satuan</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Tipe Target</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Bobot</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Status</th>
            <th class="px-4 py-4 font-medium text-black dark:text-white">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="8" class="px-4 py-8 text-center">
              <div class="flex items-center justify-center">
                <svg class="h-8 w-8 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </td>
          </tr>
          <tr v-else-if="ikus.length === 0">
            <td colspan="8" class="px-4 py-8 text-center text-black dark:text-white">
              Tidak ada data IKU
            </td>
          </tr>
          <tr v-else v-for="iku in ikus" :key="iku.id" class="border-b border-stroke dark:border-strokedark">
            <td class="px-4 py-4">
              <p class="text-black dark:text-white">{{ iku.kode_iku }}</p>
            </td>
            <td class="px-4 py-4">
              <p class="text-black dark:text-white">{{ iku.nama_iku }}</p>
              <p v-if="iku.deskripsi" class="text-sm text-gray-500">{{ truncate(iku.deskripsi, 50) }}</p>
            </td>
            <td class="px-4 py-4">
              <span class="inline-flex rounded-full bg-opacity-10 px-3 py-1 text-sm font-medium bg-success text-success">
                {{ iku.kategori || '-' }}
              </span>
            </td>
            <td class="px-4 py-4">
              <p class="text-black dark:text-white">{{ getSatuanLabel(iku.satuan) }}</p>
            </td>
            <td class="px-4 py-4">
              <span :class="[
                'inline-flex rounded-full bg-opacity-10 px-3 py-1 text-sm font-medium',
                iku.target_type === 'increase' ? 'bg-success text-success' : 'bg-warning text-warning'
              ]">
                {{ iku.target_type === 'increase' ? 'Meningkat' : 'Menurun' }}
              </span>
            </td>
            <td class="px-4 py-4">
              <p class="text-black dark:text-white">{{ iku.bobot || '-' }}</p>
            </td>
            <td class="px-4 py-4">
              <span :class="[
                'inline-flex rounded-full bg-opacity-10 px-3 py-1 text-sm font-medium',
                iku.is_active ? 'bg-success text-success' : 'bg-danger text-danger'
              ]">
                {{ iku.is_active ? 'Aktif' : 'Tidak Aktif' }}
              </span>
            </td>
            <td class="px-4 py-4">
              <div class="flex items-center space-x-2">
                <button
                  @click="viewIKU(iku)"
                  class="hover:text-primary"
                  title="Lihat Detail"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button
                  @click="editIKU(iku)"
                  class="hover:text-primary"
                  title="Edit"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  @click="confirmDelete(iku)"
                  class="hover:text-danger"
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
    <div v-if="pagination.total > 0" class="flex items-center justify-between border-t border-stroke px-7 py-4 dark:border-strokedark">
      <div class="text-sm text-black dark:text-white">
        Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} data
      </div>
      <div class="flex items-center space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="rounded border border-stroke px-3 py-1 hover:bg-gray dark:border-strokedark disabled:cursor-not-allowed disabled:opacity-50"
        >
          Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="changePage(page)"
          :class="[
            'rounded border px-3 py-1',
            page === pagination.current_page
              ? 'bg-primary text-white border-primary'
              : 'border-stroke hover:bg-gray dark:border-strokedark'
          ]"
        >
          {{ page }}
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="rounded border border-stroke px-3 py-1 hover:bg-gray dark:border-strokedark disabled:cursor-not-allowed disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useIKUApi } from '@/composables/useIKUApi';

const router = useRouter();
const { loading, getIKUs, getCategories, deleteIKU } = useIKUApi();

const ikus = ref([]);
const categories = ref([]);
const filters = ref({
  search: '',
  is_active: '',
  kategori: '',
  target_type: '',
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

const fetchIKUs = async (page = 1) => {
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

    const response = await getIKUs(params);
    ikus.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
    };
  } catch (error) {
    console.error('Failed to fetch IKUs:', error);
  }
};

const fetchCategories = async () => {
  try {
    const response = await getCategories();
    categories.value = response.data;
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchIKUs();
  }, 300);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchIKUs(page);
  }
};

const getSatuanLabel = (satuan) => {
  const labels = {
    persen: 'Persen (%)',
    jumlah: 'Jumlah',
    skor: 'Skor',
    nilai: 'Nilai',
  };
  return labels[satuan] || satuan;
};

const truncate = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const openCreateForm = () => {
  router.push({ name: 'iku-create' });
};

const viewIKU = (iku) => {
  router.push({ name: 'iku-detail', params: { id: iku.id } });
};

const editIKU = (iku) => {
  router.push({ name: 'iku-edit', params: { id: iku.id } });
};

const confirmDelete = async (iku) => {
  if (confirm(`Apakah Anda yakin ingin menghapus IKU "${iku.nama_iku}"?`)) {
    try {
      await deleteIKU(iku.id);
      fetchIKUs(pagination.value.current_page);
    } catch (error) {
      console.error('Failed to delete IKU:', error);
      alert('Gagal menghapus IKU');
    }
  }
};

onMounted(() => {
  fetchIKUs();
  fetchCategories();
});
</script>
