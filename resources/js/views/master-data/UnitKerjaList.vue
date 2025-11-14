<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Daftar Unit Kerja
          </h3>
          <button
            @click="openCreateForm"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Unit Kerja
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
              placeholder="Cari unit kerja..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.is_active"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @change="fetchUnitKerjas"
            >
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Tidak Aktif</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.jenis"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @change="fetchUnitKerjas"
            >
              <option value="">Semua Jenis</option>
              <option value="universitas">Universitas</option>
              <option value="fakultas">Fakultas</option>
              <option value="jurusan">Jurusan</option>
              <option value="prodi">Program Studi</option>
              <option value="unit_pendukung">Unit Pendukung</option>
              <option value="lembaga">Lembaga</option>
              <option value="biro">Biro</option>
              <option value="pusat">Pusat</option>
              <option value="laboratorium">Laboratorium</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Kode</th>
              <th scope="col" class="px-6 py-3">Nama Unit Kerja</th>
              <th scope="col" class="px-6 py-3">Jenis</th>
              <th scope="col" class="px-6 py-3">Parent</th>
              <th scope="col" class="px-6 py-3">Email</th>
              <th scope="col" class="px-6 py-3">Status</th>
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
            <tr v-else-if="unitKerjas.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada data unit kerja
              </td>
            </tr>
            <tr v-else v-for="item in unitKerjas" :key="item.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                {{ item.kode }}
              </td>
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ item.nama }}</p>
                <p v-if="item.nama_singkat" class="text-xs text-gray-500 dark:text-gray-400">{{ item.nama_singkat }}</p>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                  {{ getJenisLabel(item.jenis) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ item.parent?.nama || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ item.email || '-' }}
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'inline-flex rounded-full px-3 py-1 text-xs font-medium',
                  item.is_active
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                ]">
                  {{ item.is_active ? 'Aktif' : 'Tidak Aktif' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    @click="editItem(item)"
                    class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(item)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
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
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> data
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
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const router = useRouter();
const { loading, getUnitKerjas, deleteUnitKerja } = useMasterDataApi();

const unitKerjas = ref([]);
const filters = ref({
  search: '',
  is_active: '',
  jenis: '',
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

const fetchUnitKerjas = async (page = 1) => {
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

    const response = await getUnitKerjas(params);
    unitKerjas.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
    };
  } catch (error) {
    console.error('Failed to fetch unit kerjas:', error);
  }
};

let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUnitKerjas();
  }, 300);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUnitKerjas(page);
  }
};

const getJenisLabel = (jenis) => {
  const labels = {
    universitas: 'Universitas',
    fakultas: 'Fakultas',
    jurusan: 'Jurusan',
    prodi: 'Program Studi',
    unit_pendukung: 'Unit Pendukung',
    lembaga: 'Lembaga',
    biro: 'Biro',
    pusat: 'Pusat',
    laboratorium: 'Laboratorium',
  };
  return labels[jenis] || jenis;
};

const openCreateForm = () => {
  router.push({ name: 'unit-kerja-create' });
};

const editItem = (item) => {
  router.push({ name: 'unit-kerja-edit', params: { id: item.id } });
};

const confirmDelete = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus unit kerja "${item.nama}"?`)) {
    try {
      await deleteUnitKerja(item.id);
      fetchUnitKerjas(pagination.value.current_page);
    } catch (error) {
      console.error('Failed to delete unit kerja:', error);
      alert('Gagal menghapus unit kerja');
    }
  }
};

onMounted(() => {
  fetchUnitKerjas();
});
</script>
