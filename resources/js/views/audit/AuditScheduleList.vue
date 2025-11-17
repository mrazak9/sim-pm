<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Jadwal Audit
          </h3>
          <button
            @click="$router.push('/audit/schedules/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Jadwal
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
              placeholder="Cari jadwal audit..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchAuditSchedules()"
            >
              <option value="">Semua Status</option>
              <option value="scheduled">Terjadwal</option>
              <option value="ongoing">Sedang Berlangsung</option>
              <option value="completed">Selesai</option>
              <option value="cancelled">Dibatalkan</option>
              <option value="rescheduled">Dijadwal Ulang</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.audit_plan_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchAuditSchedules()"
            >
              <option value="">Semua Rencana Audit</option>
              <option v-for="plan in auditPlans" :key="plan.id" :value="plan.id">
                {{ plan.title }}
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
              <th scope="col" class="px-6 py-3">Unit Kerja</th>
              <th scope="col" class="px-6 py-3">Auditor Lead</th>
              <th scope="col" class="px-6 py-3">Tanggal Jadwal</th>
              <th scope="col" class="px-6 py-3">Durasi</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="auditSchedules.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada jadwal audit
              </td>
            </tr>
            <tr v-else v-for="schedule in auditSchedules" :key="schedule.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">
                  {{ schedule.unit_kerja?.nama || '-' }}
                </p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ schedule.auditor_lead?.name || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(schedule.scheduled_date) }}
              </td>
              <td class="px-6 py-4">
                <span class="text-gray-900 dark:text-white">{{ schedule.estimated_duration }} menit</span>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(schedule.status)">
                  {{ getStatusLabel(schedule.status) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    v-if="schedule.status === 'scheduled'"
                    @click="startAuditSchedule(schedule)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Mulai"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    v-if="schedule.status === 'ongoing'"
                    @click="completeAuditSchedule(schedule)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Selesaikan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    @click="$router.push(`/audit/schedules/${schedule.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(schedule)"
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
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> jadwal audit
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

const { getAuditSchedules, getActiveAuditPlans, deleteAuditSchedule, startAudit, completeAudit, loading } = useAuditApi();

const auditSchedules = ref([]);
const auditPlans = ref([]);

const filters = ref({
  search: '',
  status: '',
  audit_plan_id: '',
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
    fetchAuditSchedules();
  }, 500);
};

const fetchAuditSchedules = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getAuditSchedules(params);

    if (response.success) {
      auditSchedules.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch audit schedules:', error);
  }
};

const fetchAuditPlans = async () => {
  try {
    const response = await getActiveAuditPlans();
    if (response.success) {
      auditPlans.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch audit plans:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchAuditSchedules(page);
  }
};

const startAuditSchedule = async (schedule) => {
  if (confirm(`Apakah Anda yakin ingin memulai audit untuk ${schedule.unit_kerja?.nama}?`)) {
    try {
      const response = await startAudit(schedule.id);
      if (response.success) {
        alert('Audit berhasil dimulai');
        fetchAuditSchedules(pagination.value.current_page);
      }
    } catch (error) {
      alert('Gagal memulai audit: ' + (error.response?.data?.message || error.message));
    }
  }
};

const completeAuditSchedule = async (schedule) => {
  if (confirm(`Apakah Anda yakin ingin menyelesaikan audit untuk ${schedule.unit_kerja?.nama}?`)) {
    try {
      const response = await completeAudit(schedule.id);
      if (response.success) {
        alert('Audit berhasil diselesaikan');
        fetchAuditSchedules(pagination.value.current_page);
      }
    } catch (error) {
      alert('Gagal menyelesaikan audit: ' + (error.response?.data?.message || error.message));
    }
  }
};

const confirmDelete = async (schedule) => {
  if (confirm(`Apakah Anda yakin ingin menghapus jadwal audit untuk ${schedule.unit_kerja?.nama}?`)) {
    try {
      const response = await deleteAuditSchedule(schedule.id);
      if (response.success) {
        alert('Jadwal audit berhasil dihapus');
        fetchAuditSchedules(pagination.value.current_page);
      }
    } catch (error) {
      alert('Gagal menghapus jadwal audit: ' + (error.response?.data?.message || error.message));
    }
  }
};

const getStatusClass = (status) => {
  const classes = {
    scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    ongoing: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    rescheduled: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  };
  return `inline-flex rounded-full px-3 py-1 text-xs font-medium ${classes[status] || ''}`;
};

const getStatusLabel = (status) => {
  const labels = {
    scheduled: 'Terjadwal',
    ongoing: 'Sedang Berlangsung',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    rescheduled: 'Dijadwal Ulang',
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
    hour: '2-digit',
    minute: '2-digit',
  });
};

onMounted(() => {
  fetchAuditPlans();
  fetchAuditSchedules();
});
</script>
