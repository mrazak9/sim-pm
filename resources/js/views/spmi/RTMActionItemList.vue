<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Action Item RTM
          </h3>
          <button
            @click="$router.push('/spmi/rtm-actions/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Action Item
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
        <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
          <p class="text-sm text-yellow-600 dark:text-yellow-400">Sedang Berjalan</p>
          <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ statistics.in_progress }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Selesai</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ statistics.completed }}</p>
        </div>
        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <p class="text-sm text-red-600 dark:text-red-400">Terlambat</p>
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
              placeholder="Cari action item..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchActionItems()"
            >
              <option value="">Semua Status</option>
              <option value="not_started">Belum Dimulai</option>
              <option value="in_progress">Sedang Berjalan</option>
              <option value="completed">Selesai</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.priority"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchActionItems()"
            >
              <option value="">Semua Prioritas</option>
              <option value="low">Rendah</option>
              <option value="medium">Sedang</option>
              <option value="high">Tinggi</option>
              <option value="critical">Kritis</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.unit_kerja_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchActionItems()"
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
              <th scope="col" class="px-6 py-3">Kode</th>
              <th scope="col" class="px-6 py-3">Judul</th>
              <th scope="col" class="px-6 py-3">RTM</th>
              <th scope="col" class="px-6 py-3">Prioritas</th>
              <th scope="col" class="px-6 py-3">PIC</th>
              <th scope="col" class="px-6 py-3">Due Date</th>
              <th scope="col" class="px-6 py-3">Hari Tersisa</th>
              <th scope="col" class="px-6 py-3">Progress</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="10" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="actionItems.length === 0">
              <td colspan="10" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada action item
              </td>
            </tr>
            <tr v-else v-for="item in actionItems" :key="item.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                {{ item.code }}
              </td>
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ item.title }}</p>
                <p v-if="item.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                  {{ item.description }}
                </p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ item.rtm?.title || '-' }}
              </td>
              <td class="px-6 py-4">
                <span :class="getPriorityClass(item.priority)">
                  {{ getPriorityLabel(item.priority) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ item.pic_id || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(item.due_date) }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                <span :class="getDaysUntilClass(item.days_until_due)">
                  {{ item.days_until_due >= 0 ? item.days_until_due : 'Terlambat ' + Math.abs(item.days_until_due) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div
                      class="bg-blue-600 h-2 rounded-full dark:bg-blue-400"
                      :style="{ width: item.completion_percentage + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs font-medium text-gray-900 dark:text-white min-w-fit">
                    {{ item.completion_percentage }}%
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    v-if="item.status === 'not_started'"
                    @click="startActionItem(item)"
                    class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400"
                    title="Mulai"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    </svg>
                  </button>
                  <button
                    v-if="item.status !== 'completed'"
                    @click="addProgress(item)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Tambah Progress"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                  <button
                    v-if="item.status === 'in_progress'"
                    @click="completeActionItem(item)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Selesaikan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    @click="$router.push(`/spmi/rtm-actions/${item.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(item)"
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
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> action item
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
import { useSPMIApi } from '@/composables/useSPMIApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const { getRTMActionItems, getRTMActionItemStatistics, deleteRTMActionItem, startRTMActionItem, completeRTMActionItem, loading } = useSPMIApi();
const { getUnitKerjas } = useMasterDataApi();

const actionItems = ref([]);
const statistics = ref(null);
const unitKerjas = ref([]);

const filters = ref({
  search: '',
  rtm_id: '',
  status: '',
  priority: '',
  pic_id: '',
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
    fetchActionItems();
  }, 500);
};

const fetchActionItems = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getRTMActionItems(params);

    if (response.success) {
      actionItems.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch action items:', error);
  }
};

const fetchStatistics = async () => {
  try {
    const response = await getRTMActionItemStatistics();
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
  }
};

const fetchUnitKerjas = async () => {
  try {
    const response = await getUnitKerjas({ active_only: true });
    if (response.success) {
      unitKerjas.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch unit kerjas:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchActionItems(page);
  }
};

const startActionItem = async (item) => {
  if (confirm(`Apakah Anda yakin ingin memulai action item "${item.title}"?`)) {
    try {
      const response = await startRTMActionItem(item.id);
      if (response.success) {
        alert('Action item berhasil dimulai');
        fetchActionItems(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal memulai action item: ' + (error.response?.data?.message || error.message));
    }
  }
};

const completeActionItem = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menyelesaikan action item "${item.title}"?`)) {
    try {
      const response = await completeRTMActionItem(item.id, {});
      if (response.success) {
        alert('Action item berhasil diselesaikan');
        fetchActionItems(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menyelesaikan action item: ' + (error.response?.data?.message || error.message));
    }
  }
};

const addProgress = (item) => {
  // TODO: Implement progress modal
  alert('TODO: Implementasi modal tambah progress');
};

const confirmDelete = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus action item "${item.title}"?`)) {
    try {
      const response = await deleteRTMActionItem(item.id);
      if (response.success) {
        alert('Action item berhasil dihapus');
        fetchActionItems(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menghapus action item: ' + (error.response?.data?.message || error.message));
    }
  }
};

const getStatusClass = (status) => {
  const classes = {
    not_started: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[status] || ''}`;
};

const getStatusLabel = (status) => {
  const labels = {
    not_started: 'Belum Dimulai',
    in_progress: 'Sedang Berjalan',
    completed: 'Selesai',
  };
  return labels[status] || status;
};

const getPriorityClass = (priority) => {
  const classes = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    high: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[priority] || ''}`;
};

const getPriorityLabel = (priority) => {
  const labels = {
    low: 'Rendah',
    medium: 'Sedang',
    high: 'Tinggi',
    critical: 'Kritis',
  };
  return labels[priority] || priority;
};

const getDaysUntilClass = (daysUntil) => {
  if (daysUntil < 0) {
    return 'text-red-600 font-semibold dark:text-red-400';
  } else if (daysUntil <= 3) {
    return 'text-yellow-600 font-semibold dark:text-yellow-400';
  }
  return 'text-green-600 dark:text-green-400';
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
  fetchActionItems();
});
</script>
