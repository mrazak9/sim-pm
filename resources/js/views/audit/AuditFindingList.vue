<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Temuan Audit
          </h3>
          <button
            @click="$router.push('/audit/findings/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Temuan
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="statistics" class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700 md:grid-cols-5">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
          <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
        </div>
        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <p class="text-sm text-red-600 dark:text-red-400">Major</p>
          <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ statistics.major }}</p>
        </div>
        <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
          <p class="text-sm text-yellow-600 dark:text-yellow-400">Minor</p>
          <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ statistics.minor }}</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
          <p class="text-sm text-blue-600 dark:text-blue-400">OFI</p>
          <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ statistics.ofi }}</p>
        </div>
        <div class="rounded-lg bg-orange-50 p-4 dark:bg-orange-900/20">
          <p class="text-sm text-orange-600 dark:text-orange-400">Overdue</p>
          <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ statistics.overdue }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari temuan..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.category"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchAuditFindings()"
            >
              <option value="">Semua Kategori</option>
              <option value="major">Major</option>
              <option value="minor">Minor</option>
              <option value="ofi">OFI</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchAuditFindings()"
            >
              <option value="">Semua Status</option>
              <option value="open">Terbuka</option>
              <option value="resolved">Terselesaikan</option>
              <option value="verified">Diverifikasi</option>
              <option value="closed">Ditutup</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.priority"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchAuditFindings()"
            >
              <option value="">Semua Prioritas</option>
              <option value="low">Rendah</option>
              <option value="medium">Sedang</option>
              <option value="high">Tinggi</option>
              <option value="critical">Kritis</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Kode Temuan</th>
              <th scope="col" class="px-6 py-3">Kategori</th>
              <th scope="col" class="px-6 py-3">Deskripsi</th>
              <th scope="col" class="px-6 py-3">Unit Kerja</th>
              <th scope="col" class="px-6 py-3">Prioritas</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Batas Waktu</th>
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
            <tr v-else-if="auditFindings.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada temuan audit
              </td>
            </tr>
            <tr v-else v-for="finding in auditFindings" :key="finding.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ finding.code || '-' }}</p>
              </td>
              <td class="px-6 py-4">
                <span :class="getCategoryClass(finding.category)">
                  {{ getCategoryLabel(finding.category) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white line-clamp-1">{{ finding.title }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ finding.description }}</p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ finding.audit_schedule?.unit_kerja?.nama || '-' }}
              </td>
              <td class="px-6 py-4">
                <span :class="getPriorityClass(finding.priority)">
                  {{ getPriorityLabel(finding.priority) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(finding.status)">
                  {{ getStatusLabel(finding.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(finding.due_date) }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    v-if="finding.status === 'open'"
                    @click="resolveFinding(finding)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Terselesaikan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    v-if="finding.status === 'resolved'"
                    @click="verifyFinding(finding)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Verifikasi"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    v-if="finding.status === 'verified'"
                    @click="closeFinding(finding)"
                    class="text-purple-600 hover:text-purple-700 dark:text-purple-400"
                    title="Tutup"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                  <button
                    v-if="finding.status !== 'closed'"
                    @click="$router.push(`/audit/findings/${finding.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    v-if="finding.status === 'open'"
                    @click="confirmDelete(finding)"
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
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> temuan audit
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

const { getAuditFindings, getAuditFindingStatistics, deleteAuditFinding, resolveAuditFinding, verifyAuditFinding, closeAuditFinding, loading } = useAuditApi();

const auditFindings = ref([]);
const statistics = ref(null);

const filters = ref({
  search: '',
  category: '',
  status: '',
  priority: '',
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
    fetchAuditFindings();
  }, 500);
};

const fetchAuditFindings = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getAuditFindings(params);

    if (response.success) {
      auditFindings.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch audit findings:', error);
  }
};

const fetchStatistics = async () => {
  try {
    const response = await getAuditFindingStatistics();
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchAuditFindings(page);
  }
};

const resolveFinding = async (finding) => {
  if (confirm(`Apakah Anda yakin ingin menandai temuan "${finding.title}" sebagai terselesaikan?`)) {
    try {
      const response = await resolveAuditFinding(finding.id, {});
      if (response.success) {
        alert('Temuan berhasil ditandai terselesaikan');
        fetchAuditFindings(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menandai temuan: ' + (error.response?.data?.message || error.message));
    }
  }
};

const verifyFinding = async (finding) => {
  if (confirm(`Apakah Anda yakin ingin memverifikasi temuan "${finding.title}"?`)) {
    try {
      const response = await verifyAuditFinding(finding.id, {});
      if (response.success) {
        alert('Temuan berhasil diverifikasi');
        fetchAuditFindings(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal memverifikasi temuan: ' + (error.response?.data?.message || error.message));
    }
  }
};

const closeFinding = async (finding) => {
  if (confirm(`Apakah Anda yakin ingin menutup temuan "${finding.title}"?`)) {
    try {
      const response = await closeAuditFinding(finding.id);
      if (response.success) {
        alert('Temuan berhasil ditutup');
        fetchAuditFindings(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menutup temuan: ' + (error.response?.data?.message || error.message));
    }
  }
};

const confirmDelete = async (finding) => {
  if (confirm(`Apakah Anda yakin ingin menghapus temuan "${finding.title}"?`)) {
    try {
      const response = await deleteAuditFinding(finding.id);
      if (response.success) {
        alert('Temuan berhasil dihapus');
        fetchAuditFindings(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menghapus temuan: ' + (error.response?.data?.message || error.message));
    }
  }
};

const getCategoryClass = (category) => {
  const classes = {
    major: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    minor: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    ofi: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[category] || ''}`;
};

const getCategoryLabel = (category) => {
  const labels = {
    major: 'Major',
    minor: 'Minor',
    ofi: 'OFI',
  };
  return labels[category] || category;
};

const getPriorityClass = (priority) => {
  const classes = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    high: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
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

const getStatusClass = (status) => {
  const classes = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    resolved: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    verified: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[status] || ''}`;
};

const getStatusLabel = (status) => {
  const labels = {
    open: 'Terbuka',
    resolved: 'Terselesaikan',
    verified: 'Diverifikasi',
    closed: 'Ditutup',
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
  fetchAuditFindings();
});
</script>
