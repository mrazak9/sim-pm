<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Monitoring SPMI
          </h3>
          <button
            @click="$router.push('/spmi/monitorings/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Monitoring
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
          <p class="text-sm text-blue-600 dark:text-blue-400">Direncanakan</p>
          <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ statistics.planned }}</p>
        </div>
        <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
          <p class="text-sm text-yellow-600 dark:text-yellow-400">Berlangsung</p>
          <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ statistics.ongoing }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Selesai</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ statistics.completed }}</p>
        </div>
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
          <p class="text-sm text-purple-600 dark:text-purple-400">Rata-Rata Compliance</p>
          <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ statistics.average_compliance_score ? statistics.average_compliance_score.toFixed(1) : '-' }}%</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari monitoring..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchMonitorings()"
            >
              <option value="">Semua Status</option>
              <option value="planned">Direncanakan</option>
              <option value="ongoing">Berlangsung</option>
              <option value="completed">Selesai</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.monitoring_type"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchMonitorings()"
            >
              <option value="">Semua Tipe</option>
              <option value="desk_evaluation">Desk Evaluation</option>
              <option value="field_visit">Field Visit</option>
              <option value="interview">Interview</option>
              <option value="document_review">Document Review</option>
              <option value="mixed">Mixed</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.unit_kerja_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchMonitorings()"
            >
              <option value="">Semua Unit Kerja</option>
              <option v-for="unit in unitKerjas" :key="unit.id" :value="unit.id">
                {{ unit.nama_unit || unit.nama }}
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
              <th scope="col" class="px-6 py-3">Standar</th>
              <th scope="col" class="px-6 py-3">Tanggal Monitoring</th>
              <th scope="col" class="px-6 py-3">Tipe</th>
              <th scope="col" class="px-6 py-3">Nilai Compliance</th>
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
            <tr v-else-if="monitorings.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada monitoring
              </td>
            </tr>
            <tr v-else v-for="monitoring in monitorings" :key="monitoring.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                {{ monitoring.monitoring_code }}
              </td>
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ monitoring.title }}</p>
                <p v-if="monitoring.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                  {{ monitoring.description }}
                </p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ monitoring.spmi_standard?.name || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(monitoring.monitoring_date) }}
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                  {{ getMonitoringTypeLabel(monitoring.monitoring_type) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ monitoring.compliance_score !== null ? monitoring.compliance_score + '%' : '-' }}
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(monitoring.status)">
                  {{ getStatusLabel(monitoring.status) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    v-if="monitoring.status === 'planned'"
                    @click="startMonitoring(monitoring)"
                    class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400"
                    title="Mulai"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    </svg>
                  </button>
                  <button
                    v-if="monitoring.status === 'ongoing'"
                    @click="completeMonitoring(monitoring)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Selesaikan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    @click="$router.push(`/spmi/monitorings/${monitoring.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(monitoring)"
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
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> monitoring
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
import { useSPMIApi } from '@/composables/useSPMIApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const { success, error } = useToast();

const { getSPMIMonitorings, getSPMIMonitoringStatistics, deleteSPMIMonitoring, startSPMIMonitoring, completeSPMIMonitoring, loading } = useSPMIApi();
const { getUnitKerjas } = useMasterDataApi();

const monitorings = ref([]);
const statistics = ref(null);
const unitKerjas = ref([]);

const filters = ref({
  search: '',
  spmi_standard_id: '',
  tahun_akademik_id: '',
  status: '',
  monitoring_type: '',
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
    fetchMonitorings();
  }, 500);
};

const fetchMonitorings = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getSPMIMonitorings(params);

    if (response.success) {
      monitorings.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch monitorings:', error);
  }
};

const fetchStatistics = async () => {
  try {
    const response = await getSPMIMonitoringStatistics();
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
    fetchMonitorings(page);
  }
};

const startMonitoring = async (monitoring) => {
  if (confirm(`Apakah Anda yakin ingin memulai monitoring "${monitoring.title}"?`)) {
    try {
      const response = await startSPMIMonitoring(monitoring.id);
      if (response.success) {
        success('Monitoring berhasil dimulai');
        fetchMonitorings(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (err) {
      console.error('Failed to start monitoring:', err);
      error('Gagal memulai monitoring: ' + (err.response?.data?.message || err.message));
    }
  }
};

const completeMonitoring = async (monitoring) => {
  if (confirm(`Apakah Anda yakin ingin menyelesaikan monitoring "${monitoring.title}"? Anda akan diminta mengisi laporan monitoring.`)) {
    router.push(`/spmi/monitorings/${monitoring.id}/complete`);
  }
};

const confirmDelete = async (monitoring) => {
  if (confirm(`Apakah Anda yakin ingin menghapus monitoring "${monitoring.title}"?`)) {
    try {
      const response = await deleteSPMIMonitoring(monitoring.id);
      if (response.success) {
        success('Monitoring berhasil dihapus');
        fetchMonitorings(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (err) {
      console.error('Failed to delete monitoring:', err);
      error('Gagal menghapus monitoring: ' + (err.response?.data?.message || err.message));
    }
  }
};

const getStatusClass = (status) => {
  const classes = {
    planned: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    ongoing: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[status] || ''}`;
};

const getStatusLabel = (status) => {
  const labels = {
    planned: 'Direncanakan',
    ongoing: 'Berlangsung',
    completed: 'Selesai',
  };
  return labels[status] || status;
};

const getMonitoringTypeLabel = (type) => {
  const labels = {
    desk_evaluation: 'Desk Evaluation',
    field_visit: 'Field Visit',
    interview: 'Interview',
    document_review: 'Document Review',
    mixed: 'Mixed',
  };
  return labels[type] || type;
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
  fetchMonitorings();
});
</script>
