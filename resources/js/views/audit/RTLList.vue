<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Rencana Tindak Lanjut
          </h3>
          <button
            @click="$router.push('/audit/rtls/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah RTL
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="statistics" class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700 md:grid-cols-5">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
          <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
          <p class="text-sm text-blue-600 dark:text-blue-400">Belum Dimulai</p>
          <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ statistics.not_started }}</p>
        </div>
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
          <p class="text-sm text-purple-600 dark:text-purple-400">Sedang Berjalan</p>
          <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ statistics.in_progress }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Selesai</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ statistics.completed }}</p>
        </div>
        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <p class="text-sm text-red-600 dark:text-red-400">Terlambar</p>
          <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ statistics.overdue }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari RTL..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchRTLs()"
            >
              <option value="">Semua Status</option>
              <option value="not_started">Belum Dimulai</option>
              <option value="in_progress">Sedang Berjalan</option>
              <option value="completed">Selesai</option>
              <option value="overdue">Terlambar</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.unit_kerja_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchRTLs()"
            >
              <option value="">Semua Unit Kerja</option>
              <option v-for="unit in unitKerjas" :key="unit.id" :value="unit.id">
                {{ unit.nama }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Kode RTL</th>
              <th scope="col" class="px-6 py-3">Temuan</th>
              <th scope="col" class="px-6 py-3">Rencana Tindakan</th>
              <th scope="col" class="px-6 py-3">PIC</th>
              <th scope="col" class="px-6 py-3">Target</th>
              <th scope="col" class="px-6 py-3">Progress</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
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
            <tr v-else-if="rtls.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada RTL
              </td>
            </tr>
            <tr v-else v-for="rtl in rtls" :key="rtl.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ rtl.rtl_code || '-' }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-gray-900 dark:text-white line-clamp-1">
                  {{ rtl.audit_finding?.description || rtl.audit_finding?.finding_code || '-' }}
                </p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-gray-900 dark:text-white line-clamp-2">
                  {{ rtl.action_plan }}
                </p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ rtl.pic?.name || rtl.pic_id || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(rtl.target_date) }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-24 rounded-full bg-gray-200 dark:bg-gray-700">
                    <div
                      :style="{ width: (rtl.completion_percentage || 0) + '%' }"
                      class="rounded-full bg-blue-600 py-1 text-center text-xs font-bold text-white transition-all duration-300"
                    >
                      {{ rtl.completion_percentage || 0 }}%
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(rtl.status)">
                  {{ getStatusLabel(rtl.status) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    @click="$router.push(`/audit/rtls/${rtl.id}/progress`)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Tambah Progress"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                  <button
                    v-if="rtl.status === 'completed'"
                    @click="verifyRTL_action(rtl)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Verifikasi"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    @click="$router.push(`/audit/rtls/${rtl.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    v-if="rtl.status === 'not_started'"
                    @click="confirmDelete(rtl)"
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
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> RTL
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
import MainLayout from '@/layouts/MainLayout.vue';
import { useAuditApi } from '@/composables/useAuditApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const { getRTLs, getRTLStatistics, deleteRTL, verifyRTL, loading } = useAuditApi();
const { getUnitKerjas } = useMasterDataApi();

const rtls = ref([]);
const statistics = ref(null);
const unitKerjas = ref([]);

const filters = ref({
  search: '',
  status: '',
  unit_kerja_id: '',
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
    fetchRTLs();
  }, 500);
};

const fetchRTLs = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getRTLs(params);

    if (response.success) {
      rtls.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch RTLs:', error);
  }
};

const fetchStatistics = async () => {
  try {
    const response = await getRTLStatistics();
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
  }
};

const fetchUnitKerjas = async () => {
  try {
    const response = await getUnitKerjas({ per_page: 100 });
    if (response.success) {
      unitKerjas.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch unit kerjas:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchRTLs(page);
  }
};

const verifyRTL_action = async (rtl) => {
  if (confirm(`Apakah Anda yakin ingin memverifikasi RTL "${rtl.code}"?`)) {
    try {
      const response = await verifyRTL(rtl.id, {});
      if (response.success) {
        alert('RTL berhasil diverifikasi');
        fetchRTLs(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal memverifikasi RTL: ' + (error.response?.data?.message || error.message));
    }
  }
};

const confirmDelete = async (rtl) => {
  if (confirm(`Apakah Anda yakin ingin menghapus RTL "${rtl.code}"?`)) {
    try {
      const response = await deleteRTL(rtl.id);
      if (response.success) {
        alert('RTL berhasil dihapus');
        fetchRTLs(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menghapus RTL: ' + (error.response?.data?.message || error.message));
    }
  }
};

const getStatusClass = (status) => {
  const classes = {
    not_started: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[status] || ''}`;
};

const getStatusLabel = (status) => {
  const labels = {
    not_started: 'Belum Dimulai',
    in_progress: 'Sedang Berjalan',
    completed: 'Selesai',
    overdue: 'Terlambar',
  };
  return labels[status] || status;
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

onMounted(() => {
  fetchStatistics();
  fetchUnitKerjas();
  fetchRTLs();
});
</script>
